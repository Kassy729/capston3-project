<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Record;
use App\Models\User;
use Facade\FlareClient\Http\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RecordController extends Controller
{
    public function distance(Request $request)
    {
        $user = Auth::user();
        $event = $request->query('event');

        $data = Post::where('user_id', '=', $user->id)->where('event', '=', $event)->get('distance');

        $distance = 0;
        for ($i = 0; $i < count($data); $i++) {
            $distance += $data[$i]['distance'];
        }

        if ($distance) {
            return response([
                "distance" => $distance
            ], 200);
        } else {
            return response('', 204);
        }
    }

    public function type()
    {
        $user_id = Auth::user()->id;
        $total_count = Post::where('user_id', '=', $user_id)->count();
        $bike_count = Post::where('user_id', '=', $user_id)->where('event', '=', 'B')->count();

        if ($total_count != 0) {
            $bike_percentage = ($bike_count / $total_count) * 100;
            $run_percentage = 100 - $bike_percentage;
            return response([
                'B' => $bike_percentage,
                'R' => $run_percentage
            ], 200);
        } else {
            return response('', 204);
        }
    }

    public function totalTime()
    {
        $user = Auth::user();
        $weekTime = 0;

        $time = Post::where('user_id', '=', $user->id)->get('time');
        $count = Post::where('user_id', '=', $user->id)->count();

        for ($i = 0; $i < $count; $i++) {
            $weekTime += $time[$i]->time;
        };

        if ($weekTime) {
            return response(
                $weekTime,
                200
            );
        } else {
            return response(
                '',
                204
            );
        }
    }

    public function totalCalorie()
    {
        $user = Auth::user();
        $weekCalorie = 0;

        $calorie = Post::where('user_id', '=', $user->id)->get('calorie');
        $count = Post::where('user_id', '=', $user->id)->count();


        for ($i = 0; $i < $count; $i++) {
            $weekCalorie += $calorie[$i]->calorie;
        };

        if ($weekCalorie) {
            return response(
                $weekCalorie,
                200
            );
        } else {
            return response(
                '',
                204
            );
        }
    }
}
