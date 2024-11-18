<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = ['guardian_id','message','date'];

    
    public function guardian() {
        return $this->belongsTo(Guardian::class);
    }
}
