<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;


    protected $fillable = ['name', 'grade', 'section','school_year_id', 'guardian_id', 'tag_id','section_id'];

    public function guardians(){

        return $this->belongsToMany(Guardian::class, 'guardian_student')->withPivot('relationship_to_student');
    }
    public function sectionAttendances()
    {
        return $this->hasMany(AttendanceSectionTeacher::class);
    }
    public function rfidLogs(){
        return $this->hasMany(RfidLog::class);
    }

    public function attendances(){
        return $this->hasMany(Attendance::class);
    }

    public function tag(){
        return $this->belongsTo(Tag::class);
    }
    public function notifications() {
        return $this->hasMany(Notification::class);
    }
    public function schoolYear(){
        return $this->belongsTo(SchoolYear::class);
    }
    public function section(){

        return $this->belongsTo(Section::class);

    }
    public function tagHistories(){
        return $this->hasMany(TagHistory::class);
    }

    public function teachers(){

        return $this->belongsToMany(User::class,'student_teacher','student_id', 'teacher_id');
    }
}
