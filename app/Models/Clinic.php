<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Clinic
 *
 * @property int $id
 * @property string $name
 * @property string $address_line_1
 * @property string|null $address_line_2
 * @property string $city
 * @property string $state
 * @property int $zip_code
 * @property string $email
 * @property int $contact_number
 * @property string|null $fax
 * @property string|null $info
 * @property int $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property Collection|Doctor[] $doctors
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Clinic extends Model
{
    use SoftDeletes;
    protected $table = 'clinics';

    protected $casts = [
        'zip_code' => 'int',
        'is_active' => 'int'
    ];

    protected $fillable = [
        'name',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip_code',
        'email',
        'contact_number',
        'fax',
        'info',
        'is_active',
        'lat',
        'long'
    ];

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctors_clinics')
            ->withPivot('id');
    }

    // Define a many-to-many relationship with users through user_clinics
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_clinics', 'clinic_id', 'user_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function patient_bookings()
    {
        return $this->hasMany(PatientBooking::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
