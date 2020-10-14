<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property string name
 * @property string gender
 * @property string email
 * @property Collection $articles
 * @property Phone|null $phone
 */
class User extends Model
{

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function phone(): HasOne
    {
        return $this->hasOne(Phone::class);
    }
}
