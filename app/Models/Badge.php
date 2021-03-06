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
        "make_track2",
        "make_track3",
        'altitude',
        'altitude2',
        'altitude3'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
