<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['rfid_tag'];


    public function student(){
        return $this->hasOne(Student::class);
    }

    public function rfidLogs(){

        return $this->hasMany(RfidLog::class, 'tag_id');
    }

    public function tagHistories()
    {
        return $this->hasMany(TagHistory::class, 'rfid_id'); 
    }
}
