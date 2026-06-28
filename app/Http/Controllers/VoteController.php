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

            $poll->loadMissing('options');

            return response()->json([
                'success' => true,
                'message' => 'Vote recorded',
                'data'    => $this->counter->results($poll),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}