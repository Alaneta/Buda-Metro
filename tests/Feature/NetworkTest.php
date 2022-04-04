<?php

namespace Tests\Feature;

use App\Models\Network;
use App\Models\Station;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NetworkTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_home()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_download()
    {
        $response = $this->get('/download');

        $response->assertStatus(200);
    }

    public function test_network_creation()
    {
        $networkQty = count(Network::all());
        Network::factory()->create();
//        Network::factory()->count(3)->create();

        $this->assertTrue(count(Network::all()) > $networkQty);
    }

    public function test_network_access()
    {
        $network = Network::factory()->create();

        $response = $this->get('/network/' . $network->id);

        $response->assertStatus(200);
    }

    public function test_station_creation()
    {
        $stationQty = count(Station::all());
        $network = Network::factory()->create();
        $station = Station::factory()->make();
        $station->network()->associate($network);
        $station->save();

        $this->assertTrue(count(Station::all()) > $stationQty);
    }

    public function test_station_connection()
    {
        $network = Network::factory()->create();
        $station = Station::factory()->make([
            'name' => 'TEST 1',
            'color' => '',
            'x' => 1,
            'y' => 1
        ]);
        $station->network()->associate($network);
        $station->save();

        $connectedStation = new Station([
            'name' => 'TEST 2',
            'color' => '',
            'x' => 2,
            'y' => 1
        ]);
        $connectedStation->network()->associate($network);
        $connectedStation->save();

        $station->connectedStations()->save($connectedStation);
        $station->refresh();

        $this->assertEquals($connectedStation->id, $station->connectedStations()->first()->id);
    }
}
