<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Patient;
use App\Services\ClinicService;
use App\Services\PatientService;
use App\Services\ScheduleService;
use App\Services\UserService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $userService;
    protected $clinicService;
    protected $scheduleService;
    protected $patientService;

    public function __construct(UserService $userService, ClinicService $clinicService, ScheduleService $scheduleService, PatientService $patientService)
    {
        $this->userService = $userService;
        $this->clinicService = $clinicService;
        $this->scheduleService = $scheduleService;
        $this->patientService = $patientService;
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * Get dashboard data
     */

    public function listDashboard()
    {
        $dashboardData = [];
        $dashboardData['totalReceptionists'] = $this->userService->getTotalReceptionists();
        $dashboardData['totalClinics'] = $this->clinicService->getTotalClinics();
        $dashboardData['totalAdmins'] = $this->userService->getTotalAdmins();
        return ResponseHelper::success('Dashboard data fetched successfully', $dashboardData);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * Get dashboard tiles for receptionist
     */

    public function dashboardTiles()
    {

        $clinic_id = $this->clinicService->getLoggedInUserClinic();

        $dashboardData = [];

        //total number of schedules for day
        $dashboardData['totalSchedules'] = $this->scheduleService->getTotalSchedules($clinic_id);

        //total number of bookings for day
        $dashboardData['totalBookings'] = $this->patientService->getTotalBookings($clinic_id);

        //completed bookings for day
        $dashboardData['completedBookings'] = $this->patientService->getCompletedBookings($clinic_id);

        //pending bookings for day
        $dashboardData['pendingBookings'] = $this->patientService->getPendingBookings($clinic_id);

        return ResponseHelper::success('Dashboard data fetched successfully', $dashboardData);
    }
}
