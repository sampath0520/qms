<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Helpers\ResponseHelper;
use App\Http\Requests\AddPatientRequest;
use App\Http\Requests\PatientStatusUpdateRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Notifications\SystemNotification;
use App\Services\ClinicService;
use App\Services\PatientService;
use App\Services\ScheduleService;
use App\Services\UserService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    protected $patientService;
    protected $userService;
    protected $clinicService;
    protected $scheduleService;

    public function __construct(PatientService $patientService, UserService $userService, ClinicService $clinicService, ScheduleService $scheduleService)
    {
        $this->patientService = $patientService;
        $this->userService = $userService;
        $this->clinicService = $clinicService;
        $this->scheduleService = $scheduleService;
    }



    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * search patient
     */

    public function searchPatient(Request $request)
    {
        try {
            $response = $this->patientService->searchPatient($request->all());
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
     * Add Patient
     */

    public function addPatient(AddPatientRequest $request)
    {
        $validated = $request->validated();

        $clinic_id = $this->clinicService->getLoggedInUserClinic();
        $validated['auth_type'] = AppConstants::OTP;
        $validated['role'] = AppConstants::PATIENT;
        $validated['clinic_id'] = $clinic_id;
        $schedule = $this->scheduleService->getScheduleById($validated['schedule_id']);

        $response = $this->patientService->addPatient($validated, $schedule);

        if ($response['status']) {
            return ResponseHelper::success($response['message'], $response['data']);
        } else {
            return ResponseHelper::error($response['message']);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * Booked List
     */

    public function bookedList()
    {
        try {
            $clinic_id = $this->clinicService->getLoggedInUserClinic();
            $response = $this->patientService->bookedList($clinic_id);
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
     * status Update
     */

    public function statusUpdate(PatientStatusUpdateRequest $request)
    {
        try {
            $validated = $request->validated();
            $clinic_id = $this->clinicService->getLoggedInUserClinic();
            $validated['clinic_id'] = $clinic_id;
            $response = $this->patientService->statusUpdate($validated);
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
     * list Patient
     */

    public function listPatient()
    {
        try {
            // $clinic_id = $this->clinicService->getLoggedInUserClinic();
            $response = $this->patientService->listPatient();
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
     * update Patient
     */

    public function updatePatient(UpdatePatientRequest $request)
    {
        $validated = $request->validated();
        $response = $this->patientService->updatePatient($request);
        if ($response['status']) {
            return ResponseHelper::success($response['message'], $response['data']);
        } else {
            return ResponseHelper::error($response['message']);
        }
    }
}
