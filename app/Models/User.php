<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $mobile_number
 * @property int $clinic_id
 * @property string|null $profile_image
 * @property int $is_active
 * @property int $auth_type
 * @property string|null $middle_name
 *
 * @property Collection|Clinic[] $clinics
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use HasRoles, HasApiTokens, Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = 'users';

    protected $casts = [
        'email_verified_at' => 'datetime',
        'mobile_number' => 'string',
        'is_active' => 'int',
        'auth_type' => 'int'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'mobile_number',
        'profile_image',
        'is_active',
        'auth_type',
        'middle_name'
    ];

    // Define a one-to-many relationship with user_clinics
    public function userClinics()
    {
        return $this->hasMany(UserClinic::class);
    }

    // Define a many-to-many relationship with clinics through user_clinics
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'user_clinics', 'user_id', 'clinic_id');
    }

    // patient_additional_infos
    public function patientAdditionalInfo()
    {
        return $this->hasOne(PatientAdditionalInfo::class);
    }

    //patient
    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    //ratings
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
