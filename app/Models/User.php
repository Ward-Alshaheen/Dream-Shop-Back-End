<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method static where(string $string, array $email)
 * @method static create(array $input)
 */
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'created_at',
        'updated_at'
    ];
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }


    public function codes(): HasMany
    {
        return $this->hasMany(Code::class,'user_id');
    }
    public function products(): HasMany
    {
        return $this->hasMany(Product::class,'user_id');
    }
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class,'user_id');
    }
    public function views(): HasMany
    {
        return $this->hasMany(View::class,'user_id');
    }
    public function comment(): HasMany
    {
        return $this->hasMany(Comment::class,'user_id');
    }
}
