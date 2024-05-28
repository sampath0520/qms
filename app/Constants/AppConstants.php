<?php

namespace App\Constants;

class AppConstants
{

    const WEBSITE_LINK = "http://127.0.0.1:8000/";

    //user roles
    const ADMIN = 'admin';
    const RECEPTIONIST = 'receptionist';
    const PATIENT = 'patient';
    const ADMIN_ROLE_ID = 1;
    const RECEPTIONIST_ROLE_ID = 2;
    const PATIENT_ROLE_ID = 3;

    // common statues
    const ACTIVE = 1;
    const INACTIVE = 0;

    //auth type
    const CREDENTIALS = 1;
    const OTP = 2;

    //Doctor/Schedule status
    const DS_PENDING = 1;
    const DS_ARRIVED = 2;
    const DS_WAITING = 3;
    const DS_STARTED = 4;
    const DS_DELAYED = 5;
    const DS_CANCELLED = 6;
    const DS_COMPLETED = 7;

    //Patient status
    const PATIENT_NOT_ARRIVED = 1;
    const PATIENT_ARRIVED = 2;
    const PATIENT_DELAYED = 3;
    const PATIENT_WAITING = 4;
    const PATIENT_STARTED = 5;
    const PATIENT_CANCELLED = 6;
    const PATIENT_COMPLETED = 7;




    const MY_SELF = 1;
    const OTHER = 2;

    public static function getConstantsStartingWith($prefix)
    {
        $class = new \ReflectionClass(__CLASS__);
        $constants = $class->getConstants();

        $filteredConstants = [];
        foreach ($constants as $name => $value) {
            if (strpos($name, $prefix) === 0) {
                $filteredConstants[$name] = $value;
            }
        }

        return $filteredConstants;
    }
}
