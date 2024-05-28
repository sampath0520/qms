<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Token
 * 
 * @property int $id
 * @property int $schedule_id
 * @property int $token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Schedule $schedule
 *
 * @package App\Models
 */
class Token extends Model
{
	use SoftDeletes;
	protected $table = 'tokens';
	public $incrementing = false;

	protected $casts = [
		'id' => 'int',
		'schedule_id' => 'int',
		'token' => 'int'
	];

	protected $hidden = [
		'token'
	];

	protected $fillable = [
		'schedule_id',
		'token'
	];

	public function schedule()
	{
		return $this->belongsTo(Schedule::class);
	}
}
