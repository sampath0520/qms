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
class DoctorsClinic extends Model
{
	protected $table = 'doctors_clinics';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id' => 'int',
		'doctor_id' => 'int',
		'clinic_id' => 'int'
	];

	protected $fillable = [
		'doctor_id',
		'clinic_id'
	];

	public function doctor()
	{
		return $this->belongsTo(Doctor::class);
	}

	public function clinic()
	{
		return $this->belongsTo(Clinic::class);
	}
}
