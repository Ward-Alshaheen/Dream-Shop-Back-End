<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static find(int $id)
 * @method static create(array $product)
 * @method static where(string $string, mixed $category)
 */
class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'user_id',
        'images',
        'description',
        'category',
        'expiration_date',
        'remaining_days',
        'phone',
        'price',
        'discounts'
    ];
    protected $hidden = [
        'discounts',
        'updated_at',
        'created_at'
    ];
    public  function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
