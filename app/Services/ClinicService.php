<?php

namespace App\Services;

use App\Constants\AppConstants;
use App\Helpers\ErrorLogger;
use App\Models\Clinic;
use App\Models\DoctorsClinic;
use App\Models\User;
use App\Models\UserClinic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpParser\Comment\Doc;

class ClinicService
{

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * Add clinic
     */

    public function addClinic($data)
    {


        try {
            $clinic = Clinic::create([
                'name' => $data['name'],
                'address_line_1' => $data['address_1'],
                'address_line_2' => $data['address_2'],
                'city' => $data['city'],
                'state' => $data['state'],
                'zip_code' => $data['zip_code'],
                'lat' => $data['lat'],
                'long' => $data['long'],
                'email' => $data['email'],
                'contact_number' => $data['contact_number'],
                'fax' => $data['fax'],
                'info' => $data['additional_info'],
                'is_active' => AppConstants::ACTIVE,
            ]);
            return ['status' => true, 'message' => 'Clinic added successfully', 'data' => $clinic];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * List clinic
     */

    public function listClinic()
    {
        try {
            $clinic = Clinic::get();
            return ['status' => true, 'message' => 'Clinic list', 'data' => $clinic];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * Update clinic
     */

    public function updateClinic($data)
    {
        try {
            $clinic = Clinic::where('id', $data['id'])->update([
                'name' => $data['name'],
                'address_line_1' => $data['address_1'],
                'address_line_2' => $data['address_2'],
                'city' => $data['city'],
                'state' => $data['state'],
                'zip_code' => $data['zip_code'],
                'lat' => $data['lat'],
                'long' => $data['long'],
                'email' => $data['email'],
                'contact_number' => $data['contact_number'],
                'fax' => $data['fax'],
                'info' => $data['additional_info'],
            ]);
            return ['status' => true, 'message' => 'Clinic updated successfully', 'data' => $clinic];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * Delete clinic
     */

    public function deleteClinic($id)
    {
        try {
            //check doctors are assigned to clinic in doctor_clinic table
            $checkDoctorClinic = DoctorsClinic::where('clinic_id', $id)->first();

            //cehck users are assigned to clinic in user_clinic table
            $checkUserClinic = UserClinic::where('clinic_id', $id)->first();

            if ($checkUserClinic) {
                return ['status' => false, 'message' => 'Clinic cannot be deleted as users are assigned to this clinic'];
            }

            if ($checkDoctorClinic) {
                return ['status' => false, 'message' => 'Clinic cannot be deleted as doctors are assigned to this clinic'];
            }
            $delete = Clinic::where('id', $id)->delete();
            if (!$delete) {
                return ['status' => false, 'message' => 'No clinic found'];
            }
            return ['status' => true, 'message' => 'Clinic deleted successfully'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $data[]
     * @return string []
     * Activate deactivate clinic
     */

    public function activateDeactivateClinic($data)
    {
        try {
            $clinic = Clinic::where('id', $data['clinic_id'])->update([
                'is_active' => $data['is_active'],
            ]);
            return ['status' => true, 'message' => 'Status updated successfully', 'data' => $clinic];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * Get total clinics
     */

    public function getTotalClinics()
    {
        try {
            $totalClinics = Clinic::count();
            return $totalClinics;
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return 0;
        }
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * dropdown Clinic
     */

    public function dropdownClinic()
    {
        try {
            $dropdownClinic = Clinic::select('id', 'name')->where('is_active', AppConstants::ACTIVE)->get();
            return ['status' => true, 'message' => 'Clinic list', 'data' => $dropdownClinic];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * get logged in user's clinic
     */

    public function getLoggedInUserClinic()
    {
        try {
            $user = Auth::user();
            $clinic = UserClinic::where('user_id', $user->id)->first();
            return $clinic['clinic_id'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }
}
