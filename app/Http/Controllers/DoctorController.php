<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorLogger;
use App\Helpers\ResponseHelper;
use App\Http\Requests\AddDoctorRequest;
use App\Http\Requests\AddDoctorsClinicRequest;
use App\Http\Requests\DoctorStatusRequest;
use App\Http\Requests\ScheduledDateRequest;
use App\Http\Requests\updateDoctorRequest;
use App\Services\ClinicService;
use App\Services\DoctorService;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    protected $doctorService;
    protected $clinicService;

    public function __construct(DoctorService $doctorService, ClinicService $clinicService)
    {
        $this->doctorService = $doctorService;
        $this->clinicService = $clinicService;
    }
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * add doctor
     */

    public function addDoctor(AddDoctorRequest $request)
    {
        try {
            $data = $request->validated();
            $clinic_id = $this->clinicService->getLoggedInUserClinic();
            $data['clinic_id'] = $clinic_id;
            $response = $this->doctorService->addDoctor($data);
            if ($response['status']) {

                return ResponseHelper::success($response['message']);
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
     * speciality
     */

    public function speciality()
    {
        try {
            $response = $this->doctorService->speciality();
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
     * add Clinic
     */

    public function addDocClinic(AddDoctorsClinicRequest $request)
    {
        try {
            $data = $request->validated();
            $clinic_id = $this->clinicService->getLoggedInUserClinic();
            $data['clinic_id'] = $clinic_id;
            $response = $this->doctorService->addDocClinic($data);
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
     * listDoctor
     */

    public function listDoctor()
    {
        try {
            $response = $this->doctorService->getDoctorList();
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
     * update Doctor
     */

    public function updateDoctor(updateDoctorRequest $request)
    {
        try {
            $data = $request->validated();
            $response = $this->doctorService->updateDoctor($data);
            if ($response['status']) {
                return ResponseHelper::success($response['message']);
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
     * delete Doctor
     */

    public function deleteDoctor($id)
    {
        try {
            $clinic_id = $this->clinicService->getLoggedInUserClinic();
            $response = $this->doctorService->deleteDoctor($id, $clinic_id);
            if ($response['status']) {
                return ResponseHelper::success($response['message']);
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
     * activateDeactivateDoctor
     */

    public function activateDeactivateDoctor(DoctorStatusRequest $request)
    {
        $validated = $request->validated();
        try {
            $response = $this->doctorService->activateDeactivateDoctor($validated);
            if ($response['status']) {
                return ResponseHelper::success($response['message']);
            } else {
                return ResponseHelper::error($response['message']);
            }
        } catch (\Exception $e) {
            return ResponseHelper::error($response['message']);
        }
    }

    /*
        * @param  \Illuminate\Http\Request  $request
        * @return \Illuminate\Http\Response
        * dropdownDoctor
        */

    public function dropdownDoctor()
    {
        try {
            $response = $this->doctorService->dropdownDoctor();
            if ($response['status']) {
                return ResponseHelper::success($response['message'], $response['data']);
            } else {
                return ResponseHelper::error($response['message']);
            }
        } catch (\Exception $e) {
            return ResponseHelper::error($response['message']);
        }
    }

    /*
        * @param  \Illuminate\Http\Request  $request
        * @return \Illuminate\Http\Response
        * searchDoctor
        */

    public function searchDoctor(Request $request)
    {
        try {

            $response = $this->doctorService->searchDoctor($request);
            if ($response['status']) {
                return ResponseHelper::success($response['message'], $response['data']);
            } else {
                return ResponseHelper::error($response['message']);
            }
        } catch (\Exception $e) {
            return ResponseHelper::error($response['message']);
        }
    }

    /*
        * @param  \Illuminate\Http\Request  $request
        * @return \Illuminate\Http\Response
        * speciality Search
        */

    public function specialitySearch(Request $request)
    {
        try {
            $clinic_id = $this->clinicService->getLoggedInUserClinic();
            $response = $this->doctorService->specialitySearch($request, $clinic_id);
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
