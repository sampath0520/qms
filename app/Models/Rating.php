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
 * Class Clinic
 *
 * @property int $id
 * @property string $name
 * @property string $address_line_1
 * @property string|null $address_line_2
 * @property string $city
 * @property string $state
 * @property int $zip_code
 * @property string $email
 * @property int $contact_number
 * @property string|null $fax
 * @property string|null $info
 * @property int $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property Collection|Doctor[] $doctors
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Rating extends Model
{
    use SoftDeletes;
    protected $table = 'ratings';

    protected $fillable = [
        'id',
        'user_id',
        'rating',
        'type',
        'description',
        'relevent_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'relevent_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'relevent_id');
    }
}
