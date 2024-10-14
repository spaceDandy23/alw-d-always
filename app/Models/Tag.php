<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'rfid_tag'];


    public function student(){
        return $this->belongsTo(Student::class);
    }
}
