<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'created_by',
        'started_at',
        'ended_at',
    ];

    protected $attributes = [
        'status' => 'not_started',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(GameParticipant::class, 'game_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'game_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'game_id');
    }
}
