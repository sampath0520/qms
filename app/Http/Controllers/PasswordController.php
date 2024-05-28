<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorLogger;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\EmailVerifyRequest;
use App\Http\Requests\OtpVerifyRequest;
use App\Http\Requests\PasswordVerificationRequest;
use App\Services\PasswordRestTokenService;
use Illuminate\Support\Facades\Mail;
use App\Mail\OTPEmail;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PasswordController extends Controller
{
    protected $passwordRestTokenService;
    protected $userService;


    public function __construct(PasswordRestTokenService $passwordRestTokenService, UserService $userService)
    {
        $this->passwordRestTokenService = $passwordRestTokenService;
        $this->userService = $userService;
    }

    /**
     * Send reset password link to the user's email
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */

    public function sendResetLink(EmailVerifyRequest $request)
    {
        $validated = $request->validated();

        $email = $validated['email'];
        $otp = rand(100000, 999999); // Generate a random 6-digit OTP

        $tokenRequest = [
            'email' => $email,
            'token' => $otp,
        ];

        $tokenDetails = $this->passwordRestTokenService->savePasswordResetToken($tokenRequest);

        if (!$tokenDetails) {
            return ResponseHelper::error(trans('messages.otp_generation_failed'));
        }

        // Send the OTP email
        $email = Mail::to($email)->send(new OTPEmail($otp));

        return ResponseHelper::success(trans('messages.otp_sent'));
    }

    /**
     * Verify the OTP sent to the user's email
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */

    public function verifyOtp(OtpVerifyRequest $request)
    {
        $validated = $request->validated();

        $otp = $validated['otp']; // OTP from the request
        $email = $validated['email']; // Email from the request

        $passwordResetToken = $this->passwordRestTokenService->getPasswordResetToken($otp, $email);


        if ($passwordResetToken) {
            // Check if the OTP is within the valid time frame (1 hour)
            //current time

            $expirationTime = Carbon::parse($passwordResetToken->created_at)->addMinutes(60);

            if (Carbon::now()->lte($expirationTime)) {
                // OTP is valid
                return ResponseHelper::success(trans('messages.otp_verified'), ['email' => $passwordResetToken->email, 'otp' => $passwordResetToken->token]);
            } else {
                // OTP is expired
                return ResponseHelper::error(trans('messages.otp_expired'));
            }
        } else {
            // Invalid OTP
            return ResponseHelper::error(trans('messages.otp_invalid'));
        }
    }


    /**
     * Reset the user's password
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */

    public function resetPassword(PasswordVerificationRequest $request)
    {

        $validated = $request->validated();

        try {
            $password = $validated['password'];
            $email = $validated['email'];

            DB::beginTransaction();
            $this->passwordRestTokenService->resetPassword($email, $password);

            $this->passwordRestTokenService->deletePasswordResetToken($email);
            DB::commit();

            return ResponseHelper::success(trans('messages.password_reset_success'));
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            DB::rollBack();
            // Return an error response to the user
            return ResponseHelper::error(trans('messages.password_reset_failed'));
        }
    }

    /**
     * Change the user's password
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $validated = $request->validated();
        $changePass = $this->passwordRestTokenService->changePassword($validated);
        if ($changePass) {
            return ResponseHelper::success(trans('messages.record_updated'));
        } else {
            return ResponseHelper::error(trans('messages.password_change_failed'));
        }
    }

    /**
     * Verify the OTP sent to the user's email
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */

    public function verifyOtpOnly(OtpVerifyRequest $request)
    {
        $validated = $request->validated();

        $otp = $validated['otp']; // OTP from the request
        $email = $validated['email']; // Email from the request

        $passwordResetToken = $this->passwordRestTokenService->getPasswordResetToken($otp, $email);


        if ($passwordResetToken) {
            // Check if the OTP is within the valid time frame (1 hour)
            //current time

            $expirationTime = Carbon::parse($passwordResetToken->created_at)->addMinutes(60);

            if (Carbon::now()->lte($expirationTime)) {
                // OTP is valid
                return ResponseHelper::success(trans('messages.otp_verified'), ['email' => $passwordResetToken->email, 'otp' => $passwordResetToken->token]);
            } else {
                // OTP is expired
                return ResponseHelper::error(trans('messages.otp_expired'));
            }
        } else {
            // Invalid OTP
            return ResponseHelper::error(trans('messages.otp_invalid'));
        }
    }
}
