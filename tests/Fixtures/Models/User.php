<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Fixtures\Models;

use DigitalCreative\Jaqen\Tests\Fixtures\Models\Pivots\UserRole;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

/**
 * @property string name
 * @property string gender
 * @property string email
 * @property Collection $articles
 * @property Phone|null $phone
 * @property Collection $roles
 */
class User extends AbstractModel
{
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function phone(): HasOne
    {
        return $this->hasOne(Phone::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->using(UserRole::class)
            ->withPivot('extra');
    }

    public function rolesWithCustomAccessor(): BelongsToMany
    {
        return $this->roles()->as('customAccessor');
    }
}
