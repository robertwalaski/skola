<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'course', 'section', 'exercise_key', 'correct', 'points'])]
class Attempt extends Model
{
    // Attempts are an immutable log - no updated_at column.
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'correct' => 'boolean',
            'points' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
