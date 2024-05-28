<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\EmailVerifyRequest;
use App\Http\Requests\SendOtpRequest;
use App\Models\User;
use App\Services\PasswordRestTokenService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    protected $userService;
    protected $PasswordRestTokenService;
    public function __construct(UserService $userService, PasswordRestTokenService $PasswordRestTokenService)
    {
        $this->userService = $userService;
        $this->PasswordRestTokenService = $PasswordRestTokenService;
        // $this->middleware('auth:api', ['except' => ['login', 'adminLogin', 'emailVerify']]);
    }


    /**
     * student login
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     *
     * login a user
     */

    public function login(AuthRequest $request)
    {
        //validate the request
        $validated = $request->validated();

        $user = User::with('clinics')->where('email', $validated['email'])->where('is_active', 1)->first();

        if (!$user) {
            return ResponseHelper::error(trans('messages.authentication_failed'), [], 401);
        }

        if ($user && Hash::check($validated['password'], $user->password)) {

            $user_token = $user->createToken('appToken')->accessToken;
            $userType = $user->roles()->first()->name;


            //add to spatie activity log
            activity()
                ->causedBy($user)
                ->log('Logged in');
            return ResponseHelper::success(trans('messages.login_success'), [
                'token' => $user_token,
                'user_role' => $userType,
                'user' => $user
            ]);
        } else {
            return ResponseHelper::error(trans('messages.authentication_failed'), [], 401);
        }

        //check login and return token
        if (!$token = auth()->attempt($validated)) {
            return ResponseHelper::error(trans('messages.Unauthorized'), [], 401);
        }
        return $this->respondWithToken($token);
    }

    public function respondWithToken($token)
    {
        return ResponseHelper::success(trans('messages.login_success'), [
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user()
        ]);
    }

    /**
     * logout
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     *
     * logout a user
     */

    public function logout()
    {
        $logout = $this->PasswordRestTokenService->logout();


        if ($logout) {
            //add to spatie activity log
            activity()
                ->causedBy(Auth::user())
                ->log('Logged out');
            return ResponseHelper::success(trans('messages.logout_success'));
        } else {
            return ResponseHelper::error(trans('messages.logout_failed'));
        }
    }


    //MOBILE API'S

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * sendOtp
     */

    public function sendOtp(SendOtpRequest $request)
    {
        try {
            $data = $request->validated();
            $response = $this->userService->sendOtp($data);
            if ($response['status']) {
                return ResponseHelper::success($response['message'], $response['data']);
            } else {
                return ResponseHelper::error($response['message']);
            }
        } catch (\Exception $e) {
            return ResponseHelper::error($response['message']);
        }
    }
}
