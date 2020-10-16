<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 */
class AbstractModel extends Model
{

    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
    ];

}
