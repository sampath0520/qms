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
class SmsLog extends Model
{
    protected $table = 'sms_logs';

    protected $casts = [
        'recipients' => 'string',
        'message' => 'string',
        'response' => 'string',
        'type' => 'string'
    ];

    protected $fillable = [
        'recipients',
        'message',
        'response',
        'type',
        'status',
        'content'
    ];
}
