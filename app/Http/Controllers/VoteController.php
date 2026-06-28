<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoteRequest;
use App\Models\Poll;
use App\Models\PollOption;
use App\Services\VoteCounter;
use App\Services\VoteService;
use Illuminate\Http\JsonResponse;

class VoteController extends Controller
{
    public function __construct(
        private VoteService $voteService,
        private VoteCounter $counter,
    ) {}

    public function store(VoteRequest $request, Poll $poll): JsonResponse
    {
        try {
            $option = PollOption::findOrFail($request->poll_option_id);

            $this->voteService->cast($poll, $option, $request->ip());

            $counts = $this->counter->getCounts($poll->id);
            $total  = array_sum($counts);
            $poll->loadMissing('options');

            return response()->json([
                'success' => true,
                'message' => 'Vote recorded',
                'data'    => [
                    'total'   => $total,
                    'options' => $poll->options->map(fn ($opt) => [
                        'id'          => $opt->id,
                        'votes_count' => $counts[$opt->id] ?? 0,
                        'percentage'  => $total > 0
                            ? round((($counts[$opt->id] ?? 0) / $total) * 100, 1)
                            : 0,
                    ]),
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}