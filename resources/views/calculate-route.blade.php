@extends('layouts.base_layout')

@section('css_custom_files')
@stop

@section('css_custom_style')
    <style>
    </style>
@stop

@section('content')
    @if(session('shortestPath') || (session('shortestPath') && session('shortestPath') == '') || session('error'))
        <script type="text/javascript">
            function successMsg() {
                Swal.fire(
                    '{{ session('error') }}' ? 'Hubo un error' : 'Cálculo finalizado con éxito!',
                    '{{ session('error') }}' ? '{{ session('error') }}' : ('{{ session('shortestPath') }}' ? '{{ session('shortestPath') }}' : 'No existe ruta posible'),
                    '{{ session('error') }}' ? 'error' : 'success'
                );
            }

            window.onload = successMsg;
        </script>
    @endif
    <!-- ======= Calculate Route Section ======= -->
    <section id="calculateRoute">
        <div class="container" data-aos="fade-up">
            <div class="section-header">
                <h3 class="section-title">Red en {{ $network->name }}</h3>
                <p class="section-description">Calcular ruta con menor cantidad de estaciones</p>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <form method="POST" action="{{ route("network.calculateShortestRoute", $network->id) }}"
                          type="multipart/form-data">
                        @csrf
                        <div class="form-group mt-10">
                            <label for="initialStation">Estación inicial</label>
                            <select class="form-control" name="initialStation" id="initialStation">
                                @foreach($network->stations as $station)
                                    <option
                                        {{ old('initialStation') == $station->id ? "selected" : "" }} value="{{ $station->id }}">{{ $station->name }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('initialStation'))
                                <span class="error">
                                <strong>{{ $errors->first('initialStation') }}</strong>
                            </span>
                            @endif
                        </div>
                        <div class="form-group mt-10">
                            <label for="finalStation">Estación final</label>
                            <select class="form-control" name="finalStation" id="finalStation">
                                @foreach($network->stations as $station)
                                    <option
                                        {{ old('finalStation') == $station->id ? "selected" : "" }} value="{{ $station->id }}">{{ $station->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mt-10">
                            <label for="trainColor">Color de tren</label>
                            <select class="form-control" name="trainColor" id="trainColor">
                                <option value="" selected>Seleccione color de tren (opcional)</option>
                                <option>Rojo</option>
                                <option>Verde</option>
                            </select>
                        </div>
                        <div class="text-center mt-10">
                            <button type="submit" class="btn btn-primary" id="save">Calcular</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section><!-- End Calculate Route Section -->
@stop

@section('js_custom_files')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stop

@push('scripts')
    <script>
        $(document).ready(function () {

        });
    </script>
@endpush
