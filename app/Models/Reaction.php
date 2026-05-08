<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['user_id', 'reactable_type', 'reactable_id', 'type'])]
class Reaction extends Model
{
    public $timestamps = false;

    public const TYPE_DISLIKE = 0;
    public const TYPE_LIKE = 1;
    public const TYPE_LOVE = 2;
    public const TYPE_HAHA = 3;
    public const TYPES = [
        self::TYPE_DISLIKE,
        self::TYPE_LIKE,
        self::TYPE_LOVE,
        self::TYPE_HAHA,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }
}
