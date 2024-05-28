<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DoctorsClinic
 *
 * @property int $id
 * @property int $doctor_id
 * @property int $clinic_id
 *
 * @property Doctor $doctor
 * @property Clinic $clinic
 *
 * @package App\Models
 */
class Otp extends Model
{
    protected $table = 'otp';
    // public $timestamps = false;

    protected $casts = [
        'user_id' => 'int'
    ];

    protected $fillable = [
        'user_id',
        'otp_code',
        'expiration_time',
    ];
}
