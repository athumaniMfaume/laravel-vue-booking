<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{

    use HasFactory;

    protected $table = 'bookings';

    protected $with = ['user', 'service'];


    protected $fillable = ['user_id', 'service_id', 'opening_hours'];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function service(){
        return $this->belongsTo(Service::class,'service_id');
    }


}
