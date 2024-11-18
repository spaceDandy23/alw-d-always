<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use HasFactory;


    protected $fillable = ['name','contact_info','school_year_id', 'import_batch_id'];

    public function importBatch()
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public function students(){
        return $this->belongsToMany(Student::class, 'guardian_student')->withPivot('relationship_to_student');

    }
    public function notifications() {
        return $this->hasMany(Notification::class);
    }
}
