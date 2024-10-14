<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use HasFactory;


    protected $fillable = ['name','relationship_to_student','contact_info','student_id'];



    public function student(){
        return $this->belongsTo(Student::class);
    }
}
