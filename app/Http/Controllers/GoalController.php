<?php

namespace App\Http\Controllers;

use App\Models\CheckPoint;
use App\Models\Goal;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use League\CommonMark\Extension\SmartPunct\EllipsesParser;

class GoalController extends Controller
{
    public function goal(Request $request)
    {
        //날짜 입력 받기
        //그 날짜 범위의 활동만 주행거리로 누적
        //날짜 지정
        $title = $request->title;
        $goalDistance = $request->goal;
        $firstDate = $request->firstDate;
        $lastDate = $request->lastDate;
        $event = $request->event;

        $user = Auth::user();

        // (('사용자입력시작일' <= DB시작일 and DB시작일 <= '사용자입력종료일') or ('사용자입력시작일' <= DB종료일 and DB종료일 <= '사용자입력종료일') or (DB시작일 <= '사용자입력시작일' and '사용자입력종료일' <= DB종료일))
        // if ($event == 'B') {
        //     $query_one = DB::table('goals')->where('user_id', '=', $user->id)->where('event', '=', 'B')->where('firstDate', '>=', $firstDate)->where('firstDate', '<=', $lastDate);

        //     $query_two = DB::table('goals')->where('user_id', '=', $user->id)->where('event', '=', 'B')->where('lastDate', '>=', $firstDate)->where('lastDate', '<=', $lastDate);

        //     $query_three = DB::table('goals')->where('user_id', '=', $user->id)->where('event', '=', 'B')->where('firstDate', '<=', $firstDate)->where('lastDate', '>=', $lastDate)->union($query_one)->union($query_two)->get();
        // } else {
        //     $query_one = DB::table('goals')->where('user_id', '=', $user->id)->where('event', '=', 'R')->where('firstDate', '>=', $firstDate)->where('firstDate', '<=', $lastDate);

        //     $query_two = DB::table('goals')->where('user_id', '=', $user->id)->where('event', '=', 'R')->where('lastDate', '>=', $firstDate)->where('lastDate', '<=', $lastDate);

        //     $query_three = DB::table('goals')->where('user_id', '=', $user->id)->where('event', '=', 'R')->where('firstDate', '<=', $firstDate)->where('lastDate', '>=', $lastDate)->union($query_one)->union($query_two)->get();
        // }
        // if (count($query_three) == 0) {
        return response([
            'goal' => Goal::create([
                'user_id' => $user->id,
                'title' => $title,
                'goalDistance' => $goalDistance,
                'firstDate' => $firstDate,
                'lastDate' => $lastDate,
                'success' => false,
                'event' => $event
            ])
        ], 201);
        // } else {
        //     return response([
        //         'message' => '겹치는 날짜는 목표를 설정 할 수 없습니다'
        //     ]);
        // }
    }

    public function checkGoal()
    {
        //현재시간
        $today = date('Y-m-d', time());
        $user = Auth::user();

        //해당 기간내 목표
        $goal = Goal::where('user_id', '=', $user->id)->where('firstDate', '<=', $today)->where('lastDate', '>=', $today)->where('event', '=', 'B')->get();
        $run_goal = Goal::where('user_id', '=', $user->id)->where('firstDate', '<=', $today)->where('lastDate', '>=', $today)->where('event', '=', 'R')->get();

        //설정 목표 기간내 해당하는 활동
        $array = array();
        $run_array = array();

        //기간내 자전거 활동
        for ($i = 0; $i < count($goal); $i++) {
            $post = Post::where('user_id', '=', $user->id)->where('event', '=', 'B')->where('date', '>=', $goal[$i]['firstDate'])->where('date', '<=', $goal[$i]['lastDate'])->orderby('date')->get();
            array_push($array, $post);
        }


        //기간내 달리기 활동
        for ($i = 0; $i < count($run_goal); $i++) {
            $post = Post::where('user_id', '=', $user->id)->where('event', '=', 'R')->where('date', '>=', $run_goal[$i]['firstDate'])->where('date', '<=', $run_goal[$i]['lastDate'])->orderby('date')->get();
            array_push($run_array, $post);
        }

        //해당 운동의 누적거리 계산
        $data = array();
        $run_data = array();

        //자전거 데이터 0으로 설정
        for ($i = 0; $i < count($array); $i++) {
            $data[$i]['distance'] = 0;
        }

        //달리기 데이터 0으로 설정
        for ($i = 0; $i < count($run_array); $i++) {
            $run_data[$i]['distance'] = 0;
        }



        //기간내 자전거 누적거리
        for ($i = 0; $i < count($array); $i++) {
            for ($y = 0; $y < count($array[$i]); $y++) {
                $data[$i]['distance'] += $array[$i][$y]['distance'];
            }
        }

        //기간내 달리기 누적거리
        for ($i = 0; $i < count($run_array); $i++) {
            for ($y = 0; $y < count($run_array[$i]); $y++) {
                $run_data[$i]['distance'] += $run_array[$i][$y]['distance'];
            }
        }

        //자전거 목표 성공여부 체크
        for ($i = 0; $i < count($goal); $i++) {
            if ($data[$i]['distance'] >= $goal[$i]['goalDistance']) {
                Goal::where('id', '=', $goal[$i]['id'])->update(['success' => true]);
            }
            $goal[$i]['progress'] = floor($data[$i]['distance'] / $goal[$i]['goalDistance'] * 100);
            if ($goal[$i]['progress'] >= 100) {
                $goal[$i]['progress'] = 100;
            }
        }

        //달리기 목표 성공여부 체크
        for ($i = 0; $i < count($run_goal); $i++) {
            if ($run_data[$i]['distance'] >= $run_goal[$i]['goalDistance']) {
                Goal::where('id', '=', $run_goal[$i]['id'])->update(['success' => true]);
            }
            $run_goal[$i]['progress'] = floor($run_data[$i]['distance'] / $run_goal[$i]['goalDistance'] * 100);
            if ($run_goal[$i]['progress'] >= 100) {
                $run_goal[$i]['progress'] = 100;
            }
        }

        $myGoal['bike'] = $goal;
        $myGoal['run'] = $run_goal;

        if ($myGoal) {
            return response($myGoal, 200);
        } else {
            return response('', 204);
        }
    }

    public function successGoal()
    {
        $user = Auth::user();
        $goal = Goal::where('user_id', '=', $user->id)->where('success', '=', 1)->orderby('firstDate')->get();

        if ($goal) {
            return response($goal, 200);
        } else {
            return response('', 204);
        }
    }

    public function progressGoal()
    {
        $today = date('Y-m-d', time());
        $user = Auth::user();

        $goal = Goal::where('user_id', '=', $user->id)->where('success', '=', 0)->where('firstDate', '<=', $today)->where('lastDate', '>=', $today)->orderby('firstDate')->get();

        if ($goal) {
            return response($goal, 200);
        } else {
            return response('', 204);
        }
    }

    public function allGoal()
    {
        $user = Auth::user();
        $goal = Goal::where('user_id', '=', $user->id)->orderby('firstDate')->get();

        if ($goal) {
            return response($goal, 200);
        } else {
            return response('', 204);
        }
    }

    public function delete($id)
    {
        $goal = Goal::findOrFail($id);
        $user = Auth::user()->id;
        $user_id = $goal->user_id;

        //게시물 삭제
        if ($user == $user_id) {
            $goal->delete();
            return $goal;
        } else {
            return abort(401);
        }
    }
}
