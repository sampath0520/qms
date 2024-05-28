<?php

namespace App\Services;

use App\Constants\AppConstants;
use App\Helpers\ErrorLogger;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorsClinic;
use App\Models\Patient;
use App\Models\PatientAdditionalInfo;
use App\Models\PatientBooking;
use App\Models\Rating;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\SpecialityArea;
use App\Models\User;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Notifications\SystemNotification;

class PatientService
{

    protected $userService;
    // protected $scheduleService;

    public function __construct(UserService $userService)
    {
        // $this->scheduleService = $scheduleServiceF;
        $this->userService = $userService;
    }


    // protected $userService;
    // protected $scheduleService;

    // public function setUserService(UserService $userService)
    // {
    //     $this->userService = $userService;
    // }

    // public function setScheduleService(ScheduleService $scheduleService)
    // {
    //     $this->scheduleService = $scheduleService;
    // }


    /**
     * Search patient
     *
     * @return \Illuminate\Http\Response
     */
    public function searchPatient($data)
    {
        try {
            $search = $data['search'];

            $patients = User::role(AppConstants::PATIENT)->Where('mobile_number', $search)
                ->with('patient')
                //where patient.appoinment_for = 1
                ->whereHas('patient', function ($query) {
                    $query->where('appoinment_for', AppConstants::MY_SELF);
                })
                ->first();

            return ['status' => true, 'message' => 'Patients fetched successfully.', 'data' => $patients];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong. Please try again later.'];
        }
    }

    /**
     * Add patient
     *
     * @return \Illuminate\Http\Response
     */

