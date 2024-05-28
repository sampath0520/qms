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
 * Class patient_additional_infos
 *
 *
 *
 * @package App\Models
 */

class PatientAdditionalInfo extends Model
{
    protected $table = 'patient_additional_infos';
    public $timestamps = false;


    protected $fillable = [
        'user_id',
        'additional_info'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
