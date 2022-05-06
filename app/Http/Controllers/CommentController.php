<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Reply;
use App\Models\User;
use App\Notifications\InvoicePaid;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate(['content' => ['required']]);

        $me = Auth::user();
        $comment = Comment::create(
            [
                'content' => $request->content,
                'post_id' => $id,
                'user_id' => $me->id,
            ]
        );
        $post = Post::where('id', '=', $id)->first();
        $user = User::find($post->user_id);

        if ($me->id == $user->id) {
            if ($comment) {
                return response([
                    'message' => ['댓글달기 성공'],
                    'comment' => $comment
                ], 201);
            } else {
                return response([
                    'message' => ['실패']
                ], 401);
            }
        }

        //fcm알림설정
        $notification = Notification::create(
            [
                'mem_id' => $user->id,
                'target_mem_id' => $me->id,
                'not_type' => 'comment',
                'not_message' => $me->name . '님이' . ' ' . '댓글을 남겼습니다: ' . $request->content,
                'not_url' => '',
                'read' => false,
                'post_id' => $post->id
            ]
        );
        FCMService::send(
            $user->fcm_token,
            [
                'title' => '알림',
                'body' => $me->name . '님이' . ' ' . '댓글을 남겼습니다: ' . $request->content
            ],
            [
                'postId' => $id,
                'type' => 'comment',
                'notId' => $notification->id,
                'target_mem' => $me,
            ],
        );

        if ($comment) {
            return response([
                'message' => ['댓글달기 성공'],
                'comment' => $comment
            ], 201);
        } else {
            return response([
                'message' => ['실패']
            ], 401);
        }
    }

    public function index($id)
    {
        $comments = Comment::where('post_id', '=', $id)->paginate(10);
        if ($comments) {
            return response(
                $comments,
                200
            );
        } else {
            return response('', 204);
        }
    }



    public function reply(Request $request, $id)
    {
        $reply = Reply::create(
            [
                'comment_id' => $id,
                'user_id' => Auth::user()->id,
                'content' => $request->content
            ]
        );

        if ($reply) {
            return response([
                'message' => ['대댓글달기 성공'],
                'reply' => $reply
            ], 201);
        } else {
            return response([
                'message' => ['실패']
            ], 401);
        }
    }

    public function destroy($id)
    {

        $user = Auth::user()->id;
        $comment = Comment::find($id);

        $user_id = $comment->user_id;

        if ($user == $user_id) {
            $comment->delete();
            return "댓글 삭제 성공";
        } else {
            return abort(401);
        }
    }
}
