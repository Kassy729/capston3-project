<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BadgeController extends Controller
{
    public function badge()
    {
        $user = Auth::user();
        $post = Post::where('user_id', '=', $user->id)->get();

        //첫 운동
        if (count($post) >= 1) {
            Badge::where('user_id', '=', $user->id)->update(['first_exercise' => true]);
        }


        //자전거 기록
        $bikeData = Post::where('user_id', '=', $user->id)->where('event', '=', 'B')->get();
        //누적거리
        $distance = 0;
        for ($i = 0; $i < count($bikeData); $i++) {
            $distance += $bikeData[$i]['distance'];
        }
        //누적고도
        $altitude = 0;
        for ($i = 0; $i < count($bikeData); $i++) {
            $altitude += $bikeData[$i]['altitude'];
        }
        //거리 100km달성 뱃지
        if ($distance >= 100) {
            Badge::where('user_id', '=', $user->id)->update(['bike_distance' => true]);
        }
        //거리 500km달성 뱃지
        if ($distance >= 500) {
            Badge::where('user_id', '=', $user->id)->update(['bike_distance2' => true]);
        }
        //거리 1000km달성 뱃지
        if ($distance >= 1000) {
            Badge::where('user_id', '=', $user->id)->update(['bike_distance3' => true]);
        }
        //누적 고도
        if ($altitude >= 10000) {
            Badge::where('user_id', '=', $user->id)->update(['bike_altitude' => true]);
        }


        //달리기 기록
        $runData = Post::where('user_id', '=', $user->id)->where('event', '=', 'R')->get();
        //누적거리
        $run_distance = 0;
        for ($i = 0; $i < count($runData); $i++) {
            $run_distance += $runData[$i]['distance'];
        }
        //누적고도
        $run_altitude = 0;
        for ($i = 0; $i < count($runData); $i++) {
            $run_altitude += $runData[$i]['altitude'];
        }
        //거리 100km달성 뱃지
        if ($run_distance >= 100) {
            Badge::where('user_id', '=', $user->id)->update(['run_distance' => true]);
        }
        //거리 500km달성 뱃지
        if ($run_distance >= 500) {
            Badge::where('user_id', '=', $user->id)->update(['run_distance2' => true]);
        }
        //거리 1000km달성 뱃지
        if ($run_distance >= 1000) {
            Badge::where('user_id', '=', $user->id)->update(['run_distance3' => true]);
        }
        //누적 고도
        if ($run_altitude >= 10000) {
            Badge::where('user_id', '=', $user->id)->update(['run_altitude' => true]);
        }


        //코스
        
    }
}
