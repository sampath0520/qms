<?php


namespace App\Services;

use App\Helpers\ErrorLogger;
use App\Models\Otp;
use App\Models\SmsLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Validator;


class SmsService
{


    protected $client;

    public function __construct()
    {
        $this->client = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
    }
    // /**
    //  * Send SMS
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public static function sendMessagea($validated)
    // {
    //     $message = $validated['message'];
    //     $recipients = $validated['recipients'];
    //     $account_sid = getenv("TWILIO_SID");
    //     $auth_token = getenv("TWILIO_AUTH_TOKEN");
    //     $twilio_number = getenv("TWILIO_NUMBER");
    //     $twilio_from = getenv("TWILIO_SMS_FROM");
    // }


    public function sendMessage($data, $user_id = null)
    {
        $message = $data['message'];
        $to = $data['recipients'];

        try {
            DB::beginTransaction();
            //insert to otp table
            $otp_data = [
                'user_id' => $user_id,
                'otp_code' => $message,
                'expiration_time' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
            ];

            $otp = Otp::create($otp_data);
            DB::commit();
            return ['status' => true, 'message' => 'OTP sent successfull.'];
        } catch (TwilioException $e) {
            ErrorLogger::logError($e);
            DB::rollBack();
            $smsLog = new SmsLog();
            $smsLog->message = $message;
            $smsLog->recipients = $to;
            $smsLog->status = $msgSent->status;
            $smsLog->type = 'otp';
            $smsLog->content = $e->getMessage();
            $smsLog->save();
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
