<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\DayRecord;
use App\Models\Follow;
use App\Models\Image;
use App\Models\RunRecord;
use App\Models\User;
use App\Notifications\InvoicePaid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function allUser()
    {
        return User::all('id');
    }

    public function register(Request $request)
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'sex' => $request->input('sex'),
            'weight' => $request->input('weight'),
            'profile' => env('DEFAULT_PROFILE'),
            'birth' => $request->input('birth'),
            'introduce' => $request->input('introduce'),
            'location' => $request->input('location'),
            'mmr' => 0,
            'run_mmr' => 0
        ]);

        Badge::create([
            'user_id' => $user->id,
            'first_exercise' => false,
            'bike_distance' => false,
            'bike_distance2' => false,
            'bike_distance3' => false,
            'bike_altitude' => false,
            'run_distance' => false,
            'run_distance2' => false,
            'run_distance3' => false,
            'run_altitude' => false,
            'make_track' => false,
            'make_track2' => false,
            'make_track3' => false
        ]);

        return response([
            'message' => '회원가입 성공',
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);


        if (!Auth::attempt($request->only('email', 'password'))) {
            return response([
                'message' => 'Invalid credentials!'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $login_user = Auth::user();
        $login_token = $login_user->createToken('token')->plainTextToken;

        $cookie = cookie('login_token', $login_token, 60 * 24); // 1 day


        $user = User::with(['followings', 'followers', 'posts'])->find($login_user->id);


        return response([
            'access_token' => $login_token,
            'user' => $user,
        ])->withCookie($cookie);
    }

    public function user()
    {
        $user_id = Auth::user()->id;
        return User::with(['followings', 'followers', 'posts'])->find($user_id);
    }


    public function logout()
    {
        $user = Auth::user();
        $user->tokens()->delete();

        $cookie = Cookie::forget('login_token');
        User::where('id', '=', $user->id)->update(['fcm_token' => null]);

        return response([
            'message' => 'Success'
        ], 200)->withCookie($cookie);
    }

    public function userSearch(Request $request)
    {
        $keyword = $request->query('keyword');

        if ($keyword) {
            $user = User::where('name', 'like', '%' . $keyword . '%')->paginate(10);
        } else {
            return response('', 204);
        }

        //팔로우체크하기
        $follow = Follow::where("follower_id", '=', Auth::user()->id)->get('following_id');
        $follow_array = array();
        for ($i = 0; $i < count($follow); $i++) {
            array_push($follow_array, $follow[$i]->following_id);
        }
        for ($i = 0; $i < count($user); $i++) {
            $user[$i]['followCheck'] = in_array($user[$i]->id, $follow_array);
        }

        return response(
            $user,
            200
        );
    }

    public function image(Request $request)
    {
        if ($request->hasFile("images")) {
            for ($i = 0; $i < count($request->images); $i++) {
                $path[$i] = $request->images[$i]->store('image', 's3');
                $image = Image::create([
                    'image' => basename($path[$i]),
                    'url' => Storage::url($path[$i]),
                    'post_id' => 1
                ]);
            }
        }
        // 이제 Read/Update/Delete를 할 수 있게 하면된다.
        return $image;
    }

    public function profile(Request $request)
    {
        $user = Auth::user();
        $user->name = $request->name;
        $user->weight = $request->weight;
        $user->birth = $request->birth;
        $user->introduce = $request->introduce;
        $user->location = $request->location;

        if ($request->hasFile("profile")) {
            $path = $request->profile->store('profile', 's3');
            $user->profile = Storage::url($path);
        };

        $user->save();

        return $user;
    }

    public function fcmToken(Request $request)
    {
        $user = Auth::user();

        $user->fcm_token = $request->fcmToken;
        $user->save();

        return response([
            "message" => "Success"
        ], 200);
    }
}
