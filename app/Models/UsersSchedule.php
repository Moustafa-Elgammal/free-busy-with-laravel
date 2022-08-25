<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersSchedule extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'booked_schedule_slot'];
}