    public function addPatient($data, $schedule)
    {

        try {
            DB::beginTransaction();

            //check token for patients_bookings table
            $token_count = PatientBooking::where('schedule_id', $data['schedule_id'])
                ->where('clinic_id', $data['clinic_id'])
                ->where('current_status', '<>', AppConstants::PATIENT_CANCELLED)
                ->count();


            //check schedule token count exceeds the slots in schedule table
            if ($token_count >= $schedule['slots']) {
                return ['status' => false, 'message' => 'Token count exceeds for this schedule.'];
            }

            $user = User::where('mobile_number', $data['phone_number'])
                // ->orWhere('email', $data['email'])
                ->first();


            if ($user) {

                $check_name = Patient::where('name', $data['first_name'] . ' ' . $data['last_name'])
                    ->where('schedule_id', $data['schedule_id'])
                    ->where('mobile_number', $data['phone_number'])
                    ->first();

                if ($check_name) {
                    return ['status' => false, 'message' => 'Patient already exists for this schedule.'];
                }

                //check $data['phone_number'] exists in Patient table and appoinment_for = MY_AppConstants
                $patient = Patient::where('mobile_number', $data['phone_number'])
                    ->where('appoinment_for', AppConstants::MY_SELF)
                    ->first();

                if (!$patient) {

                    //update user
                    $user->first_name = $data['first_name'];
                    $user->last_name = $data['last_name'];
                    $user->mobile_number = $data['phone_number'];
                    $user->email = $data['email'] ?? null;
                    $user->save();
                }
            } else {
                $password = $this->userService->generatePassword();
                $user = User::create([
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'] ?? null,
                    'last_name' => $data['last_name'],
                    'email' => $data['email'] ?? null,
                    'mobile_number' => $data['phone_number'],
                    'password' => bcrypt($password),
                    'is_active' => AppConstants::ACTIVE,
                    'auth_type' => $data['auth_type'],
                ]);
                // dd($user);
                //assign role to user
                $role = Role::where('name', $data['role'])->first();
                $user->roles()->attach($role);
            }


            $patient = new Patient();
            $patient->name = $data['first_name'] . ' ' . $data['last_name'];
            $patient->email = $data['email'];
            $patient->mobile_number = $data['phone_number'];
            $patient->birth_date = $data['dob'];
            $patient->appoinment_for = $data['appoinment_for'];
            $patient->user_id = $user->id;
            $patient->schedule_id = $data['schedule_id'];

            $patient->save();
            // dd($patient);

            // //check token for patients_bookings table
            // $token_count = PatientBooking::where('schedule_id', $data['schedule_id'])
            //     ->where('clinic_id', $data['clinic_id'])
            //     ->where('current_status', '<>', AppConstants::PATIENT_CANCELLED)
            //     ->count();

            // $schedule = $this->scheduleService->getScheduleById($data['schedule_id']);
            $slots = $schedule['slots'];
            $appoinment_date = $schedule['date'];

            // Assuming $schedule['start_time'] and $schedule['end_time'] are time strings in 'H:i:s' format
            $startTime = Carbon::parse($schedule['start_time']);
            $endTime = Carbon::parse($schedule['end_time']);

            // Calculate the time difference in minutes
            $timeDiffMinutes = $startTime->diffInMinutes($endTime);

            // Assuming $slots and $PatientBooking->token are available
            $timePerSlot = $timeDiffMinutes / $slots;

            if ($token_count > 0) {

                $MaxToken = PatientBooking::where('schedule_id', $data['schedule_id'])
                    ->where('clinic_id', $data['clinic_id'])
                    ->orderBy('token', 'desc')
                    ->first()->token;
                // If a token exists, calculate the start and end time based on the token number
                $token = $MaxToken + 1;
                $tokenStart = $startTime->copy()->addMinutes($timePerSlot * $token_count);
                $tokenEnd = $tokenStart->copy()->addMinutes($timePerSlot);
            } else {
                // If no token exists, set token to 1 and calculate start and end time accordingly
                $token = 1;
                $tokenStart = $startTime->copy();
                $tokenEnd = $tokenStart->copy()->addMinutes($timePerSlot);
            }

            // dd($tokenStart, $tokenEnd);

            //add to patient_booking table
            $patientBooking =  $patient->patient_bookings()->create([
                'clinic_id' => $data['clinic_id'],
                // 'doctor_id' => $data['doctor_id'],
                'schedule_id' => $data['schedule_id'],
                'token' => $token,
                'symptoms' =>  $data['symptoms'],
                'current_status' => AppConstants::PATIENT_NOT_ARRIVED,
                'is_active' => AppConstants::ACTIVE,

                'schedule_date' => $appoinment_date,
                'start_time' => $tokenStart,
                'end_time' => $tokenEnd,

            ]);

            $patientBooking->patient_statuses()->create([
                'booking_id' => $patientBooking->id,
                'schedule_id' => $data['schedule_id'],
                'status' => AppConstants::PATIENT_NOT_ARRIVED,
            ]);


            $message = trans('messages.booking_added', [
                'name' => $patient->name,
                'booking_id' => $patientBooking->token,
                'booking_date' => $patientBooking->schedule_date,
                'booking_time' => $patientBooking->start_time,
                'doctor_name' => $patientBooking->schedule->doctor->first_name . ' ' . $patientBooking->schedule->doctor->last_name,
                'clinic_name' => $patientBooking->clinic->name,
            ]);


            //send notification to patient
            $user->notify(new SystemNotification($patient, $message, $patientBooking->clinic->name));

            DB::commit();
            return ['status' => true, 'message' => 'Patient added successfully.', 'data' => $user];
        } catch (\Exception $e) {
            //duplicate entry
            if ($e->getCode() == 23000) {
                return ['status' => false, 'message' => 'User already exists.'];
            }
            ErrorLogger::logError($e);
            DB::rollBack();
            return ['status' => false, 'message' => 'Something went wrong. Please try again later.'];
        }
    }

    /**
     * Booked List
     *
     * @return \Illuminate\Http\Response
     */

