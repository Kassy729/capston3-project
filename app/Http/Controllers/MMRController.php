<?php

namespace App\Http\Controllers;

use App\Models\MMR;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class MMRController extends Controller
{
    public function rank(Request $request)
    {
        //랜덤매칭 함수를 호출
        $track_id = $request->query();
        return $this->random_match($track_id);
    }

    //해당 트랙을 나랑 팔로워들 사이에서 달린 리스트

    //친선전
    public function friendly(Request $request)
    {
        $user = Auth::user();
        $track_id = $request->query('track_id');

        $array = array();

        for ($i = 0; $i < count($user->followings); $i++) {
            array_push($array, $user->followings[$i]->id);
        }
        array_push($array, $user->id);

        $post = Post::with('user')->where('track_id', '=', $track_id)->whereIn('user_id', $array)->paginate(10);
        if ($post) {
            return response([
                'followPostList' => $post
            ], 200);
        } else {
            return response('', 204);
        }
    }

    public function gpsData(Request $request)
    {
        $gpsId = $request->query('gpsId');
        // //이거를 이제 mongoDB에 보내서 요청
        // return $match_gps_id = $random_match_post->gps_id;

        //Node에서 GPS_data_id를 받아와서 활동에 저장
        $response = Http::get(env('NODE_SERVER_URL') . "/api/gpsdata/$gpsId");

        $gpsData = json_decode($response->getBody(), true);

        if ($gpsData) {
            return response([
                'gpsData' => $gpsData
            ], 200);
        } else {
            return response('', 204);
        }
    }


    //mmr이 비슷한 사람과 매칭 시키는 함수
    public function random_match($track_id)
    {
        $user_mmr = Auth::user()->mmr;
        $user_id = Auth::user()->id;

        //트랙아이디를 받아와서 해당 트랙을 달린 유저중 mmr이 비슷한 사람(mmr +10 or -10)을 탐색
        $posts = Post::where('track_id', '=', $track_id)->where('user_id', '!=', $user_id)->where('mmr', '<=', $user_mmr + 50)->where('mmr', '>=', $user_mmr - 50)->get();

        //배열의 길이
        $array_length = count($posts);

        $matching = array();

        //array_push함수를 이용해서 해당하는 유저의 id를 배열에 넣음
        for ($i = 0; $i < $array_length; $i++) {
            array_push($matching, $posts[$i]);
        }

        if ($matching) {
            //mmr이 비슷한 유저를 랜덤으로 한명 선정해서 뽑음
            $random = array_rand($matching);
            $random_match_post = $matching[$random];

            $user = User::where('id', '=', $random_match_post->user_id)->first();

            return response([
                'message' => '매칭이 완료 됐습니다',
                'user' => $user,
                'post' => $random_match_post,
            ], 200);
        } else {
            return response([
                'message' => '이 트랙에 매칭 할 수 있는 유저가 없습니다.'
            ], 204);
        }
    }
}


//1km부터 4/1지점으로
