<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Fixtures\Models;

use DigitalCreative\Jaqen\Tests\Fixtures\Models\Pivots\UserRole;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * @property string name
 * @property Collection $users
 */
class Role extends AbstractModel
{
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(UserRole::class);
    }
}
