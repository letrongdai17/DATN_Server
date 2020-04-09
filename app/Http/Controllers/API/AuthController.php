<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = [
          'email'       =>  $request->get('email'),
          'password'    =>  $request->get('password'),
        ];

        $user = User::where('email', '=', $request->get('email'))->first();

        if (!$user) {
            return response()->json(['Email or password is invalid'], 401);
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('Project')->accessToken;
            $userInfo = $this->getUserInfo($user);

            return response()->json([
                'token'=>$token,
                'user'=>$userInfo,
            ], 200);
        } else {
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['User not found'], 403);
        }

        $userInfo = $this->getUserInfo($user);
        return response()->json(['user'=>$userInfo], 200);
    }

    public function register(Request $request)
    {
        $credentials = [
            'name'      =>      $request->get('name'),
            'email'     =>      $request->get('email'),
            'tel'       =>      $request->get('tel'),
            'password'  =>      \Hash::make($request->get('password')),
            'description'=>     $request->description || '',
            'position'  =>      $request->position || '',
        ];

        $user = User::create($credentials);
        $token = $user->createToken('Project')->accessToken;

        return response()->json(['token'=>$token, 'user'=>$user], 201);
    }

    protected function getUserInfo($user)
    {
        return [
            'id'    =>  $user['id'],
            'email' =>  $user['email'],
            'name'  =>  $user['name'],
            'tel'   =>  $user['tel'],
        ];
    }
}
