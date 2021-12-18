<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $string, $id)
 * @method static create(array $array)
 * @method static find(int $id)
 */
class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'text'
    ];

    protected $hidden = [
        'user_id',
        'product_id',
        'created_at',
        'updated_at'
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
