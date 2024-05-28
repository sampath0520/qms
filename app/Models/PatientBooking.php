<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * Class PatientBooking
 *
 * @property int $id
 * @property int $patient_id
 * @property string $symptoms
 * @property int $is_active
 * @property int $current_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deletd_at
 * @property int $schedule_id
 * @property int $token
 * @property int $clinic_id
 * @property Carbon|null $start_time
 * @property Carbon|null $end_time
 *
 * @property Patient $patient
 * @property Schedule $schedule
 * @property Collection|PatientStatus[] $patient_statuses
 *
 * @package App\Models
 */
class PatientBooking extends Model
{
    use Notifiable;
    protected $table = 'patient_bookings';

    protected $casts = [
        'patient_id' => 'int',
        'is_active' => 'int',
        'current_status' => 'int',
        'deletd_at' => 'datetime',
        'schedule_id' => 'int',
        'token' => 'int',
        'clinic_id' => 'int',
    ];

    protected $fillable = [
        'patient_id',
        'symptoms',
        'is_active',
        'current_status',
        'deletd_at',
        'schedule_id',
        'token',
        'clinic_id',
        'start_time',
        'end_time',
        'schedule_date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function patient_statuses()
    {
        return $this->hasMany(PatientStatus::class, 'booking_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
