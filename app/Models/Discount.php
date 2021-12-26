<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create($discount)
 * @method static firstWhere(string $string, $id)
 */
class Discount extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'main_price',
        'discount1',
        'discount2',
        'discount3',
        'remaining_days1',
        'remaining_days2',
        'remaining_days3'
    ];
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
