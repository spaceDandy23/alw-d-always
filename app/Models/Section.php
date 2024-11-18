<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;



    protected $fillable = ['grade', 'section','import_batch_id'];


    public function importBatch()
    {
        return $this->belongsTo(ImportBatch::class);
    }
    public function students(){
        return $this->hasMany(Student::class);
    }
    public function sectionAttendances()
    {
        return $this->hasMany(AttendanceSectionTeacher::class);
    }
}
