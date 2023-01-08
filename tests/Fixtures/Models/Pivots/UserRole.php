<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Fixtures\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserRole extends Pivot
{
    protected $table = 'user_role';
}
