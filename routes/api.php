<?php

use App\Http\Controllers\AppoinmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\RatingsController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UserController;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

//otp
Route::get('/otp/{id}', [OtpController::class, 'getOtp']);
//COMMON API'S
Route::group(['prefix' => 'common'], function () {

    Route::group(['prefix' => 'auth'], function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [PasswordController::class, 'sendResetLink']);
        Route::post('/reset-password', [PasswordController::class, 'resetPassword']);
        Route::post('/verify-otp', [PasswordController::class, 'verifyOtp']);

        Route::group(['middleware' => ['auth:api']], function () {
            Route::post('/change-password', [PasswordController::class, 'changePassword']);
            Route::get('/logout', [AuthController::class, 'logout']);
        });
    });

    //token
    Route::group(['prefix' => 'token'], function () {
        Route::get('/userCheck', [UserController::class, 'userCheck']);
    });

    Route::group(['prefix' => 'clinic'], function () {
        Route::get('/dropdown', [ClinicController::class, 'dropdownClinic']);
    });

    Route::group(['middleware' => ['auth:api']], function () {
        //ratings
        Route::group(['prefix' => 'ratings'], function () {
            Route::get('/clinic-ratings/{id}', [RatingsController::class, 'clinicRating']);
            Route::get('/doctor-ratings-count', [RatingsController::class, 'doctorRatingsCount']);
            Route::get('/doctor-ratings/{id}', [RatingsController::class, 'doctorRatings']);
        });
    });
});


//ADMIN API'S
Route::group(['prefix' => 'admin'], function () {

    //CLINIC API'S
    Route::group(['middleware' => ['auth:api', 'role:admin']], function () {
        Route::group(['prefix' => 'clinic'], function () {
            Route::post('/add', [ClinicController::class, 'addClinic']);
            Route::get('/list', [ClinicController::class, 'listClinic']);
            Route::put('/update', [ClinicController::class, 'updateClinic']);
            Route::delete('/delete/{id}', [ClinicController::class, 'deleteClinic']);
            Route::put('/activate-deactivate', [ClinicController::class, 'activateDeactivateClinic']);
        });

        //USER API'S
        Route::group(['prefix' => 'user'], function () {
            Route::post('/add', [UserController::class, 'createUser']);
            Route::post('/update', [UserController::class, 'updateUser']);
            Route::delete('/delete/{id}', [UserController::class, 'deleteUser']);
            Route::get('/list', [UserController::class, 'listUser']);
            Route::put('/activate-deactivate', [UserController::class, 'activateDeactivateUser']);
        });

        //Dashboard API'S
        Route::group(['prefix' => 'dashboard'], function () {
            Route::get('/list', [DashboardController::class, 'listDashboard']);
        });
    });
});

