<?php

namespace App\Http\Controllers;

use App\Models\follow;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\InvoicePaid;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowsController extends Controller
{
    public function request(User $user)
    {
        $me = Auth::user();

        Notification::create(
            [
                'mem_id' => $user->id,
                'target_mem_id' => $me->id,
                'not_type' => 'followRequest',
                'not_message' => $me->name . '님이' . ' ' . '팔로우를 요청했습니다.',
                'not_url' => '',
                'read' => false
            ]
        );
        FCMService::send(
            $user->fcm_token,
            [
                'title' => '알림',
                'body' => $me->name . '님이' . ' ' . '팔로우를 요청했습니다.'
            ],
            [
                'id' => $user->id,
                'target_mem_id' => $me,
                'type' => 'followRequest'
            ],
        );
    }


    //팔로우 취소
    public function cancel(User $user)
    {
        Notification::where('mem_id', '=', $user->id)->where('target_mem_id', '=', Auth::user()->id)->where('not_type', '=', 'followRequest')->delete();
    }

    public function store(User $user)
    {
        //현재 로그인한 유저의 id
        $me = User::where('id', '=', Auth::user()->id)->first();


        if ($user->id != $me->id) {
            $follow = $me->followers()->toggle($user->id);
        } else {
            return response('본인은 팔로우할 수 없습니다', 400);
        }

        if ($follow['attached']) {
            //팔로우를 수락했을 때 내가 받는 시작 알림
            $notification = Notification::create(
                [
                    'mem_id' => $me->id,
                    'target_mem_id' => $user->id,
                    'not_type' => 'follow',
                    'not_message' => $user->name . '님이' . ' ' . '회원님을 팔로우 하기 시작했습니다',
                    'not_url' => '',
                    'read' => false
                ]
            );
            FCMService::send(
                $me->fcm_token,
                [
                    'title' => '알림',
                    'body' => $user->name . '님이' . ' ' . '회원님을 팔로우 하기 시작했습니다'
                ],
                [
                    'id' => $me->id,
                    'target_mem_id' => $user->id,
                    'type' => 'follow',
                    'notId' => $notification->id,
                ],
            );

            //팔로우 수락했을때 알림
            $notification = Notification::create(
                [
                    'mem_id' => $user->id,
                    'target_mem_id' => $me->id,
                    'not_type' => 'follow',
                    'not_message' => $me->name . '님이' . ' ' . '팔로우 요청을 수락했습니다.',
                    'not_url' => '',
                    'read' => false
                ]
            );
            FCMService::send(
                $user->fcm_token,
                [
                    'title' => '알림',
                    'body' => $me->name . '님이' . ' ' . '팔로우 요청을 수락했습니다.'
                ],
                [
                    'id' => $user->id,
                    'target_mem_id' => $me->id,
                    'type' => 'follow',
                    'notId' => $notification->id,
                ],
            );
        };

        return response(
            User::where('id', '=', $user->id)->get(['id', 'sex', 'name', 'profile', 'mmr']),
            200
        );
    }

    public function un_follow(User $user)
    {
        //현재 로그인한 유저의 id
        $me = User::where('id', '=', Auth::user()->id)->first();

        Notification::where('mem_id', '=', $user->id)->where('target_mem_id', '=', $me->id)->where('not_type', '=', 'followRequest')->delete();

        if ($user->id != $me->id) {
            $me->followings()->toggle($user->id);
            return response('', 200);
        } else {
            return response('본인은 팔로우할 수 없습니다', 400);
        }
    }
}
