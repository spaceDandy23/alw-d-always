<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportBatch extends Model
{
    use HasFactory;
    protected $fillable = ['batch_name', 'imported_at'];


    public function students()
    {
        return $this->hasMany(Student::class);
    }


    public function guardians()
    {
        return $this->hasMany(Guardian::class);
    }


    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
