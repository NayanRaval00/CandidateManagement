<?php

namespace App\Models;

use Database\Factories\InnoventixBotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InnoventixBot extends Model
{
    /** @use HasFactory<InnoventixBotFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'prompt',
        'sql_query',
        'results',
        'is_successful',
        'error_message',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'results' => 'array',
            'is_successful' => 'boolean',
        ];
    }

    /**
     * Get the user that executed the query.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
