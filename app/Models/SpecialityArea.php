<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SpecialityArea
 * 
 * @property int $id
 * @property string $speciality
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class SpecialityArea extends Model
{
	use SoftDeletes;
	protected $table = 'speciality_areas';

	protected $fillable = [
		'speciality'
	];
}