    public function bookedList($clinic_id)
    {
        try {

            $booked_list = PatientBooking::with('schedule.doctor', 'patient', 'patient_statuses')
                ->where('clinic_id', $clinic_id)
                ->where('schedule_date', '>=', Carbon::now()->toDateString()) // Use 'toDateString()' to get the date in 'Y-m-d' format
                ->where('current_status', '<>', AppConstants::PATIENT_CANCELLED)
                ->whereHas('schedule.doctor', function ($query) {
                    $query->select('id')
                        ->from('doctors')
                        ->whereColumn('doctors.id', '=', 'schedules.doctor_id');
                })
                ->orderBy('schedule_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();

            //add swith to get patient current status
            foreach ($booked_list as $key => $value) {
                //get age from patient.birth_date
                $booked_list[$key]['patient_age'] = Carbon::parse($value['patient']['birth_date'])->age;
                $booked_list[$key]['current_status_name'] = $this->getPatientStatus($value['current_status']);
            }

            return ['status' => true, 'message' => 'Booked list fetched successfully.', 'data' => $booked_list];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong. Please try again later.'];
        }
    }

    //switch case for patient status
    public static function getPatientStatus($status)
    {
        switch ($status) {
            case AppConstants::PATIENT_NOT_ARRIVED:
                return 'Not Arrived';
                break;
            case AppConstants::PATIENT_ARRIVED:
                return 'Arrived';
                break;
            case AppConstants::PATIENT_DELAYED:
                return 'Delayed';
                break;
            case AppConstants::PATIENT_WAITING:
                return 'Waiting';
                break;
            case AppConstants::PATIENT_CANCELLED:
                return 'Cancelled';
                break;
            case AppConstants::PATIENT_STARTED:
                return 'Started';
                break;
            case AppConstants::PATIENT_COMPLETED:
                return 'Completed';
                break;
            default:
                return 'Not Arrived';
        }
    }

    //get doctor status
    public static function getDoctorScheduleStatus($status)
    {

        switch ($status) {
            case AppConstants::DS_PENDING:
                return 'Pending';
                break;
            case AppConstants::DS_ARRIVED:
                return 'Arrived';
                break;
            case AppConstants::DS_WAITING:
                return 'Waiting';
                break;
            case AppConstants::DS_STARTED:
                return 'Started';
                break;
            case AppConstants::DS_COMPLETED:
                return 'Completed';
                break;
            case AppConstants::DS_DELAYED:
                return 'Delayed';
                break;
            case AppConstants::DS_CANCELLED:
                return 'Cancelled';
                break;
            default:
                return 'Pending';
        }
    }

    /**
     * Update Patient Status
     *
     * @return \Illuminate\Http\Response
     */

    public function statusUpdate($data)
    {

        try {
            DB::beginTransaction();

            //close all patient status = PATIENT_STARTED
            if ($data['status'] == AppConstants::PATIENT_STARTED) {
                //get schedule_id from patient_booking

                $patientBook = PatientBooking::where('id', $data['patient_booking_id'])->first();

                //update all the patient status = PATIENT_STARTED to PATIENT_COMPLETED
                //add each record to patient_status table also
                $patient_booking = PatientBooking::where('schedule_id', $patientBook->schedule_id)
                    ->where('current_status', AppConstants::PATIENT_STARTED)
                    ->get();

                foreach ($patient_booking as $key => $value) {
                    $value->current_status = AppConstants::PATIENT_COMPLETED;
                    $value->save();

                    $value->patient_statuses()->create([
                        'booking_id' => $value->id,
                        'schedule_id' => $value->schedule_id,
                        'status' => AppConstants::PATIENT_COMPLETED,
                    ]);
                }
            }

            $patient_booking = PatientBooking::where('id', $data['patient_booking_id'])->first();
            $patient_booking->current_status = $data['status'];
            //if status = PATIENT_STARTED start_time will be updated
            if ($data['status'] == AppConstants::PATIENT_STARTED) {
                $patient_booking->start_time = Carbon::now();
            }
            $patient_booking->save();

            $patient_booking->patient_statuses()->create([
                'booking_id' => $patient_booking->id,
                'schedule_id' => $patient_booking->schedule_id,
                'status' => $data['status'],
                'delay_reason' => $data['delay_reason'] ?? '',
            ]);

            //get schedule
            $schedule = Schedule::where('id', $patient_booking->schedule_id)->first();

            $patient = Patient::where('id', $patient_booking->patient_id)->first();
            $user = User::where('id', $patient->user_id)->first();

            // switch case for patient status
            switch ($data['status']) {
                case AppConstants::PATIENT_ARRIVED:
                    $status = 'Arrived';
                    break;
                case AppConstants::PATIENT_DELAYED:
                    $status = 'Delayed';
                    break;
                case AppConstants::PATIENT_WAITING:
                    $status = 'Waiting';
                    break;
                case AppConstants::PATIENT_STARTED:
                    $status = 'Started';
                    break;
                case AppConstants::PATIENT_CANCELLED:
                    $status = 'Cancelled';
                    break;
                case AppConstants::PATIENT_COMPLETED:
                    $status = 'Completed';
                    break;
                default:
                    $status = 'Not Arrived';
            }

            $details = [
                'name' => $patient->name,
                'doctor_name' => $schedule->doctor->first_name . ' ' . $schedule->doctor->last_name,
                'status' => "'" . $status . "'",
            ];

            $message = trans('messages.patient_messages', $details);

            $user->notify(new SystemNotification($user->user_id, $message, $schedule->clinic->name));

            DB::commit();
            return ['status' => true, 'message' => 'Patient status updated successfully.', 'data' => $patient_booking];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            DB::rollBack();
            return ['status' => false, 'message' => 'Something went wrong. Please try again later.'];
        }
    }

    /**
     * List Patient
     *
     * @return \Illuminate\Http\Response
     */

    public function listPatient()
    {
        try {
            $patients = User::role(AppConstants::PATIENT)->with('patientAdditionalInfo')->get();

            return ['status' => true, 'message' => 'Patients fetched successfully.', 'data' => $patients];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong. Please try again later.'];
        }
    }

    /**
     * Update Patient
     *
     * @return \Illuminate\Http\Response
     */

    public function updatePatient($data)
    {
        try {

            $user = User::where('id', $data['id'])->first();

            $user->first_name = $data['first_name'];
            $user->last_name = $data['last_name'];
            $user->mobile_number = $data['phone_number'];
            $user->email = $data['email'];
            if (isset($data['profile_image'])) {
                $imagePath = $data['profile_image']->store('image/profile_image', 'public');
                $user->profile_image = $imagePath;
            }
            $user->save();

            if (isset($data['additional_info'])) {
                //if patient additional info exists update else create
                $patient_additional_info = PatientAdditionalInfo::where('user_id', $user->id)->first();
                if ($patient_additional_info) {
                    $patient_additional_info->additional_info = $data['additional_info'];
                    $patient_additional_info->save();
                } else {
                    $patient_additional_info = new PatientAdditionalInfo();
                    $patient_additional_info->user_id = $user->id;
                    $patient_additional_info->additional_info = $data['additional_info'];
                    $patient_additional_info->save();
                }
            }



            return ['status' => true, 'message' => 'Patient updated successfully.', 'data' => $user];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong. Please try again later.'];
        }
    }

    /**
     * View Appoinments
     *
     * @return \Illuminate\Http\Response
     */

    public function viewAppoinments()
    {
        try {
            $user = Auth::user();
            //upcomming appoinments

            $upcoming_appointments = PatientBooking::with('schedule.doctor', 'clinic')
                ->whereHas('patient', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->whereHas('schedule', function ($query) {
                    $query->where('schedule_date', '>=', Carbon::now()->toDateString());
                })
                ->orderBy('start_time')
                ->orderBy('schedule_date')
                ->get();

            foreach ($upcoming_appointments as $key => $value) {
                $upcoming_appointments[$key]['schedule_status'] = $this->getDoctorScheduleStatus($value['schedule']['status']);
                $upcoming_appointments[$key]['current_status_name'] = $this->getPatientStatus($value['current_status']);
            }

            //past appoinments

            $past_appointments = PatientBooking::with('schedule.doctor', 'clinic')
                ->whereHas('patient', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->whereHas('schedule', function ($query) {
                    $query->where('schedule_date', '<', Carbon::now()->toDateString());
                })
                ->orderBy('start_time', 'desc')
                ->orderBy('schedule_date', 'desc')
                ->get();

            foreach ($past_appointments as $key => $value) {

                $clinic_ratings = Rating::where('user_id', $user->id)->where('relevent_id', $value['clinic_id'])->where('type', 1)->first();

                $doctor_ratings = Rating::where('user_id', $user->id)->where('relevent_id', $value['schedule']['doctor_id'])->where('type', 2)->first();

                if ($clinic_ratings && $doctor_ratings) {
                    $clinic_rating = 1;
                } else {
                    $clinic_rating = 0;
                }

                $past_appointments[$key]['schedule_status'] = $this->getDoctorScheduleStatus($value['schedule']['status']);
                $past_appointments[$key]['current_status_name'] = $this->getPatientStatus($value['current_status']);

                $past_appointments[$key]['rating_availability'] = $clinic_rating;
            }

            //today's appoinments

            $today_appointments = PatientBooking::with('schedule.doctor', 'clinic')
                ->whereHas('patient', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->whereHas('schedule', function ($query) {
                    $query->where('schedule_date', Carbon::now()->toDateString());
                })
                ->orderBy('start_time')
                ->orderBy('schedule_date')
                ->get();

            foreach ($today_appointments as $key => $value) {
                $today_appointments[$key]['schedule_status'] = $this->getDoctorScheduleStatus($value['schedule']['status']);
                $today_appointments[$key]['current_status_name'] = $this->getPatientStatus($value['current_status']);
            }

            return ['status' => true, 'message' => 'Appoinments fetched successfully.', 'data' => ['upcoming_appointments' => $upcoming_appointments, 'past_appointments' => $past_appointments, 'today_appointments' => $today_appointments]];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong. Please try again later.'];
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * ongoing Token
     */

    public function ongoingToken($data)
    {
        try {
            $schedule_id =  $data['schedule_id'];
            $booking_id =  $data['booking_id'];

            //get ongoing token
            // $ongoing_token = PatientBooking::with('schedule.doctor', 'clinic')
            $ongoing_token = PatientBooking::where('schedule_id', $schedule_id)
                ->where('current_status', AppConstants::PATIENT_STARTED)
                ->first();

            //get my token
            $mytoken = PatientBooking::with('schedule.doctor')->where('id', $booking_id)
                ->first();

            if ($mytoken) {

                $mytoken['schedule_status'] = $this->getDoctorScheduleStatus($mytoken['schedule']['current_status']);
                $mytoken['my_status'] = $this->getPatientStatus($mytoken['current_status']);
            }
            //get token status
            $patient_booking = PatientBooking::where('schedule_id', $schedule_id)
                ->orderBy('token', 'asc')
                ->get();

            //get curreny status from Appconstants
            foreach ($patient_booking as $key => $value) {
                $patient_booking[$key]['is_my_token'] = false;
                if ($value['id'] == $booking_id) {
                    $patient_booking[$key]['is_my_token'] = true;
                }

                $patient_booking[$key]['current_status_name'] = $this->getPatientStatus($value['current_status']);
            }

            if ($patient_booking) {
                return ['status' => true, 'message' => 'Ongoing token fetched successfully.', 'data' => ['ongoing_token' => $ongoing_token, 'patient_booking' => $patient_booking, 'mytoken' => $mytoken]];
            } else {
                return ['status' => false, 'message' => 'No ongoing token.'];
            }
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong. Please try again later.'];
        }
    }

    //getTotalBookings per day
    public function getTotalBookings($clinic_id)
    {
        try {
            $total_bookings = PatientBooking::where('clinic_id', $clinic_id)
                ->where('schedule_date', Carbon::now()->toDateString())
                ->where('current_status', '<>', AppConstants::PATIENT_CANCELLED)
                ->count();

            return $total_bookings;
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return 0;
        }
    }

    //getCompletedBookings per day
    public function getCompletedBookings($clinic_id)
    {
        try {
            $completed_bookings = PatientBooking::where('clinic_id', $clinic_id)
                ->where('schedule_date', Carbon::now()->toDateString())
                ->where('current_status', AppConstants::PATIENT_COMPLETED)
                ->count();

            return $completed_bookings;
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return 0;
        }
    }

    //getPendingBookings per day
    public function getPendingBookings($clinic_id)
    {

        try {
            $pending_bookings = PatientBooking::where('clinic_id', $clinic_id)
                ->where('schedule_date', Carbon::now()->toDateString())
                //get all data cuurent_status = PATIENT_NOT_ARRIVED or PATIENT_ARRIVED or PATIENT_DELAYED or PATIENT_WAITING
                ->where('current_status', '<>', AppConstants::PATIENT_COMPLETED)
                ->where('current_status', '<>', AppConstants::PATIENT_CANCELLED)
                ->count();

            return $pending_bookings;
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return 0;
        }
    }
}
