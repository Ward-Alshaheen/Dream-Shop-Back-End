<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static find(int $id)
 * @method static create(array $product)
 * @method static where(string $string, mixed $category)
 * @method static orderBy(string $string)
 * @method static withCount(string $string)
 * @method static join(string $string, string $string1, string $string2, string $string3)
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
        'price',
        'quantity',
    ];
    protected $hidden = [
        'updated_at',
        'created_at',
        'discount'
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
    public function comment(): HasMany
    {
        return $this->hasMany(Comment::class,'product_id');
    }
    public function images(): HasMany
    {
        return $this->hasMany(Image::class,'product_id');
    }
    public function discount(): HasOne
    {
        return $this->hasOne(Discount::class,'product_id');
    }
}
