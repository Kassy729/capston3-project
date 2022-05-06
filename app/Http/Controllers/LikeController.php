<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use App\Notifications\InvoicePaid;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function store(Post $post)
    {
        //나
        $me = Auth::user();
        //상대방
        $user = User::find($post->user_id);
        $like = $post->likes()->toggle($me->id);

        if ($me->id == $user->id) {
            return $like;
        }

        if ($like['attached']) {
            if ($like['attached'] == [$me->id]) {
                $notification = Notification::create(
                    [
                        'mem_id' => $user->id,
                        'target_mem_id' => $me->id,
                        'not_type' => 'like',
                        'not_message' => $me->name . '님이' . ' ' . '회원님의' . $post->title . ' 게시물을 좋아합니다',
                        'not_url' => '',
                        'read' => false,
                        'post_id' => $post->id
                    ]
                );
                FCMService::send(
                    $user->fcm_token,
                    [
                        'title' => '알림',
                        'body' => $me->name . '님이' . ' ' . '회원님의 ' . $post->title . ' 게시물을 좋아합니다'
                    ],
                    [
                        'postId' => $post->id,
                        'type' => 'like',
                        'notId' => $notification->id,
                        'mem_id' => $user->id,
                        'target_mem' => $me,
                    ],
                );
            }
        }
        return $like;
    }
}
