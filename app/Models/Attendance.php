<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;


    protected $fillable = ['student_id', 'check_in_time', 'date', 'status_morning', 'status_lunch'];



    public function student(){
        return $this->belongsTo(Student::class);
    }
}
