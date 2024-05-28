<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ScheduleStatus
 * 
 * @property int $id
 * @property int $schedule_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int $status
 * 
 * @property Schedule $schedule
 *
 * @package App\Models
 */
class ScheduleStatus extends Model
{
	use SoftDeletes;
	protected $table = 'schedule_statuses';

	protected $casts = [
		'schedule_id' => 'int',
		'status' => 'int'
	];

	protected $fillable = [
		'schedule_id',
		'status'
	];

	public function schedule()
	{
		return $this->belongsTo(Schedule::class);
	}
}
