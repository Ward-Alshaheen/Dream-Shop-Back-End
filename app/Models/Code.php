<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $code)
 * @method static where(string $string, mixed $code)
 */
class Code extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'user_id',
    ];
    public  function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
