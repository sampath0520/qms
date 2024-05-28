<?php

namespace App\Services;

use App\Constants\AppConstants;
use App\Helpers\ErrorLogger;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorsClinic;
use App\Models\Schedule;
use App\Models\SpecialityArea;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DoctorService
{
    /**
     * Add doctor
     *
     * @return \Illuminate\Http\Response
     */
    public function addDoctor($data)
    {
        try {
            //get logged in user's clinic
            $user = Auth::user();
            $clinic_id = $user->clinic_id;
            $speciality_arears = json_encode($data['speciality_arears']);

            DB::beginTransaction();
            $doc = new Doctor();
            $doc->first_name = $data['first_name'];
            $doc->last_name = $data['last_name'];
            $doc->email = $data['email'];
            $doc->mobile_number = $data['phone_number'];
            $doc->info = $data['additional_info'];
            $doc->speciality_areas = $speciality_arears;
            $doc->save();


            $clinic = DoctorsClinic::where('clinic_id', $clinic_id)->where('doctor_id', $doc->id)->first();
            if ($clinic) {
                return ['status' => false, 'message' => 'Doctor already exists.'];
            }

            $this->addDocClinic(['doctor_id' => $doc->id, 'clinic_id' => $data['clinic_id']]);


            //db commit
            DB::commit();
            return ['status' => true, 'message' => 'Doctor added successfully.'];
        } catch (\Exception $e) {
            DB::rollBack();
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /**
     * Speciality
     *
     * @return \Illuminate\Http\Response
     */
    public function speciality()
    {
        try {
            $speciality = SpecialityArea::select('id', 'speciality')->get();
            return ['status' => true, 'data' => $speciality, 'message' => 'Speciality list.'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /**
     * Add doctor clinic
     *
     * @return \Illuminate\Http\Response
     */
    public function addDocClinic($data)
    {
        try {
            $doClinics = new DoctorsClinic();
            $doClinics->doctor_id = $data['doctor_id'];
            $doClinics->clinic_id = $data['clinic_id'];
            $doClinics->save();

            return ['status' => true, 'message' => 'Doctor clinic added successfully.'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /**
     * Get doctor list
     *
     * @return \Illuminate\Http\Response
     */
    public function getDoctorList()
    {
        try {
            //doctors with doctor_clinics with clinics
            $doctors = Doctor::with('clinics')->get();
            //speciality areas json to array
            foreach ($doctors as $doctor) {
                $doctor->speciality_areas = json_decode($doctor->speciality_areas);
            }
            return ['status' => true, 'data' => $doctors, 'message' => 'Doctor list.'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /**
     * update Doctor
     *
     * @return \Illuminate\Http\Response
     */
    public function updateDoctor($data)
    {
        try {
            $speciality_arears = json_encode($data['speciality_arears']);

            DB::beginTransaction();
            $doc = Doctor::where('id', $data['id'])->first();
            $doc->first_name = $data['first_name'];
            $doc->last_name = $data['last_name'];
            $doc->email = $data['email'];
            $doc->mobile_number = $data['phone_number'];
            $doc->info = $data['additional_info'];
            $doc->speciality_areas = $speciality_arears;
            $doc->save();

            // //delete existing clinics related to doctor
            // $docClinics = DoctorsClinic::where('doctor_id', $data['id'])->delete();

            // //add clinic to doctor_clinics table
            // foreach ($data['clinic'] as $clinic_id) {
            //     $clinics_id = $clinic_id['id'];
            //     $this->addDocClinic(['doctor_id' => $data['id'], 'clinic_id' => $clinics_id]);
            // }

            DB::commit();
            return ['status' => true, 'message' => 'Doctor updated successfully.'];
        } catch (\Exception $e) {
            DB::rollBack();
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /**
     * delete Doctor
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteDoctor($id, $clinic_id)
    {
        try {

            //check doctor is assigned to schedule
            $schedule = Schedule::where('doctor_id', $id)->first();
            if ($schedule) {
                return ['status' => false, 'message' => 'Cannot delete doctor. Doctor is assigned to schedule.'];
            }

            $doctor = Doctor::where('id', $id)->first();
            if (!$doctor) {
                return ['status' => false, 'message' => 'Doctor not exists.'];
            }
            $docClinics = DoctorsClinic::where('doctor_id', $id)->get();
            if (count($docClinics) > 1) {
                $docClinics = DoctorsClinic::where('doctor_id', $id)->where('clinic_id', $clinic_id)->delete();
            } else {
                $docClinics = DoctorsClinic::where('doctor_id', $id)->delete();
                $doctor = Doctor::where('id', $id)->delete();
            }
            return ['status' => true, 'message' => 'Doctor deleted successfully.'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /**
     * activateDeactivateDoctor
     *
     * @return \Illuminate\Http\Response
     */
    public function activateDeactivateDoctor($data)
    {
        try {
            $doctor = Doctor::where('id', $data['doctor_id'])->first();
            if (!$doctor) {
                return ['status' => false, 'message' => 'Doctor not exists.'];
            }
            $doctor->is_active = $data['is_active'];
            $doctor->save();
            return ['status' => true, 'message' => 'Doctor status updated successfully.'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /*
        * dropdownDoctor
        *
        * @return \Illuminate\Http\Response
        */
    public function dropdownDoctor()
    {
        try {
            $doctors = Doctor::select('id', 'first_name', 'last_name')->get();
            return ['status' => true, 'data' => $doctors, 'message' => 'Doctor list.'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /*
        * searchDoctor
        *
        * @return \Illuminate\Http\Response
        */
    public function searchDoctor($data)
    {
        try {
            $doctors = Doctor::where('mobile_number', $data['search'])
                ->first();
            if (!$doctors) {
                return ['status' => false, 'message' => 'Doctor not found.'];
            }
            //speciality areas json to array
            $doctors->speciality_areas = json_decode($doctors->speciality_areas);


            return ['status' => true, 'data' => $doctors, 'message' => 'Doctor list.'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }

    /*
        * speciality Search
        *
        * @return \Illuminate\Http\Response
        */
    public function specialitySearch($request, $clinic_id)
    {
        try {
            $doctors = Doctor::whereJsonContains('speciality_areas',  [['id' => $request->speciality_id]])
                ->where('is_active', AppConstants::ACTIVE)
                ->whereHas('clinics', function ($query) use ($clinic_id) {
                    $query->where('clinic_id', $clinic_id);
                })
                ->get();
            return ['status' => true, 'data' => $doctors, 'message' => 'Doctor list.'];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong.'];
        }
    }
}
