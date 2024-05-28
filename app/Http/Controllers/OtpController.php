<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Helpers\ResponseHelper;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyMobileOtpRequest;
use App\Models\Otp;
use App\Models\User;
use App\Services\ClinicService;
use App\Services\SmsService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class OtpController extends Controller
{
    protected $userService;
    protected $clinicService;
    protected $SmsService;

    public function __construct(UserService $userService, ClinicService $clinicService, SmsService $SmsService)
    {
        $this->userService = $userService;
        $this->clinicService = $clinicService;
        $this->SmsService = $SmsService;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * send otp
     */

    public function sendOtp(SendOtpRequest $request)
    {
        $validated = $request->validated();
        $mobile_number = $validated['recipients'];
        //check mobile number is already exist
        $user = $this->userService->getUserByMobileNumber($mobile_number);
        if (!$user) {
            return ['status' => false, 'message' => 'Mobile number is not registered.'];
        }
        try {
            $otp = rand(1000, 9999);
            $validated['message'] = $otp;
            $response = $this->SmsService->sendMessage($validated, $user->id);

            if ($response['status']) {
                return ResponseHelper::success($response['message']);
            } else {

                return ResponseHelper::error($response['message']);
            }
        } catch (\Exception $e) {
            return ResponseHelper::error('Otp not sent');
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * verify otp
     */

    public function verifyOtp(VerifyMobileOtpRequest $request)
    {
        $validated = $request->validated();
        $mobile_number = $validated['mobile_number'];
        $otp_code = $validated['otp'];

        //check mobile number is already exist
        $user = $this->userService->getUserByMobileNumber($mobile_number);

        if (!$user) {
            return ['status' => false, 'message' => 'Mobile number is not registered.'];
        }
        $userType = $user->roles()->first()->name;

        //check user role patient
        if (!$userType == AppConstants::PATIENT) {
            return ['status' => false, 'message' => 'User is not a registered patient.'];
        }
        $otp = Otp::where('user_id', $user->id)->where('otp_code', $otp_code)->first();
        if (!$otp) {
            return ['status' => false, 'message' => 'Invalid OTP.'];
        }
        if ($otp->is_verified == AppConstants::ACTIVE) {
            return ['status' => false, 'message' => 'OTP is already verified.'];
        }
        if ($otp->expiration_time < date('Y-m-d H:i:s')) {
            return ['status' => false, 'message' => 'OTP has been expired.'];
        }



        $user_token = $user->createToken('appToken')->accessToken;

        //update otp status
        $otp->is_verified = AppConstants::ACTIVE;
        $otp->save();

        return ResponseHelper::success(trans('messages.login_success'), [
            'token' => $user_token,
            'user_role' => $userType,
            'user' => $user
        ]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * getOtp
     */

    public function getOtp($id)
    {
        $user_id = User::where('mobile_number', $id)->first()->id;

        $otp = Otp::where('user_id', $user_id)->where('is_verified', 0)->latest()->first();
        if (!$otp) {
            return ['status' => false, 'message' => 'Otp not found.'];
        }
        return ['status' => true, 'OPT' => $otp->otp_code, 'message' => 'Otp found.'];
    }
}
