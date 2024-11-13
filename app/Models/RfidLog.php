<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfidLog extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'rfid_tag', 'check_in', 'date', 'check_out', 'tag_id'];



    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function tag(){

        return $this->belongsTo(Tag::class);
        
    }
}

