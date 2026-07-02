<?php

namespace App\Models;
use App\Services\VoteCounter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Vote extends Model
{
    use HasFactory;
    protected $fillable = ['poll_id', 'poll_option_id', 'user_id', 'ip_address'];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }
    public function option()
    {
        return $this->belongsTo(PollOption::class, 'poll_option_id');
    }
    protected static function booted()
    {
        static::deleting(function (Vote $vote) {
            app(VoteCounter::class)->rollback(
                $vote->poll_id,
                $vote->poll_option_id,
                $vote->ip_address
            );
            $vote->option?->decrement('votes_count');
        });

        static::deleted(function (Vote $vote) {
            $poll = \App\Models\Poll::find($vote->poll_id);
            if ($poll) {
                broadcast(new \App\Events\VoteCast(
                    $poll->id,
                    app(VoteCounter::class)->results($poll)
                ));
            }
        });
    }
}
