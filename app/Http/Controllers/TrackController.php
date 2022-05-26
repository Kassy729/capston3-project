<?php

namespace App\Http\Controllers;

use App\Models\CheckPoint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TrackController extends Controller
{
    public function addTrack(Request $request)
    {
        $gpsData = $request->gpsData;

        $response = Http::post(env('NODE_SERVER_URL') . '/api/tracks', $gpsData);
        return json_decode($response, true);
    }

    public function allTracks()
    {
        //Node에서 track_id를 리턴
        $response = Http::get(env('NODE_SERVER_URL') . '/api/tracks');
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
        $response = Http::get(env('NODE_SERVER_URL') . "/api/tracks/search?$query");

        //JSON 문자열을 변환하여 값을 추출
        return json_decode($response, true);
    }

    public function track(Request $request)
    {
        $id = $request->query('track_id');
        //Node에서 track_id를 리턴
        $response = Http::get(env('NODE_SERVER_URL') . "/api/tracks/$id");
        //JSON 문자열을 변환하여 값을 추출
        $track = json_decode($response, true);

        $profile = User::where('id', '=', $track['user']['userId'])->first('profile');
        $track['user']['profile'] = $profile;

        if ($track) {
            return response($track, 200);
        } else {
            return response('', 204);
        }
    }

    public function checkPoint(Request $request)
    {
        $user = Auth::user();

        //쿼리스트링으로 체크포인트, 트랙아이디, 시간을 받음
        $checkPoint = $request->query('checkPoint');
        $track_id = $request->query('track_id');
        $time = $request->query('time');

        //내 기존 기록 불러옴
        $myCheckPoint = CheckPoint::where('checkPoint', '=', $checkPoint)->where('track_id', '=', $track_id)->where('user_id', '=', $user->id)->first();

        //나를 제외한 다른 사람들의 체크포인트 기록을 가져옴
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
