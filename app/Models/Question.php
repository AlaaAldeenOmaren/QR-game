<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'source_code',
        'question_text',
        'type',
        'max_points',
    ];

    protected $attributes = [
        'max_points' => 10,
    ];

    protected function casts(): array
    {
        return [
            'max_points' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Question $question): void {
            if (empty($question->qr_token)) {
                $question->qr_token = (string) Str::uuid();
            }
        });
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class, 'question_id')
            ->orderBy('label');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'question_id');
    }
}
