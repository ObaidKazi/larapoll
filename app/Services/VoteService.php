<?php

namespace App\Services;

use App\Events\VoteCast;
use App\Jobs\SaveVote;
use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Support\Facades\Cache;

class VoteService
{
    public function __construct(private VoteCounter $counter)
    {
    }


    public function cast(Poll $poll, PollOption $option, string $ip): void
    {
        if (!$poll->isOpen()) {
            throw new \RuntimeException('This poll is closed.');
        }

        if ($option->poll_id !== $poll->id) {
            throw new \RuntimeException('That option does not belong to this poll.');
        }

        $recorded = $this->counter->recordVote($poll->id, $option->id, $ip);

        if (!$recorded) {
            throw new \RuntimeException('You have already voted on this poll.');
        }

        SaveVote::dispatch($poll->id, $option->id, $ip);

        $results = $this->counter->results($poll); 
        
        broadcast(new VoteCast($poll->id, $results));
    }

    public function hasVoted(Poll $poll, string $ip): bool
    {
        return $this->counter->hasVoted($poll->id, $ip);
    }

    public function ensureSeeded(Poll $poll): void
    {
        $counts = $this->counter->getCounts($poll->id);

        if (!empty($counts)) {
            return;
        }
        $lockKey = "poll:{$poll->id}:seed-lock";
        $lock = Cache::lock($lockKey, 10);

        if ($lock->get()) {
            try {
                $counts = $this->counter->getCounts($poll->id);
                if (!empty($counts)) {
                    return;
                }
                $poll->loadMissing('options');
                $optionCounts = $poll->options->pluck('votes_count', 'id')->toArray();
                $voterIps = $poll->votes()->whereNotNull('ip_address')->pluck('ip_address')->toArray();
                $this->counter->seed($poll->id, $optionCounts, $voterIps);
            } finally {
                $lock->release();
            }
        } else {
            usleep(100000); 
        }
    }
}