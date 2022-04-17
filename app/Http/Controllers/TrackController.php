<?php

namespace App\Http\Controllers;

use App\Models\CheckPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TrackController extends Controller
{
    public function addTrack(Request $request)
    {
        $gpsData = $request->gpsData;


        $response = Http::post('http://13.124.24.179/api/tracks', $gpsData);
        return json_decode($response, true);
    }

    public function allTracks()
    {
        //Node에서 track_id를 리턴
        $response = Http::get('http://13.124.24.179/api/tracks');
        //JSON 문자열을 변환하여 값을 추출
        return json_decode($response, true);
    }

    public function search(Request $request)
    {
        $bound1 = $request->query('bound1');
        $bound2 = $request->query('bound2');
        $bound3 = $request->query('bound3');
        $bound4 = $request->query('bound4');
        $event = $request->query('event');

        //쿼리스트링을 만듦
        $query = "bounds" . '=' . $bound1  . '&' .  "bounds" . '=' . $bound2 . '&' . "bounds" . '=' . $bound3 . '&' . "bounds" . '=' . $bound4 . '&' . "event" . '=' . $event;
        //Node에서 track_id를 리턴
        $response = Http::get("http://13.124.24.179/api/tracks/search?$query");

        //JSON 문자열을 변환하여 값을 추출
        return json_decode($response, true);
    }

    public function track(Request $request)
    {
        $id = $request->query('track_id');
        //Node에서 track_id를 리턴
        $response = Http::get("http://13.124.24.179/api/tracks/$id");
        //JSON 문자열을 변환하여 값을 추출
        return json_decode($response, true);
    }

    public function checkPoint(Request $request)
    {
        $user = Auth::user();

        $checkPoint = $request->query('checkPoint');
        $track_id = $request->query('track_id');
        $time = $request->query('time');

        //내 기존 기록 불러옴
        $myCheckPoint = CheckPoint::where('checkPoint', '=', $checkPoint)->where('track_id', '=', $track_id)->where('user_id', '=', $user->id)->first();

        $allCheckPoint = CheckPoint::where('checkPoint', '=', $checkPoint)->where('track_id', '=', $track_id)->where('user_id', '!=', $user->id)->orderby('time')->get();

        //트랙에서 내 기록이 없거나 더 좋은 결과를 냈을 경우 체크포인트 저장
        if ($myCheckPoint == null) {
            if (count($allCheckPoint) == 0) {
                CheckPoint::create([
                    'user_id' => $user->id,
                    'track_id' => $track_id,
                    'time' => $time,
                    'checkPoint' => $checkPoint
                ]);
                return response([
                    'rank' => 100
                ], 200);
            }
            CheckPoint::create([
                'user_id' => $user->id,
                'track_id' => $track_id,
                'time' => $time,
                'checkPoint' => $checkPoint
            ]);
        } else if ($myCheckPoint['time'] > $time) {
            CheckPoint::where('user_id', '=', $user->id)->where('checkPoint', '=', $checkPoint)->where('track_id', '=', $track_id)->update(['time' => $time]);
        }


        if (count($allCheckPoint) == 0) {
            return response([
                'rank' => 100
            ], 200);
        }

        //제일 늦은 시간 보다 늦으면 상위 100%
        if ($allCheckPoint[count($allCheckPoint) - 1]['time'] < $time) {
            return response([
                'rank' => 100
            ], 200);
        }


        for ($i = 0; $i < count($allCheckPoint); $i++) {
            $allCheckPoint[$i]['rank'] = ($i + 1) / count($allCheckPoint) * 100;
            if ($allCheckPoint[$i]['time'] > $time) {
                return response([
                    'rank' => ($i + 1) / (count($allCheckPoint) + 1) * 100
                ], 200);
            } else if ($allCheckPoint[$i]['time'] == $time) {
                return response([
                    'rank' => $allCheckPoint[$i]['rank']
                ], 200);
            }
        }
    }
}


//트랙좌표 index로 확인 가능
