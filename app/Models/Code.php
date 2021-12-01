<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'user_id',
    ];
    public  function user(){
        return $this->belongsTo('App\Models\User','user_id');
    }
}
