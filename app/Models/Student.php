<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_number',
    ];

    protected function casts(): array
    {
        return [
            'student_number' => 'string',
        ];
    }

    public function participations(): HasMany
    {
        return $this->hasMany(GameParticipant::class, 'student_id');
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(
            Game::class,
            'game_participants',
            'student_id',
            'game_id'
        )
            ->withPivot('id', 'joined_at')
            ->withTimestamps();
    }
}
