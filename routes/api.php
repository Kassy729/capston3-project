<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowsController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\GpsDataController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\MMRController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\TrackController;
use App\Models\Comment;
use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


//로그인, 회원가입
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/test', [AuthController::class, 'test']);

Route::get('/auth/login/google', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/login/google/callback', [GoogleAuthController::class, 'callback']);

//gps데이터 관리
Route::get('/gpsData', [GpsDataController::class, 'gpsData']);
Route::get('/gpsData/check', [GpsDataController::class, 'gpsDataCheck']);

//모든유저검색
Route::get('/allUser', [AuthController::class, 'allUser']);
//프로필 조회
Route::get('/profile', [PostController::class, "profile"]);



//현재로그인 확인
Route::middleware('auth:sanctum')->group(function () {
    //유저확인, 로그아웃, 유저검색, 프로필편집, fcmToken
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('userSearch', [AuthController::class, 'userSearch']);
    //프로필 편집
    Route::post('/profile', [AuthController::class, 'profile']);
    Route::patch('/fcmToken', [AuthController::class, 'fcmToken']);


    // 운동sns
    Route::prefix('post')->group(function () {
        Route::post('/image', [PostController::class, 'image']);
        Route::post('/store', [PostController::class, 'store']);
        Route::get('/index', [PostController::class, 'index'])->name('post.index');
        Route::get('/myIndex', [PostController::class, 'myIndex'])->name('post.myIndex');
        Route::get('/show/{id}', [PostController::class, 'show']);
        Route::put('/update/{id}', [PostController::class, "update"]);
        Route::delete('/{id}', [PostController::class, "destroy"]);
        //일주일 간격으로 요일별 누적 거리 구하는 라우터
        Route::get('/weekRecord', [PostController::class, "weekRecord"]);
    });

    //팔로우요청
    Route::post('/followRequest/{user}', [FollowsController::class, 'request']);
    //팔로우 요청 취소
    Route::post('/cancel/{user}', [FollowsController::class, 'cancel']);

    // 팔로우수락
    Route::post('/follow/{user}', [FollowsController::class, 'store']);
    Route::post('/unFollow/{user}', [FollowsController::class, 'un_follow']);


    //게시글 좋아요
    Route::post('/like/{post}', [LikeController::class, 'store']);

    //댓글
    Route::prefix('comment')->group(function () {
        Route::post('/store/{id}', [CommentController::class, 'store']);
        Route::get('/index/{id}', [CommentController::class, 'index']);
        Route::delete('/destroy/{id}', [CommentController::class, 'destroy']);
        Route::post('/store/reply/{id}', [CommentController::class, 'reply']);
    });

    //mmr
    Route::prefix('/match')->group(function () {
        Route::get('/rank', [MMRController::class, 'rank']);
        Route::get('/friendly', [MMRController::class, 'friendly']);
        Route::get('/gpsData', [MMRController::class, 'gpsData']);
    });


    //누적 기록 불러오기
    Route::prefix('/record')->group(function () {
        Route::get('/distance', [RecordController::class, 'distance']);  //누적거리
        Route::get('/type', [RecordController::class, 'type']);  //자전거 달리기 비율
        Route::get('/totalTime', [RecordController::class, 'totalTime']);  //누적 시간
        Route::get('/totalCalorie', [RecordController::class, 'totalCalorie']);  //누적 칼로리
        Route::get('/altitude', [RecordController::class, 'altitude']);
        Route::post('/goal', [PostController::class, 'goal']);
    });

    //랭킹조회
    Route::prefix('/ranking')->group(function () {
        Route::get('/mmr', [RankingController::class, 'mmr']);
        Route::get('/track', [RankingController::class, 'track']);
        Route::get('/myRank', [RankingController::class, 'myRank']);
        Route::get('/followRank', [RankingController::class, 'followRank']);
    });

    //트랙관련
    Route::prefix('/tracks')->group(function () {
        Route::post('/', [TrackController::class, 'addTrack']);  //트랙 만들기
        Route::get('/', [TrackController::class, 'allTracks']);  //모든 트랙 id리턴
        Route::get('/search', [TrackController::class, 'search']);  //구간에 맞는 트랙 리턴
        Route::get('/', [TrackController::class, 'track']);  //트랙아이디로 트랙 리턴
        Route::get('/checkPoint', [TrackController::class, 'checkPoint']);  //체크포인트
    });

    //알림
    Route::prefix('/notification')->group(function () {
        Route::get('/', [NotificationController::class, 'notification']);
        Route::get('/read/{id}', [NotificationController::class, 'read']);
        Route::delete('/delete/{id}', [NotificationController::class, 'delete']);
    });

    //목표
    Route::prefix('/goal')->group(function () {
        Route::post('/', [GoalController::class, 'goal']);
        Route::get('/check', [GoalController::class, 'checkGoal']);
        Route::get('/success', [GoalController::class, 'successGoal']);
        Route::get('/progress', [GoalController::class, 'progressGoal']);
        Route::get('/all', [GoalController::class, 'allGoal']);
        Route::delete('/delete/{id}', [GoalController::class, 'delete']);
    });

    //뱃지
    Route::prefix('/badge')->group(function () {
        Route::get('/', [BadgeController::class, 'badge']);
    });
});
