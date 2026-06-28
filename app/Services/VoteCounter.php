<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class VoteCounter
{

    public function hasVoted(int $pollId, string $ip): bool
    {
        return (bool) Redis::sismember($this->votersKey($pollId), $ip);
    }


    public function recordVote(int $pollId, int $optionId, string $ip): bool
    {
        $added = Redis::sadd($this->votersKey($pollId), $ip);

        if ($added === 0) {
            return false;
        }

        Redis::hincrby($this->countsKey($pollId), $optionId, 1);

        return true;
    }


    public function getCounts(int $pollId): array
    {
        $counts = Redis::hgetall($this->countsKey($pollId));

        return array_map('intval', $counts);
    }

    public function seed(int $pollId, array $optionCounts, array $voterIps): void
    {
        if (!empty($optionCounts)) {
            Redis::hmset($this->countsKey($pollId), $optionCounts);
        }
        if (!empty($voterIps)) {
            Redis::sadd($this->votersKey($pollId), ...$voterIps);
        }
    }

    public function totalVotes(int $pollId): int
    {
        return array_sum($this->getCounts($pollId));
    }

    private function votersKey(int $pollId): string
    {
        return "poll:{$pollId}:voters";
    }

    private function countsKey(int $pollId): string
    {
        return "poll:{$pollId}:counts";
    }
}