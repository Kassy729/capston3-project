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
        $event = $request->query('event');
        $user = $request->query('id');
        if (!$user) {
            $user = Auth::user()->id;
        }
        $data = Post::where('user_id', '=', $user)->where('event', '=', $event)->get('distance');

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

    public function type(Request $request)
    {
        $user_id = $request->query('id');
        if (!$user_id) {
            $user_id = Auth::user()->id;
        }

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

    public function totalTime(Request $request)
    {
        $user = $request->query('id');
        if (!$user) {
            $user = Auth::user()->id;
        }
        $weekTime = 0;

        $time = Post::where('user_id', '=', $user)->get('time');
        $count = Post::where('user_id', '=', $user)->count();

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

    public function totalCalorie(Request $request)
    {
        $user = $request->query('id');
        if (!$user) {
            $user = Auth::user()->id;
        }
        $weekCalorie = 0;

        $calorie = Post::where('user_id', '=', $user)->get('calorie');
        $count = Post::where('user_id', '=', $user)->count();


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

    public function altitude(Request $request)
    {
        $user = $request->query('id');
        if (!$user) {
            $user = Auth::user()->id;
        }

        $post = Post::where('user_id', '=', $user)->get();

        //누적고도
        $altitude = 0;
        for ($i = 0; $i < count($post); $i++) {
            $altitude += $post[$i]['altitude'];
        }
        return response($altitude, 200);
    }

    //코스
    public function track_count(Request $request)
    {
        $user = $request->query('id');
        if (!$user) {
            $user = Auth::user()->id;
        }
        $response = Http::get(env('NODE_SERVER_URL') . "/api/users/$user");

        $count_track = json_decode($response)->count;

        return response($count_track, 200);
    }
}
