<?php

namespace App\Services;

use App\Constants\AppConstants;
use App\Helpers\ErrorLogger;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorsClinic;
use App\Models\Patient;
use App\Models\PatientBooking;
use App\Models\Schedule;
use App\Models\ScheduleStatus;
use App\Models\SpecialityArea;
use App\Models\User;
use App\Notifications\SystemNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScheduleService
{

    private $clinicService;
    private $patientService;

    public function __construct(PatientService $patientService, ClinicService $clinicService)
    {
        $this->clinicService = $clinicService;
        $this->patientService = $patientService;
    }


    /**
     * Add schedule
     *
     * @return \Illuminate\Http\Response
     */
    public function addSchedule($data)
    {
        try {

            $doctorId = $data['doctor_id'];
            $date = $data['date'];
            $startTime = $data['start_time'];
            $endTime = $data['end_time'];
            $maximumSlots = $data['maximum_slots'];
            $avgTimePerPerson = $data['avg_time_per_person'];

            $clinic_id = $this->clinicService->getLoggedInUserClinic();

            $availability = $this->hasOverlappingSchedules($doctorId, $date, $startTime, $endTime);

            if ($availability) {
                return ['status' => false, 'message' => 'Schedule already exists.'];
            } else {
                DB::beginTransaction();
                $schedule = new Schedule();
                $schedule->doctor_id = $doctorId;
                $schedule->clinic_id = $clinic_id;
                $schedule->date = $date;
                $schedule->start_time = $startTime;
                $schedule->end_time = $endTime;
                $schedule->slots = $maximumSlots;
                $schedule->avg_time = $avgTimePerPerson;
                $schedule->save();

                //insert to schedule_status table
                $this->addScheduleStatus($schedule->id, AppConstants::DS_PENDING);

                DB::commit();
            }

            return ['status' => true, 'message' => 'Schedule added successfully.', 'data' => $schedule];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            DB::rollBack();
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }


    // Method to check for overlapping schedules
    public function hasOverlappingSchedules($doctorId, $date, $startTime, $endTime, $scheduleId = null)
    {
        $query = Schedule::where('doctor_id', $doctorId)
            ->where('date', $date)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime]);
            });

        if ($scheduleId) {
            // Exclude the current schedule being edited/checked for overlaps
            $query->where('id', '<>', $scheduleId);
        }

        return $query->exists();
    }

    /**
     * Add schedule status
     *
     * @return \Illuminate\Http\Response
     */
    public function addScheduleStatus($shedule_id, $status)
    {
        try {
            $scheduleStatus = new ScheduleStatus();
            $scheduleStatus->schedule_id = $shedule_id;
            $scheduleStatus->status = $status;
            $scheduleStatus->save();
            return ['status' => true, 'message' => 'Schedule status added successfully.'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /**
     * List schedule
     *
     * @return \Illuminate\Http\Response
     */

    public function listSchedule($doctorId)
    {
        try {

            $clinic_id = $this->clinicService->getLoggedInUserClinic();
            // $schedule = Schedule::where('clinic_id', $clinic_id)->get();

            $schedule = Schedule::with('doctor')->where('clinic_id', $clinic_id)
                ->where('doctor_id', $doctorId)
                ->get();

            //get booking count for each schedule
            foreach ($schedule as $value) {
                $value->booking_count = PatientBooking::where('clinic_id', $clinic_id)
                    ->where('current_status', '<>', AppConstants::PATIENT_CANCELLED)
                    ->where('schedule_id', $value->id)->count();
            }

            foreach ($schedule as $value) {
                $value->current_status_name = $this->patientService->getDoctorScheduleStatus($value->current_status);
            }

            return ['status' => true, 'data' => $schedule, 'message' => 'Schedule list.'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /**
     * Status dropdown
     *
     * @return \Illuminate\Http\Response
     */

    public function statusDropdown()
    {
        //get all the status from AppConstants starting with DS_
        $status = AppConstants::getConstantsStartingWith('DS_');

        $modifiedStatusConstants = [];
        foreach ($status as $key => $value) {
            $modifiedStatusConstants[] = [
                'id' => $value,
                'status' => substr($key, 3),
            ];
        }
        return ['status' => true, 'data' => $modifiedStatusConstants, 'message' => 'Status list.'];
    }

    /**
     * Update status
     *
     * @return \Illuminate\Http\Response
     */

    public function updateStatus($data)
    {
        try {

            DB::beginTransaction();
            $clinic_id = $this->clinicService->getLoggedInUserClinic();

            //get clinic name from clinic table
            // $clinic = Clinic::where('id', $clinic_id)->first();
            $schedule = Schedule::where('id', $data['schedule_id'])->where('clinic_id', $clinic_id)->first();
            if (!$schedule) {
                return ['status' => false, 'message' => 'Schedule not exists.'];
            }
            $schedule->current_status = $data['status'];
            $schedule->save();

            $res = $this->addScheduleStatus($data['schedule_id'], $data['status']);

            //get users for this schedule from patient table
            //where current status is not cancelled check in patient booking table
            $bookings = Patient::with('patient_bookings')
                ->whereHas('patient_bookings', function ($query) use ($data, $clinic_id) {
                    $query->where('schedule_id', $data['schedule_id'])
                        ->where('clinic_id', $clinic_id)
                        ->where('current_status', '<>', AppConstants::PATIENT_CANCELLED);
                })
                ->get();

            //send notification to users
            foreach ($bookings as $book) {
                //add switch case for each status
                $details = ['name' => $book->user->first_name, 'doctor_name' => $schedule->doctor->first_name . ' ' . $schedule->doctor->last_name];
                switch ($data['status']) {
                    case AppConstants::DS_ARRIVED:
                        //doctor_arrival
                        $message = trans('messages.doctor_arrival', $details);
                        break;
                    case AppConstants::DS_WAITING:
                        $message = trans('messages.doctor_waiting', $details);
                        break;
                    case AppConstants::DS_STARTED:
                        $message = trans('messages.doctor_started', $details);
                        break;
                    case AppConstants::DS_DELAYED:
                        $message = trans('messages.doctor_delayed', $details);
                        break;
                    case AppConstants::DS_CANCELLED:
                        $message = trans('messages.doctor_cancelled', $details);
                        break;
                    case AppConstants::DS_COMPLETED:
                        $message = trans('messages.doctor_completed', $details);
                        break;
                    default:
                        $message = trans('messages.doctor_arrival', $details);
                }

                $user = User::where('id', $book->user_id)->first();

                $clinic = Clinic::where('id', $clinic_id)->first();
                $user->notify(new SystemNotification($user->user_id, $message, $clinic->name));
            }
            DB::commit();
            return ['status' => true, 'message' => 'Schedule status updated successfully.', 'data' => $schedule];
        } catch (\Exception $e) {
            DB::rollBack();
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /**
     * Update schedule
     *
     * @return \Illuminate\Http\Response
     */

    public function updateSchedule($data)
    {
        try {

            DB::beginTransaction();

            $clinic_id = $this->clinicService->getLoggedInUserClinic();

            $schedule = Schedule::where('id', $data['schedule_id'])->where('clinic_id', $clinic_id)->first();

            if (!$schedule) {
                return ['status' => false, 'message' => 'Schedule not exists.'];
            }

            $availability = $this->hasOverlappingSchedules($data['doctor_id'], $data['date'], $data['start_time'], $data['end_time'], $data['schedule_id']);
            if ($availability) {
                return ['status' => false, 'message' => 'Schedule already exists.'];
            }

            $schedule->doctor_id = $data['doctor_id'];
            $schedule->date = $data['date'];
            $schedule->start_time = $data['start_time'];
            $schedule->end_time = $data['end_time'];
            $schedule->slots = $data['maximum_slots'];
            $schedule->avg_time = $data['avg_time_per_person'];
            $schedule->save();

            DB::commit();
            return ['status' => true, 'message' => 'Schedule updated successfully.', 'data' => $schedule];
        } catch (\Exception $e) {
            DB::rollBack();
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /*
        * scheduled Dates
        *
        * @return \Illuminate\Http\Response
        */

    public function scheduledDates($data, $clinic_id)
    {
        $doctor_id = $data['doctor_id'];
        try {
            $schedule = Schedule::where('doctor_id', $doctor_id)
                ->where('clinic_id', $clinic_id)
                ->where('date', '>=', date('Y-m-d'))->get();

            return ['status' => true, 'data' => $schedule, 'message' => 'Schedule list.'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /*
        * date Wise Schedule
        *
        * @return \Illuminate\Http\Response
        */

    public function dateWiseSchedule($data, $clinic_id)
    {

        $doctor_id = $data['doctor_id'];
        $date = $data['date'];
        try {
            $schedule = Schedule::where('doctor_id', $doctor_id)
                ->where('clinic_id', $clinic_id)
                ->where('date', $date)->get();

            return ['status' => true, 'data' => $schedule, 'message' => 'Schedule list.'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /*
        * @param  \Illuminate\Http\Request  $request
        * @return \Illuminate\Http\Response
        * get Token
        */

    public function getToken($schedule_id, $clinic_id)
    {
        try {

            $schedule = Schedule::where('id', $schedule_id)->first();

            if (!$schedule) {
                return ['status' => false, 'message' => 'Schedule not exists.'];
            }

            //check token for patients_bookings table
            // $token = PatientBooking::where('schedule_id', $schedule_id)
            //     ->where('clinic_id', $clinic_id)
            //     ->first();

            $tokens = PatientBooking::where('schedule_id', $schedule_id)
                ->where('clinic_id', $clinic_id)
                ->where('current_status', '<>', AppConstants::PATIENT_CANCELLED)
                ->count();

            if ($tokens > 0) {
                $token = $tokens + 1;
            } else {
                $token = 1;
            }

            if ($token >= $schedule->slots) {
                return ['status' => false, 'message' => 'Token limit reached.'];
            }

            return ['status' => true, 'data' => ['token' => $token], 'message' => 'Token generated successfully.'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * get schedule by id
     */

    public function getScheduleById($schedule_id)
    {
        try {
            $schedule = Schedule::where('id', $schedule_id)->first();

            if (!$schedule) {
                return false;
            }
            return $schedule;
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /*
        * @param  \Illuminate\Http\Request  $request
        * @return \Illuminate\Http\Response
        * details
        */

    public function details($schedule_id, $clinic_id)
    {
        try {
            //get schedule details with current status, doctor details
            $schedule = Schedule::with('doctor')->where('id', $schedule_id)->first();

            $schedule->current_status_name = $this->patientService->getDoctorScheduleStatus($schedule->current_status);

            //get patient bookings
            $patientBookings = PatientBooking::with('patient')->where('schedule_id', $schedule_id)
                ->whereIn('current_status', [
                    AppConstants::PATIENT_WAITING,
                    AppConstants::PATIENT_STARTED,
                    AppConstants::PATIENT_COMPLETED,
                    AppConstants::PATIENT_ARRIVED,
                ])
                ->where('clinic_id', $clinic_id)
                ->orderBy('token', 'asc')
                ->get();


            foreach ($patientBookings as $value) {
                if ($value->current_status == AppConstants::PATIENT_STARTED) {
                    $schedule->active_token = $value->token;
                }
                $value->current_status_name = $this->patientService->getPatientStatus($value->current_status);
            }

            return ['status' => true, 'data' => ['schedule' => $schedule, 'patient_bookings' => $patientBookings], 'message' => 'Schedule details.'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    //getTotalSchedules
    public function getTotalSchedules($clinic_id)
    {
        try {
            //get total schedules for the day
            $totalSchedules = Schedule::where('clinic_id', $clinic_id)
                ->where('date', Carbon::now()->toDateString())
                ->count();

            return $totalSchedules;
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return 0;
        }
    }
}
