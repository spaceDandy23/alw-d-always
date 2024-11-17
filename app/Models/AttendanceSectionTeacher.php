<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSectionTeacher extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'teacher_id', 'section_id', 'date', 'present', 'time'];



    public function student()
    {
        return $this->belongsTo(Student::class); 
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id'); 
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
