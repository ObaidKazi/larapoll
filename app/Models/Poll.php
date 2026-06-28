<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
class Poll extends Model
{
    use HasFactory;
    protected $fillable = [
        'slug',
        'question',
        'is_active',
        'ends_at',
    ];

    protected $table = 'polls';
    protected $casts = [
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function booted(){
        static::creating(function (Poll $poll) {
             $poll->slug = Str::slug($poll->question) . '-' . Str::random(6);
        });
    }


    public function options()
    {
        return $this->hasMany(PollOption::class)->orderBy('sort_order');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function getTotalVotesAttribute(): int
    {
        return $this->options->sum('votes_count');
    }

    public function getShareUrlAttribute(): string
    {
        return route('polls.show', $this->slug);
    }

    public function isExpired(): bool
    {
        return $this->ends_at !== null && $this->ends_at->isPast();
    }

    public function isOpen(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }
}
