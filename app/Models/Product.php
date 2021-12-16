<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static find(int $id)
 * @method static create(array $product)
 * @method static where(string $string, mixed $category)
 * @method static orderBy(string $string)
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
        'discounts',
        'quantity'
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
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class,'product_id');
    }
    public function views(): HasMany
    {
        return $this->hasMany(View::class,'product_id');
    }
}
