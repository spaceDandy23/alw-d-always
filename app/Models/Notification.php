<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = ['guardian_id', 'student_id', 'message'];

    public function student() {
        return $this->belongsTo(Student::class);
    }
    
    public function guardian() {
        return $this->belongsTo(Guardian::class);
    }
}
