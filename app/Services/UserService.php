<?php

namespace App\Services;

use App\Constants\AppConstants;
use App\Helpers\ErrorLogger;
use App\Mail\UserPasswordEmail;
use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
use App\Models\UserClinic;
use App\Models\UserDeleteRequest;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UserService
{
    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * add user
     */
    public function addUser($data)
    {
        try {

            if (isset($data['profile_image'])) {
                $imagePath = $data['profile_image']->store('image/profile_image', 'public');
            }

            //generate unique password according to  Password::min(8)->mixedCase()->numbers()->symbols()
            $password = $this->generatePassword();
            DB::beginTransaction();

            $user = User::create([
                // 'clinic_id' => $data['clinic_id'],
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'] ?? null,
                'mobile_number' => $data['phone_number'],
                'profile_image' => $imagePath ?? null,
                'password' => bcrypt($password),
                'is_active' => AppConstants::ACTIVE,
                'auth_type' => $data['auth_type'],
            ]);

            //assign role to user
            $role = Role::where('name', $data['role'])->first();
            $user->roles()->attach($role);
            $user->clinics()->attach($data['clinic_id'], ['role_id' => $role->id]);
            $email = Mail::to($data['email'])->send(new UserPasswordEmail($password));
            DB::commit();
            return ['status' => true, 'message' => 'User created successfully', 'data' => $user];
        } catch (\Exception $e) {
            DB::rollBack();
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }


    public static function generatePassword($length = 8)
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()-_+=<>?';

        $characters = $uppercase . $lowercase . $numbers . $symbols;
        $password = '';

        // Ensure at least one character from each category
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $symbols[rand(0, strlen($symbols) - 1)];

        // Generate the rest of the password
        for ($i = 0; $i < $length - 4; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }

        // Shuffle the password to randomize the characters
        return str_shuffle($password);
    }

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * update user
     */
    public function updateUser($data)
    {
        try {
            DB::beginTransaction();
            $user = User::find($data['user_id']);

            $user->first_name = $data['first_name'];
            $user->middle_name = $data['middle_name'];
            $user->last_name = $data['last_name'];
            $user->email = $data['email'];
            $user->mobile_number = $data['phone_number'];
            if (isset($data['profile_image'])) {
                $imagePath = $data['profile_image']->store('image/profile_image', 'public');
                $user->profile_image = $imagePath;
            }
            $user->save();

            //check if user is assigned to clinic
            $userClinic = UserClinic::where('user_id', $data['user_id'])->first();
            $role = $user->roles()->first()->id;

            //insert or update user_to_clinic table
            if ($userClinic) {
                UserClinic::where('user_id', $data['user_id'])->update(['clinic_id' => $data['clinic_id']]);
            } else {
                $user->clinics()->attach($data['clinic_id'], ['role_id' => $role]);
            }

            DB::commit();
            return ['status' => true, 'message' => 'User updated successfully', 'data' => $user];
        } catch (\Exception $e) {
            DB::rollBack();
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * delete user
     */

    public function deleteUser($id)
    {
        try {
            // $user = User::find($id);
            // $user->delete();

            // Get the user and clinic instances
            $user = User::find($id);
            if (!$user) {
                return ['status' => false, 'message' => 'User not found'];
            }
            $user->delete();
            $user->clinics()->detach();
            return ['status' => true, 'message' => 'User deleted successfully', 'data' => $user];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * list user
     */

    public function listUser()
    {
        try {
            //where role is receptionist
            $usersWithClinics = User::with('clinics')->whereHas('roles', function ($q) {
                $q->where('name', AppConstants::RECEPTIONIST);
            })
                // ->where('is_active', AppConstants::ACTIVE)
                ->get();
            return ['status' => true, 'message' => 'User list', 'data' => $usersWithClinics];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * activate or deactivate user
     */

    public function activateDeactivateUser($data)
    {
        try {
            $user = User::find($data['user_id']);
            $user->is_active = $data['is_active'];
            $user->save();
            return ['status' => true, 'message' => 'Status updated successfully', 'data' => $user];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * get Total Receptionists
     */

    public function getTotalReceptionists()
    {
        try {
            $totalReceptionists = User::whereHas('roles', function ($q) {
                $q->where('name', AppConstants::RECEPTIONIST);
            })
                ->where('is_active', AppConstants::ACTIVE)
                ->count();
            return $totalReceptionists;
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return 0;
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * get Total Admins
     */

    public function getTotalAdmins()
    {
        try {
            $totalAdmins = User::whereHas('roles', function ($q) {
                $q->where('name', AppConstants::ADMIN);
            })
                ->where('is_active', AppConstants::ACTIVE)
                ->count();
            return $totalAdmins;
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return 0;
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * get UserBy Mobile Number
     */

    public function getUserByMobileNumber($mobile_number)
    {
        try {
            $user = User::where('mobile_number', $mobile_number)->where('auth_type', AppConstants::OTP)->first();
            return $user;
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return 0;
        }
    }

    //deleteRequest
    public function deleteRequest()
    {
        try {
            //get logged in user
            $user = Auth::user();
            UserDeleteRequest::create([
                'user_id' => $user->id,
            ]);
            return ['status' => true, 'message' => 'User deleted successfully', 'data' => $user];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    //profileSettings
    public function profileSettings()
    {
        try {
            //get logged in user
            $user = Auth::user();
            //get logged users details with clinic
            $userWithClinic = User::with('clinics')->where('id', $user->id)->first();
            return ['status' => true, 'message' => 'User details', 'data' => $userWithClinic];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    //userDetails
    public function userDetails()
    {
        try {
            //get logged in user
            $user = Auth::user();
            //get logged users details with clinic
            $userWithClinic = User::with('clinics')->where('id', $user->id)->first();
            return ['status' => true, 'message' => 'User details', 'data' => $userWithClinic];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    //userCheck
    /**
     * Get the user details for the current token
     *
     */
    public function userCheck()
    {
        try {
            //get current token
            $token = Request()->bearerToken();
            //get logged in user
            $user = Auth::user();
            return [
                'token' => $token,
                'user' => $user
            ];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return false; // Return null instead of false
        }
    }
}
