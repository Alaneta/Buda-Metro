<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'x',
        'y'
    ];

    public function network()
    {
        return $this->belongsTo(Network::class);
    }

    public function connectedStations()
    {
        return $this->belongsToMany(Station::class, 'station_station', 'station_id', 'connected_station_id');
    }

    public function visit() {
        $this->weight += 1;
        $this->save();
    }

    public function checkpoint() {
        $this->checkpoint = 1;
        $this->save();
    }

    public function removeCheckpoint() {
        $this->checkpoint = 0;
        $this->save();
    }
}
