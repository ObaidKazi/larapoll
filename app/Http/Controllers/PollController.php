<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Services\VoteCounter;
use App\Services\VoteService;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function __construct(
        private VoteService $voteService,
        private VoteCounter $counter,
    ) {}

    public function index()
    {
        return view('polls.index');
    }

    public function show(Request $request, string $slug)
    {
        $poll = Poll::with('options')->where('slug', $slug)->firstOrFail();

        $this->voteService->ensureSeeded($poll);

        $hasVoted = $this->voteService->hasVoted($poll, $request->ip());

        $counts = $this->counter->getCounts($poll->id);

        return view('polls.show', compact('poll', 'hasVoted', 'counts'));
    }
}