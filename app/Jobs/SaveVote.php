<?php

namespace App\Jobs;

use App\Models\PollOption;
use App\Models\Vote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveVote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 5;
    public int $backoff = 30; 

    public function __construct(
        public int $pollId,
        public int $optionId,
        public string $ip,
    ) {}

    public function handle(): void
    {
        DB::transaction(function () {
            $exists = Vote::where('poll_id', $this->pollId)
                ->where('ip_address', $this->ip)
                ->exists();

            if ($exists) {
                return;
            }

            Vote::create([
                'poll_id'        => $this->pollId,
                'poll_option_id' => $this->optionId,
                'ip_address'     => $this->ip,
            ]);

            PollOption::where('id', $this->optionId)->increment('votes_count');
        });
    }
    
    public function failed(\Throwable $exception): void
    {
        Log::error("SaveVote permanently failed for poll {$this->pollId}, option {$this->optionId}", [
            'ip' => $this->ip,
            'error' => $exception->getMessage(),
        ]);
    }
}