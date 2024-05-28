<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\AddRatingRequest;
use App\Models\Clinic;
use App\Services\ClinicService;
use App\Services\RatingService;
use Illuminate\Http\Request;

class RatingsController extends Controller
{
    protected $ratingService;
    protected $clinicService;

    public function __construct(RatingService $ratingService, ClinicService $clinicService)
    {
        $this->ratingService = $ratingService;
        $this->clinicService = $clinicService;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * Add rating
     */

    public function addRating(AddRatingRequest $request)
    {
        try {
            $validated = $request->validated();

            $response = $this->ratingService->addRating($validated);
            if ($response['status']) {
                return ResponseHelper::success($response['message'], $response['data']);
            } else {
                return ResponseHelper::error($response['message']);
            }
        } catch (\Exception $e) {
            return ResponseHelper::error($response['message']);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * clinicRating
     */

    public function clinicRating($clinic)
    {
        try {
            //check clinic id exists in the table
            $clinic_id = Clinic::find($clinic);
            if (!$clinic_id) {
                return ResponseHelper::error('Clinic not found');
            }

            $response = $this->ratingService->clinicRatings($clinic_id->id);
            if ($response['status']) {
                return ResponseHelper::success($response['message'], $response['data']);
            } else {
                return ResponseHelper::error($response['message']);
            }
        } catch (\Exception $e) {

            return ResponseHelper::error($response['message']);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * doctorRatingsCount
     */

    public function doctorRatingsCount()
    {
        try {
            $clinic_id = $this->clinicService->getLoggedInUserClinic();

            $response = $this->ratingService->doctorRatingsCount($clinic_id);
            if ($response['status']) {
                return ResponseHelper::success($response['message'], $response['data']);
            } else {
                return ResponseHelper::error($response['message']);
            }
        } catch (\Exception $e) {
            return ResponseHelper::error($response['message']);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * doctorRatings
     */

    public function doctorRatings($id)
    {
        try {
            $response = $this->ratingService->doctorRatings($id);
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
