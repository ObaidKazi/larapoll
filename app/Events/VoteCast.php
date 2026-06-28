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

    public function __construct(public int $pollId) {}

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
        $counter = app(VoteCounter::class);
        $counts  = $counter->getCounts($this->pollId);
        $total   = array_sum($counts);

        $poll = Poll::with('options')->find($this->pollId);

        return [
            'total'   => $total,
            'options' => $poll->options->map(fn ($opt) => [
                'id'          => $opt->id,
                'label'       => $opt->label,
                'votes_count' => $counts[$opt->id] ?? 0,
                'percentage'  => $total > 0
                    ? round((($counts[$opt->id] ?? 0) / $total) * 100, 1)
                    : 0,
            ]),
        ];
    }
}