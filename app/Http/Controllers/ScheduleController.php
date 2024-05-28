<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\AddScheduleRequest;
use App\Http\Requests\DateWiseScheduleRequest;
use App\Http\Requests\ScheduledDateRequest;
use App\Http\Requests\StatusUpdateRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Models\Schedule;
use App\Services\ClinicService;
use App\Services\ScheduleService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    protected $scheduleService;
    protected $clinicService;

    public function __construct(ScheduleService $scheduleService, ClinicService $clinicService)
    {
        $this->scheduleService = $scheduleService;
        $this->clinicService = $clinicService;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * add schedule
     */

    public function addSchedule(AddScheduleRequest $request)
    {
        try {
            $validated = $request->validated();
            $response = $this->scheduleService->addSchedule($validated);
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
     * get schedule
     */

    public function listSchedule($id)
    {
        try {
            $response = $this->scheduleService->listSchedule($id);
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
     * status Dropdown
     */

    public function statusDropdown()
    {
        try {
            $response = $this->scheduleService->statusDropdown();
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
     * update Status
     */

    public function statusUpdate(StatusUpdateRequest $request)
    {
        $validated = $request->validated();
        try {
            $response = $this->scheduleService->updateStatus($validated);
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
     * update Schedule
     */

    public function updateSchedule(UpdateScheduleRequest $request)
    {
        $validated = $request->validated();
        try {
            $response = $this->scheduleService->updateSchedule($validated);
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
        * scheduled Dates
        */

    public function scheduledDates(ScheduledDateRequest $request)
    {
        try {
            $validated = $request->validated();
            $clinic_id = $this->clinicService->getLoggedInUserClinic();
            $response = $this->scheduleService->scheduledDates($validated, $clinic_id);
            if ($response['status']) {
                return ResponseHelper::success($response['message'], $response['data']);
            } else {
                return ResponseHelper::error($response['message'], $response['data']);
            }
        } catch (\Exception $e) {
            return ResponseHelper::error($response['message']);
        }
    }

    /*
        * @param  \Illuminate\Http\Request  $request
        * @return \Illuminate\Http\Response
        * date Wise Schedule
        */

    public function dateWiseSchedule(DateWiseScheduleRequest $request)
    {
        try {
            $validated = $request->validated();
            $clinic_id = $this->clinicService->getLoggedInUserClinic();
            $response = $this->scheduleService->dateWiseSchedule($validated, $clinic_id);
            if ($response['status']) {
                return ResponseHelper::success($response['message'], $response['data']);
            } else {
                return ResponseHelper::error($response['message'], $response['data']);
            }
        } catch (\Exception $e) {
            return ResponseHelper::error($response['message']);
        }
    }

    /*
        * @param  \Illuminate\Http\Request  $request
        * @return \Illuminate\Http\Response
        * token
        */

    public function token($shedule_id)
    {
        try {
            $schedule = Schedule::where('id', $shedule_id)->first();
            if (!$schedule) {
                return ResponseHelper::error('Schedule not found');
            }
            $clinic_id = $this->clinicService->getLoggedInUserClinic();
            $response = $this->scheduleService->getToken($shedule_id, $clinic_id);
            if ($response['status']) {
                return ResponseHelper::success($response['message'], $response['data']);
            } else {
                return ResponseHelper::error($response['message'], $response['data']);
            }
        } catch (\Exception $e) {
            return ResponseHelper::error($response['message']);
        }
    }

    /*
        * @param  \Illuminate\Http\Request  $request
        * @return \Illuminate\Http\Response
        * details
        */

    public function details($shedule_id)
    {
        try {
            $schedule = $this->scheduleService->getScheduleById($shedule_id);
            if (!$schedule) {
                return ResponseHelper::error('Schedule not found');
            }
            $clinic_id = $this->clinicService->getLoggedInUserClinic();
            $response = $this->scheduleService->details($shedule_id, $clinic_id);
            if ($response['status']) {
                return ResponseHelper::success($response['message'], $response['data']);
            } else {
                return ResponseHelper::error($response['message'], $response['data']);
            }
        } catch (\Exception $e) {
            return ResponseHelper::error($response['message']);
        }
    }
}
