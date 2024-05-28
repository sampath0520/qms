<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UserClinic
 *
 * @property int $id
 * @property int $clinic_id
 * @property int $user_id
 * @property int $role_id
 *
 * @property Clinic $clinic
 * @property User $user
 *
 * @package App\Models
 */
class UserClinic extends Model
{
    protected $table = 'user_clinics';
    public $timestamps = false;

    protected $casts = [
        'clinic_id' => 'int',
        'user_id' => 'int',
        'role_id' => 'int'
    ];

    protected $fillable = [
        'clinic_id',
        'user_id',
        'role_id'
    ];

    // Define the inverse one-to-many relationship with users
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Define the inverse one-to-many relationship with clinics
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }
}
