<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "first_exercise",
        "bike_distance",
        "bike_distance2",
        "bike_distance3",
        "bike_altitude",
        "run_distance",
        "run_distance2",
        "run_distance3",
        "run_altitude",
        "make_track",
        "rank"
    ];
}
