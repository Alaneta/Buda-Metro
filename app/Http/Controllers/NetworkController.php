<?php

namespace App\Http\Controllers;

use App\Models\Network;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class NetworkController extends Controller
{

    // Devuelve la estación no visitada en caso de tener un recorrido cíclico
    private function unvisitedConnectedStation($connectedStations, $station, array $paths)
    {
        if (!count($paths)) return $connectedStations->get(1);
        foreach ($connectedStations as $connectedStation) {
            $string = $station->name . ', ' . $connectedStation->name;
            foreach ($paths as $path) {
                if (stripos($string, $path) == false) return $connectedStation;
            }
        }
        return false;
    }

    private function contains($str, array $arr)
    {
        foreach($arr as $a) {
            if (stripos($a, $str) !== false) return true;
        }
        return false;
    }

    private function checkpointCompleted($checkpointStations, array $paths)
    {
        foreach ($checkpointStations as $checkpointStation) {
            foreach ($checkpointStation->connectedStations as $connectedStation) {
                $str = $checkpointStation->name . ',' . $connectedStation->name;
                $strInv = $connectedStation->name . ',' . $checkpointStation->name;
                if (!($this->contains($str, $paths) || $this->contains($strInv, $paths))) return false;
            }
        }
        return true;
    }

    private function saveConnectedStations($station, $textLine, $i, $j, $network) {
        if ($textLine) {
            $connectedStation = Network::find($network->id)->stations()->where('name', $textLine)->first();
            if (is_null($connectedStation)) {
                $connectedStation = new Station([
                    'name' => $textLine,
                    'color' => array_key_exists(1, explode('-', $textLine)) ? explode('-', $textLine)[1] : '',
                    'x' => $i,
                    'y' => $j
                ]);
                $connectedStation->network()->associate($network);
                $connectedStation->save();
            }
            $station->connectedStations()->save($connectedStation);
        }
    }

    public function index()
    {
        return view('home', [
            'networks' => Network::all()
        ]);
    }

    // Guardado del archivo que contiene la red de metro
    public function save(Request $request)
    {
        $request->validate([
            'file' => ['required']
        ]);

        try {
            // Guardado de archivo en /storage/app/
            $file = $request->allFiles()['file'][0];
            Storage::disk('local')->put($file->getClientOriginalName(), file_get_contents($file));

              // Desarrollo para subida de múltiples archivos
//            $files = $request->allFiles();
//            foreach ($files['file'] as $file) {
//                Storage::disk('local')->put($file->getClientOriginalName(), file_get_contents($file));
//            }
            DB::beginTransaction();
            $network = new Network([
                'name' => $file->getClientOriginalName()
            ]);
            $network->save();

            $fileHandle = fopen(storage_path('app/' . $file->getClientOriginalName()), 'r');
            $textLines = [];
            while (!feof($fileHandle)) {
                $textLines[] = fgetcsv($fileHandle, 0, ',');
            }
            fclose($fileHandle);

            for ($i = 0; $i < count($textLines); $i++) {
                for ($j = 0; $j < count($textLines[$i]); $j++)
                {
                    if ($textLines[$i][$j]) {
                        $station = Network::find($network->id)->stations()->where('name', $textLines[$i][$j])->first();
                        if (is_null($station)) {
                            $station = new Station([
                                'name' => $textLines[$i][$j],
                                'color' => array_key_exists(1, explode('-', $textLines[$i][$j])) ? explode('-', $textLines[$i][$j])[1] : '',
                                'x' => $i,
                                'y' => $j
                            ]);
                            $station->network()->associate($network);
                            $station->save();
                        }
                        $this->saveConnectedStations($station, array_key_exists($j + 1, $textLines[$i]) ? $textLines[$i][$j + 1] : '', $i, $j + 1, $network);
                        $this->saveConnectedStations($station, array_key_exists($j - 1, $textLines[$i]) ? $textLines[$i][$j - 1] : '', $i, $j - 1, $network);
                    }
                }
            }
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['error' => $e->getMessage()], 401);
        }
        return true;
    }

    public function delete($id)
    {
        $network = Network::find($id);
        $network->delete();
    }

    public function downloadExample() {
        return response()->download(storage_path('/app/example/Ejemplo Buda Metro.csv'));
    }

    public function showCalculateRoute($id) {
        $network = Network::with('stations')->find($id);

        return view('calculate-route', [
            'network' => $network
        ]);
    }

    public function calculateShortestRoute(Request $request, $id)
    {
        $request->validate([
            'initialStation' => ['required', 'exists:stations,id'],
            'finalStation' => ['required', 'exists:stations,id'],
        ]);

        try {
            $request->trainColor = $request->trainColor == null ? '' : $request->trainColor;

            // Si la estación inicial es igual a la final, se retorna el nombre de la estación como camino mas corto
            if ($request->initialStation == $request->finalStation) {
                $shortestPath = Network::find($id)->stations()->find($request->initialStation)->name;
                return back()->with('shortestPath', $shortestPath);
            }

            // Se generan todas las rutas/caminos posibles en $paths
            $shortestPath = [];
            $paths = [];
            $unvisitedStations = Network::find($id)->stations()->where('weight', 0)->get();
            $checkpointStations = Network::find($id)
                ->stations()
                ->where('checkpoint', 1)
                ->get();
            DB::beginTransaction();
            while (count($unvisitedStations) || count($checkpointStations)) {
                $station = Network::find($id)->stations()->find($request->initialStation);
                $station->visit();
                $path = $station->name; // inicializa con el nombre de la estacion inicial
                $pathIds = $station->id; // inicializa con el id de la estacion inicial
                $reachesFinal = false;

                // Se va armando el camino hasta que llegue a la estación final o hasta que no haya mas conexiones. Luego
                // se guarda el camino armado en $paths
                while (!$reachesFinal) { //
                    $connectedStations = $station
                        ->connectedStations()
                        ->whereNotIn('stations.id', array_map('intval', explode(',', $pathIds)))
                        ->orderBy('weight', 'asc')
                        ->orderBy('checkpoint', 'desc')
                        ->get();
                    if ($connectedStations->count()) {
                        // Si la estación tiene mas de 1 conexión (ramificaciones), se pone un checkpoint a la primera
                        // conexión que NO se visitará en esta pasada, así en la próxima pasada esté primero en los resultados
                        // de la busqueda de conexiones (orderBy checkpoint desc).
                        if ($connectedStations->count() > 1 && $unvisitedStation = $this->unvisitedConnectedStation($connectedStations, $station, $paths)) {
                            $unvisitedStation->checkpoint();
                        }
                        $station = $connectedStations->first();
                        $station->visit();
                        if ($station->id != $request->finalStation && $station->checkpoint) $station->removeCheckpoint();
                        $station->refresh();
                        $path .= ',' . $station->name;
                        $pathIds .= ',' . $station->id;
                        $reachesFinal = $station->id == $request->finalStation;
                    }
                    else {
                        $reachesFinal = true;
                    }
                }

                // Si la ruta encontrada no se encuentra en el listado de rutas encontradas, se agrega. Si la ruta encontrada
                // ya estaba  en el listado y en el listado solo hay 1, significa que no existe una ruta que lleve de la
                // estacion inicial a la final. Esto evita el ciclo infinito tratando de encontrar rutas
                if (!in_array($path, $paths)) {
                    $paths[] = $path;
                    $unvisitedStations = Network::find($id)->stations()->where('weight', 0)->get();
                    $checkpointStations = Network::find($id)
                        ->stations()
                        ->where('checkpoint', 1)
                        ->get();
                    if ($this->checkpointCompleted($checkpointStations, $paths)) {
                        $checkpointStations = [];
                    }
                }
                else {
                    $unvisitedStations = [];
                    if (count($paths) == 1) {
                        throw ValidationException::withMessages(['error' => 'No existe ruta que lleve desde la estación inicial hasta la estación final elegida']);
                    }
                }
            }
            DB::table('stations')->update(['weight' => 0]);

            // Se recorren las rutas encontradas y se busca la que tenga menos estaciones y que respeta el color de tren
            $stationsWithDiffColor = Network::find($id)->stations()->where('color', '!=', $request->trainColor)->get();
            foreach ($paths as $path) {
                $arrayPath = explode(',', $path);
                // Si se eligio color de tren, se remueve las estaciones con diferente color de cada ruta encontrada
                if ($request->trainColor) {
                    foreach ($stationsWithDiffColor as $stationWithDiffColor) {
                        if ($stationWithDiffColor->color != '' && ($key = array_search($stationWithDiffColor->name, $arrayPath)) !== false) {
                            unset($arrayPath[$key]);
                        }
                    }
                }
                if (!count($shortestPath) || (count($arrayPath) < count($shortestPath))) {
                    $hasColor = false;
                    // Si no se eligió color de tren, las rutas con estaciones con color no se tomaran en cuenta
                    if (!$request->trainColor) {
                        foreach ($stationsWithDiffColor as $stationWithDiffColor) {
                            $hasColor = in_array($stationWithDiffColor->name, $arrayPath);
                            if ($hasColor) {
                                break;
                            }
                        }
                    }
                    $shortestPath = $hasColor ? $shortestPath : $arrayPath;
                }
            }
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', $e->getMessage());
        }
        return back()->with('shortestPath', implode('->', $shortestPath));
    }
}