//RECEPTIONIST API'S
Route::group(['prefix' => 'receptionist'], function () {

    Route::group(['middleware' => ['auth:api', 'role:receptionist']], function () {

        //Doctor API'S
        Route::group(['prefix' => 'doctor'], function () {

            Route::post('/add', [DoctorController::class, 'addDoctor']);
            Route::get('/speciality', [DoctorController::class, 'speciality']);
            Route::post('/add-clinic', [DoctorController::class, 'addDocClinic']);
            Route::get('/list', [DoctorController::class, 'listDoctor']);
            Route::put('/update', [DoctorController::class, 'updateDoctor']);
            Route::delete('/delete/{id}', [DoctorController::class, 'deleteDoctor']);
            Route::put('/activate-deactivate', [DoctorController::class, 'activateDeactivateDoctor']);
            Route::get('/dropdown', [DoctorController::class, 'dropdownDoctor']);

            Route::post('/search', [DoctorController::class, 'searchDoctor']);
            Route::post('/speciality-search', [DoctorController::class, 'specialitySearch']);
        });

        //schedule API'S
        Route::group(['prefix' => 'schedule'], function () {

            Route::post('/add', [ScheduleController::class, 'addSchedule']);
            Route::get('/list/{id}', [ScheduleController::class, 'listSchedule']);

            Route::get('/status-dropdown', [ScheduleController::class, 'statusDropdown']);
            Route::put('/status-update', [ScheduleController::class, 'statusUpdate']);
            Route::put('/update', [ScheduleController::class, 'updateSchedule']);

            Route::delete('/delete/{id}', [ScheduleController::class, 'deleteSchedule']);
            Route::put('/activate-deactivate', [ScheduleController::class, 'activateDeactivateSchedule']);
            Route::post('/scheduled-dates', [ScheduleController::class, 'scheduledDates']);
            Route::post('/date-wise', [ScheduleController::class, 'dateWiseSchedule']);
            Route::get('/token/{id}', [ScheduleController::class, 'token']);

            Route::get('/details/{id}', [ScheduleController::class, 'details']);
        });

        //Booking API'S
        Route::group(['prefix' => 'patient'], function () {
            Route::post('/search', [PatientController::class, 'searchPatient']);
            Route::post('/add', [PatientController::class, 'AddPatient']);
            Route::get('/booked-list', [PatientController::class, 'bookedList']);

            Route::put('/status-update', [PatientController::class, 'statusUpdate']);


            Route::get('/list', [PatientController::class, 'listPatient']); //patinet means user's
            Route::put('/update', [PatientController::class, 'updatePatient']);

            // Route::delete('/delete/{id}', [PatientController::class, 'deleteUser']);
            // Route::put('/activate-deactivate', [PatientController::class, 'activateDeactivateUser']);
        });

        Route::group(['prefix' => 'dashboard'], function () {
            Route::get('/tiles', [DashboardController::class, 'dashboardTiles']);
        });

        //settings API'S
        Route::group(['prefix' => 'settings'], function () {
            Route::get('/profile', [UserController::class, 'profileSettings']);
            // Route::put('/clinic', [UserController::class, 'clinic-settings']);
            Route::post('/profile-update', [UserController::class, 'updateUser']);


            Route::put('/user', [UserController::class, 'user-settings']);
            Route::put('/clinic-update', [ClinicController::class, 'updateClinic']);
        });
    });
});


//mobile API'S
//RECEPTIONIST API'S
Route::group(['prefix' => 'mobile'], function () {
    Route::group(['prefix' => 'otp'], function () {
        Route::post('/send', [OtpController::class, 'sendOtp']);
        Route::post('/verify', [OtpController::class, 'verifyOtp']);
    });

    //Clinic API'S
    Route::group(['prefix' => 'clinic'], function () {
        Route::get('/list', [ClinicController::class, 'listClinic']);
    });


    Route::group(['middleware' => ['auth:api', 'role:patient']], function () {
        Route::group(['prefix' => 'apppoinments'], function () {
            Route::get('/view', [AppoinmentController::class, 'viewAppoinments']);
            Route::post('/ongoing-token', [AppoinmentController::class, 'ongoingToken']);
            Route::post('/status-update', [PatientController::class, 'statusUpdate']);
            Route::get('/token-details', [AppoinmentController::class, 'tokenDetails']);
        });

        //user
        Route::group(['prefix' => 'user'], function () {
            Route::post('/update', [PatientController::class, 'updatePatient']);
            Route::get('/delete-request', [UserController::class, 'deleteRequest']);
            Route::get('/details', [UserController::class, 'userDetails']);
        });

        //Notification
        Route::group(['prefix' => 'notifications'], function () {
            Route::get('/list', [NotificationController::class, 'getUnreadNotifications']);
            Route::put('/read/{id}', [NotificationController::class, 'markAsRead']);
        });

        // ratings
        Route::group(['prefix' => 'ratings'], function () {
            Route::post('/add', [RatingsController::class, 'addRating']);
            Route::get('/list', [RatingsController::class, 'listRating']);
        });
    });
});
