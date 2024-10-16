<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use HasFactory;


    protected $fillable = ['name','relationship_to_student','contact_info','student_id'];



    public function students() {
        return $this->hasMany(Student::class);
    }
    public function notifications() {
        return $this->hasMany(Notification::class);
    }
}
