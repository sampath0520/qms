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
 * Class Doctor
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $speciality_areas
 * @property string $email
 * @property int $mobile_number
 * @property string|null $info
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $is_active
 *
 * @property Collection|Clinic[] $clinics
 * @property Collection|Schedule[] $schedules
 *
 * @package App\Models
 */
class Doctor extends Model
{
    use SoftDeletes;
    protected $table = 'doctors';

    protected $casts = [
        'mobile_number' => 'string',
        'is_active' => 'int'
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'speciality_areas',
        'email',
        'mobile_number',
        'info',
        'is_active'
    ];

    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'doctors_clinics')
            ->withPivot('id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
