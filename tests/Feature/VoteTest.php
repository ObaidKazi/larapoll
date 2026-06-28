<?php

namespace Tests\Feature;

use App\Events\VoteCast;
use App\Jobs\SaveVote;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use App\Services\VoteCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    private Poll $poll;
    private PollOption $optionA;
    private PollOption $optionB;

    protected function setUp(): void
    {
        parent::setUp();

        Redis::flushdb();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        
        User::factory()->create(['is_admin' => true]);

        $this->poll    = Poll::factory()->create(['is_active' => true]);
        $this->optionA = PollOption::factory()->create(['poll_id' => $this->poll->id, 'label' => 'Option A']);
        $this->optionB = PollOption::factory()->create(['poll_id' => $this->poll->id, 'label' => 'Option B']);
    }

    protected function tearDown(): void
    {
        Redis::flushdb();
        parent::tearDown();
    }

    /** @test */
    public function guest_can_vote_once(): void
    {
        Event::fake([VoteCast::class]);
        Queue::fake();

        $response = $this->postJson("/polls/{$this->poll->id}/vote", [
            'poll_option_id' => $this->optionA->id,
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $counter = app(VoteCounter::class);
        $this->assertEquals(1, $counter->getCounts($this->poll->id)[$this->optionA->id]);

        Queue::assertPushed(SaveVote::class);

        Event::assertDispatched(VoteCast::class);
    }

    /** @test */
    public function guest_cannot_vote_twice_from_same_ip(): void
    {
        Event::fake([VoteCast::class]);
        Queue::fake();

        $this->postJson("/polls/{$this->poll->id}/vote", ['poll_option_id' => $this->optionA->id]);

        $response = $this->postJson("/polls/{$this->poll->id}/vote", ['poll_option_id' => $this->optionB->id]);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'You have already voted on this poll.']);

        $counter = app(VoteCounter::class);
        $this->assertEquals(1, $counter->totalVotes($this->poll->id));

        Queue::assertPushed(SaveVote::class, 1);
    }

    /** @test */
    public function different_ips_can_each_vote(): void
    {
        Event::fake([VoteCast::class]);
        Queue::fake();

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->postJson("/polls/{$this->poll->id}/vote", ['poll_option_id' => $this->optionA->id]);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
            ->postJson("/polls/{$this->poll->id}/vote", ['poll_option_id' => $this->optionA->id]);

        $counter = app(VoteCounter::class);
        $this->assertEquals(2, $counter->totalVotes($this->poll->id));
    }

    /** @test */
    public function cannot_vote_on_closed_poll(): void
    {
        $this->poll->update(['is_active' => false]);

        $response = $this->postJson("/polls/{$this->poll->id}/vote", [
            'poll_option_id' => $this->optionA->id,
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'This poll is closed.']);
    }

    /** @test */
    public function cannot_vote_with_option_from_another_poll(): void
    {
        $otherPoll   = Poll::factory()->create(['is_active' => true]);
        $otherOption = PollOption::factory()->create(['poll_id' => $otherPoll->id]);

        $response = $this->postJson("/polls/{$this->poll->id}/vote", [
            'poll_option_id' => $otherOption->id,
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    /** @test */
    public function save_job_writes_vote_to_database(): void
    {
        
        (new SaveVote($this->poll->id, $this->optionA->id, '10.0.0.1'))->handle();

        $this->assertDatabaseHas('votes', [
            'poll_id'        => $this->poll->id,
            'poll_option_id' => $this->optionA->id,
            'ip_address'     => '10.0.0.1',
        ]);

        $this->assertDatabaseHas('poll_options', [
            'id'          => $this->optionA->id,
            'votes_count' => 1,
        ]);
    }

    /** @test */
    public function save_job_does_not_double_write_same_ip(): void
    {
        (new SaveVote($this->poll->id, $this->optionA->id, '10.0.0.1'))->handle();
        (new SaveVote($this->poll->id, $this->optionA->id, '10.0.0.1'))->handle();

        $this->assertDatabaseCount('votes', 1);
    }
}