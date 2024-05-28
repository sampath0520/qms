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
 * Class Schedule
 *
 * @property int $id
 * @property int $doctor_id
 * @property Carbon $date
 * @property Carbon $start_time
 * @property Carbon $end_time
 * @property int $slots
 * @property int $avg_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int $is_active
 * @property int $current_status
 * @property int $clinic_id
 *
 * @property Doctor $doctor
 * @property Collection|PatientBooking[] $patient_bookings
 * @property Collection|ScheduleStatus[] $schedule_statuses
 * @property Collection|Token[] $tokens
 *
 * @package App\Models
 */
class Schedule extends Model
{
    use SoftDeletes;
    protected $table = 'schedules';

    protected $casts = [
        'doctor_id' => 'int',
        'slots' => 'int',
        'avg_time' => 'int',
        'is_active' => 'int',
        'current_status' => 'int',
        'clinic_id' => 'int'
    ];

    protected $fillable = [
        'doctor_id',
        'date',
        'start_time',
        'end_time',
        'slots',
        'avg_time',
        'is_active',
        'current_status',
        'clinic_id'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient_bookings()
    {
        return $this->hasMany(PatientBooking::class);
    }

    public function schedule_statuses()
    {
        return $this->hasMany(ScheduleStatus::class);
    }

    public function tokens()
    {
        return $this->hasMany(Token::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
