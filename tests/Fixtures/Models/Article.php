<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property string title
 * @property string content
 * @property User user
 */
class Article extends AbstractModel
{

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
