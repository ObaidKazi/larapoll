<?php

namespace App\Events;

use App\Models\Poll;
use App\Services\VoteCounter;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class VoteCast implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(public int $pollId, public array $results) {}

    public function broadcastOn(): Channel
    {
        return new Channel('poll.' . $this->pollId);
    }

    public function broadcastAs(): string
    {
        return 'vote.cast';
    }

    public function broadcastWith(): array
    {
        return $this->results;
    }
}