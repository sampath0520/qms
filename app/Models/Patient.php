<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * Class Patient
 *
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property int $mobile_number
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property Carbon $birthdate
 *
 * @property Collection|PatientBooking[] $patient_bookings
 *
 * @package App\Models
 */
class Patient extends Model
{
    use SoftDeletes, Notifiable;
    protected $table = 'patients';

    protected $casts = [
        'mobile_number' => 'string'
    ];

    protected $fillable = [
        'name',
        'email',
        'mobile_number',
        'appoinment_for',
        'birth_date'
    ];

    public function patient_bookings()
    {
        return $this->hasMany(PatientBooking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
