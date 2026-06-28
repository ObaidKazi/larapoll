<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VoteCounter;
use App\Models\Poll;
use App\Models\PollOption;
class SyncVoteCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-vote-counts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Redis vote counts into the database';

    /**
     * Execute the console command.
     */
    public function handle(VoteCounter $counter): int
    {
        Poll::where('is_active', true)->each(function (Poll $poll) use ($counter) {
            $counts = $counter->getCounts($poll->id);

            foreach ($counts as $optionId => $count) {
                PollOption::where('id', $optionId)->update(['votes_count' => $count]);
            }
        });

        $this->info('Vote counts synced.');
        return self::SUCCESS;
    }
}
