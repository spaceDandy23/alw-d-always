<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;



    protected $fillable = ['grade', 'section'];



    public function students(){
        return $this->hasMany(Student::class);
    }
    public function sectionAttendances()
    {
        return $this->hasMany(AttendanceSectionTeacher::class);
    }
}
