<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function isAdmin(){
        return $this->role === 'admin';
    }

    public function isTeacher(){
        return $this->role === 'teacher';
    }

    public function sectionAttendances()
    {
        return $this->hasMany(AttendanceSectionTeacher::class,'teacher_id');
    }
    public function students(){
        return $this->belongsToMany(Student::class, 'student_teacher', 'teacher_id', 'student_id')
        ->withPivot('enrolled')
        ->withTimestamps();
    }
    


}
