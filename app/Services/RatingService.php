<?php

namespace App\Services;

use App\Constants\AppConstants;
use App\Helpers\ErrorLogger;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorsClinic;
use App\Models\Rating;
use App\Models\User;
use App\Models\UserClinic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpParser\Comment\Doc;

class RatingService
{

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * Add rating
     */

    public function addRating($data)
    {
        //if $data $data['type'] == 1 then check clinic id exists in the table
        if ($data['type'] == 1) {
            $clinic_id = Clinic::find($data['relevent_id']);
            if (!$clinic_id) {
                return ['status' => false, 'message' => 'Clinic not found'];
            }
        }

        //if $data $data['type'] == 2 then check doctor id exists in the table
        if ($data['type'] == 2) {
            $doctor_id = Doctor::find($data['relevent_id']);
            if (!$doctor_id) {
                return ['status' => false, 'message' => 'Doctor not found'];
            }
        }

        try {
            // $rating = Rating::create([
            //     'user_id' => $data['user_id'],
            //     'rating' => $data['rating'],
            //     'type' => $data['type'],
            //     'description' => $data['description'],
            //     'relevent_id' => $data['relevent_id'],
            // ]);

            //create or update rating
            $rating = Rating::updateOrCreate(
                [
                    'user_id' => $data['user_id'],
                    'type' => $data['type'],
                    'relevent_id' => $data['relevent_id'],
                ],
                [
                    'rating' => $data['rating'],
                    'description' => $data['description'],
                ]
            );

            return ['status' => true, 'message' => 'Rating added successfully', 'data' => $rating];
        } catch (\Exception $e) {
            //check duplicate entry
            if ($e->getCode() == 23000) {
                return ['status' => false, 'message' => 'You have already rated'];
            }
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * clinicRatings
     */

    public function clinicRatings($clinic_id)
    {

        try {
            $clinicRatings = Rating::with('clinic', 'user')->where('type', 1)->where('relevent_id', $clinic_id)->orderBy('id', 'desc')->get();
            $averageRating = Rating::where('type', 1)->where('relevent_id', $clinic_id)->avg('rating');
            $averageRating = number_format((float)$averageRating, 1, '.', '');
            $data = [
                'averageRating' => $averageRating,
                'clinicRatings' => $clinicRatings
            ];
            return ['status' => true, 'message' => 'Clinic ratings', 'data' => $data];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * doctorRatingsCount
     */

    public function doctorRatingsCount($clinic_id)
    {
        //get doctor id's from doctors_clinics table
        $doctor_ids = DoctorsClinic::where('clinic_id', $clinic_id)->pluck('doctor_id')->toArray();


        try {

            //get doctors from ratings table where in doctor id's
            $doctorRatingsCount = Rating::with('doctor')->select('relevent_id', DB::raw('count(*) as total'))
                ->where('type', 2)
                ->whereIn('relevent_id', $doctor_ids)
                ->groupBy('relevent_id')
                ->get();

            return ['status' => true, 'message' => 'Doctor ratings count', 'data' => $doctorRatingsCount];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * doctorRatings
     */

    public function doctorRatings($doctor_id)
    {
        try {
            $doctorRatings = Rating::with('doctor', 'user')->where('type', 2)->where('relevent_id', $doctor_id)->orderBy('id', 'desc')->get();
            $averageRating = Rating::where('type', 2)->where('relevent_id', $doctor_id)->avg('rating');
            $averageRating = number_format((float)$averageRating, 1, '.', '');

            //create array for average rating
            $data = [
                'averageRating' => $averageRating,
                'doctorRatings' => $doctorRatings
            ];

            return ['status' => true, 'message' => 'Doctor ratings', 'data' => $data];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }
}
