<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PatientStatus
 * 
 * @property int $id
 * @property int $booking_id
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $delay_reason
 * 
 * @property PatientBooking $patient_booking
 *
 * @package App\Models
 */
class PatientStatus extends Model
{
	use SoftDeletes;
	protected $table = 'patient_statuses';

	protected $casts = [
		'booking_id' => 'int',
		'status' => 'int'
	];

	protected $fillable = [
		'booking_id',
		'status',
		'delay_reason'
	];

	public function patient_booking()
	{
		return $this->belongsTo(PatientBooking::class, 'booking_id');
	}
}
