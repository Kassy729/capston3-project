<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class BadgeController extends Controller
{
    public function badge()
    {
        $user = Auth::user();
        $post = Post::where('user_id', '=', $user->id)->get();

        //첫 운동
        if (count($post) >= 1) {
            Badge::where('user_id', '=', $user->id)->update(['first_exercise' => true]);
        } else {
            Badge::where('user_id', '=', $user->id)->update(['first_exercise' => false]);
        }


        //자전거 기록
        $bikeData = Post::where('user_id', '=', $user->id)->where('event', '=', 'B')->get();
        //누적거리
        $distance = 0;
        for ($i = 0; $i < count($bikeData); $i++) {
            $distance += $bikeData[$i]['distance'];
        }

        //거리 100km달성 뱃지
        if ($distance >= 100) {
            Badge::where('user_id', '=', $user->id)->update(['bike_distance' => true]);
        } else {
            Badge::where('user_id', '=', $user->id)->update(['bike_distance' => false]);
        }

        //거리 500km달성 뱃지
        if ($distance >= 500) {
            Badge::where('user_id', '=', $user->id)->update(['bike_distance2' => true]);
        } else {
            Badge::where('user_id', '=', $user->id)->update(['bike_distance2' => false]);
        }
        //거리 1000km달성 뱃지
        if ($distance >= 1000) {
            Badge::where('user_id', '=', $user->id)->update(['bike_distance3' => true]);
        } else {
            Badge::where('user_id', '=', $user->id)->update(['bike_distance3' => false]);
        }



        //달리기 기록
        $runData = Post::where('user_id', '=', $user->id)->where('event', '=', 'R')->get();
        //누적거리
        $run_distance = 0;
        for ($i = 0; $i < count($runData); $i++) {
            $run_distance += $runData[$i]['distance'];
        }

        //거리 100km달성 뱃지
        if ($run_distance >= 100) {
            Badge::where('user_id', '=', $user->id)->update(['run_distance' => true]);
        } else {
            Badge::where('user_id', '=', $user->id)->update(['run_distance' => false]);
        }
        //거리 500km달성 뱃지
        if ($run_distance >= 500) {
            Badge::where('user_id', '=', $user->id)->update(['run_distance2' => true]);
        } else {
            Badge::where('user_id', '=', $user->id)->update(['run_distance2' => false]);
        }
        //거리 1000km달성 뱃지
        if ($run_distance >= 1000) {
            Badge::where('user_id', '=', $user->id)->update(['run_distance3' => true]);
        } else {
            Badge::where('user_id', '=', $user->id)->update(['run_distance3' => false]);
        }

        //누적고도
        $altitude = 0;
        for ($i = 0; $i < count($bikeData); $i++) {
            $altitude += $bikeData[$i]['altitude'];
        }
        //누적고도
        for ($i = 0; $i < count($runData); $i++) {
            $altitude += $runData[$i]['altitude'];
        }

        //누적 고도
        if ($altitude >= 10000) {
            Badge::where('user_id', '=', $user->id)->update(['altitude' => true]);
        } else {
            Badge::where('user_id', '=', $user->id)->update(['altitude' => false]);
        }
        if ($altitude >= 20000) {
            Badge::where('user_id', '=', $user->id)->update(['altitude2' => true]);
        } else {
            Badge::where('user_id', '=', $user->id)->update(['altitude2' => false]);
        }
        if ($altitude >= 30000) {
            Badge::where('user_id', '=', $user->id)->update(['altitude3' => true]);
        } else {
            Badge::where('user_id', '=', $user->id)->update(['altitude3' => false]);
        }


        //코스
        $id = Auth::user()->id;
        $response = Http::get(env('NODE_SERVER_URL') . "/api/users/$id");

        $count_track = json_decode($response)->count;

        if ($count_track >= 3) {
            Badge::where('user_id', '=', $user->id)->update(['make_track' => true]);
        } else {
            Badge::where('user_id', '=', $user->id)->update(['make_track' => false]);
        }
        if ($count_track >= 20) {
            Badge::where('user_id', '=', $user->id)->update(['make_track2' => true]);
        } else {
            Badge::where('user_id', '=', $user->id)->update(['make_track2' => false]);
        }
        if ($count_track >= 50) {
            Badge::where('user_id', '=', $user->id)->update(['make_track3' => true]);
        } else {
            Badge::where('user_id', '=', $user->id)->update(['make_track3' => false]);
        }
    }
}
