<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagHistory extends Model
{
    use HasFactory;




    protected $fillable = ['student_id', 'rfid_id'];


    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class, 'rfid_id');
    }
}
