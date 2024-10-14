<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;


    protected $fillable = ['name', 'grade', 'section'];


    public function guardian(){
        return $this->hasOne(Guardian::class);
    }
    public function rfidLogs(){
        return $this->hasMany(RfidLog::class);
    }

    public function attendances(){
        return $this->hasMany(Attendance::class);
    }

    public function tag(){
        return $this->hasOne(Tag::class);
    }

}
