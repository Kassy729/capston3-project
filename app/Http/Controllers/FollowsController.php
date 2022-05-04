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
                'type' => 'followRequest'
            ],
        );
    }

    public function store(User $user)
    {
        //현재 로그인한 유저의 id
        $me = Auth::user();

        if ($user->id != $me->id) {
            $follow = $user->followers()->toggle($me->id);
        } else {
            return response('본인은 팔로우할 수 없습니다', 400);
        }

        if ($follow['attached']) {
            $notification = Notification::create(
                [
                    'mem_id' => $user->id,
                    'target_mem_id' => $me->id,
                    'not_type' => 'follow',
                    'not_message' => $me->name . '님이' . ' ' . '회원님을 팔로우 하기 시작했습니다',
                    'not_url' => '',
                    'read' => false
                ]
            );
            FCMService::send(
                $user->fcm_token,
                [
                    'title' => '알림',
                    'body' => $me->name . '님이' . ' ' . '회원님을 팔로우 합니다'
                ],
                [
                    'id' => $user->id,
                    'type' => 'follow'
                ],
            );
        };

        return response(
            User::where('id', '=', $user->id)->get(['id', 'sex', 'name', 'profile', 'mmr']),
            200
        );
    }
}
