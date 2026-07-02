<?php

namespace Tests\Feature;

use App\Events\VoteCast;
use App\Jobs\SaveVote;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use App\Services\VoteCounter;
use App\Services\VoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
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
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Redis::flushdb();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        
        \Spatie\Permission\Models\Role::create(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        
        $this->superAdmin = User::factory()->superAdmin()->create();

        $this->poll = Poll::factory()->create([
            'is_active' => true,
            'user_id' => $this->superAdmin->id,
        ]);
        
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

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total',
                    'options' => [
                        '*' => ['id', 'label', 'votes_count', 'percentage']
                    ]
                ]
            ])
            ->assertJson(['success' => true]);

        $counter = app(VoteCounter::class);
        $this->assertEquals(1, $counter->getCounts($this->poll->id)[$this->optionA->id]);
        $this->assertEquals(1, $counter->totalVotes($this->poll->id));
        $this->assertTrue($counter->hasVoted($this->poll->id, $this->getServerIp()));

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
        $this->assertEquals(1, $counter->getCounts($this->poll->id)[$this->optionA->id]);
        $this->assertArrayNotHasKey($this->optionB->id, $counter->getCounts($this->poll->id));

        Queue::assertPushed(SaveVote::class, 1);
        Event::assertDispatched(VoteCast::class, 1);
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
        $this->assertEquals(2, $counter->getCounts($this->poll->id)[$this->optionA->id]);
        $this->assertTrue($counter->hasVoted($this->poll->id, '10.0.0.1'));
        $this->assertTrue($counter->hasVoted($this->poll->id, '10.0.0.2'));
        Queue::assertPushed(SaveVote::class, 2);
        Event::assertDispatched(VoteCast::class, 2);
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
    public function cannot_vote_on_expired_poll(): void
    {
        $this->poll->update(['ends_at' => now()->subHour()]);

        $response = $this->postJson("/polls/{$this->poll->id}/vote", [
            'poll_option_id' => $this->optionA->id,
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'This poll is closed.']);
    }

    /** @test */
    public function cannot_vote_with_option_from_another_poll(): void
    {
        $otherPoll = Poll::factory()->create([
            'is_active' => true,
            'user_id' => $this->superAdmin->id,
        ]);
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
        $this->optionA->refresh();
        $this->assertEquals(1, $this->optionA->votes_count);
    }

    /** @test */
    public function ensure_seeded_seeds_counts_and_voters_from_db(): void
    {
        // First, create some votes in the database
        (new SaveVote($this->poll->id, $this->optionA->id, '10.0.0.1'))->handle();
        (new SaveVote($this->poll->id, $this->optionA->id, '10.0.0.2'))->handle();
        (new SaveVote($this->poll->id, $this->optionB->id, '10.0.0.3'))->handle();

        // Clear Redis so we can test seeding
        Redis::flushdb();
        $counter = app(VoteCounter::class);
        $this->assertEmpty($counter->getCounts($this->poll->id));
        $this->assertFalse($counter->hasVoted($this->poll->id, '10.0.0.1'));

        // Call ensureSeeded
        $voteService = app(VoteService::class);
        $voteService->ensureSeeded($this->poll);

        // Verify counts are seeded
        $counts = $counter->getCounts($this->poll->id);
        $this->assertEquals(2, $counts[$this->optionA->id]);
        $this->assertEquals(1, $counts[$this->optionB->id]);
        $this->assertEquals(3, $counter->totalVotes($this->poll->id));

        // Verify voters are seeded
        $this->assertTrue($counter->hasVoted($this->poll->id, '10.0.0.1'));
        $this->assertTrue($counter->hasVoted($this->poll->id, '10.0.0.2'));
        $this->assertTrue($counter->hasVoted($this->poll->id, '10.0.0.3'));
        $this->assertFalse($counter->hasVoted($this->poll->id, '10.0.0.4'));
    }

    /** @test */
    public function sync_vote_counts_command_updates_db_from_redis(): void
    {
        // Create votes in Redis
        $counter = app(VoteCounter::class);
        $counter->recordVote($this->poll->id, $this->optionA->id, '10.0.0.1');
        $counter->recordVote($this->poll->id, $this->optionA->id, '10.0.0.2');
        $counter->recordVote($this->poll->id, $this->optionB->id, '10.0.0.3');

        // DB should still have 0 votes_count
        $this->optionA->refresh();
        $this->optionB->refresh();
        $this->assertEquals(0, $this->optionA->votes_count);
        $this->assertEquals(0, $this->optionB->votes_count);

        // Run sync command
        Artisan::call('app:sync-vote-counts');

        // DB should now be synced
        $this->optionA->refresh();
        $this->optionB->refresh();
        $this->assertEquals(2, $this->optionA->votes_count);
        $this->assertEquals(1, $this->optionB->votes_count);
    }

    /** @test */
    public function poll_results_have_correct_structure_and_percentages(): void
    {
        $counter = app(VoteCounter::class);
        $counter->recordVote($this->poll->id, $this->optionA->id, '10.0.0.1');
        $counter->recordVote($this->poll->id, $this->optionA->id, '10.0.0.2');
        $counter->recordVote($this->poll->id, $this->optionB->id, '10.0.0.3');

        $results = $counter->results($this->poll);

        $this->assertEquals(3, $results['total']);
        $this->assertCount(2, $results['options']);

        // Check Option A
        $optionAResult = collect($results['options'])->firstWhere('id', $this->optionA->id);
        $this->assertEquals('Option A', $optionAResult['label']);
        $this->assertEquals(2, $optionAResult['votes_count']);
        $this->assertEquals(66.7, $optionAResult['percentage']); // 2/3 ≈ 66.7%

        // Check Option B
        $optionBResult = collect($results['options'])->firstWhere('id', $this->optionB->id);
        $this->assertEquals('Option B', $optionBResult['label']);
        $this->assertEquals(1, $optionBResult['votes_count']);
        $this->assertEquals(33.3, $optionBResult['percentage']); // 1/3 ≈ 33.3%
    }

    /** @test */
    public function vote_cast_event_has_correct_data(): void
    {
        Event::fake([VoteCast::class]);
        Queue::fake();

        $this->postJson("/polls/{$this->poll->id}/vote", [
            'poll_option_id' => $this->optionA->id,
        ]);

        Event::assertDispatched(VoteCast::class, function (VoteCast $event) {
            $this->assertEquals($this->poll->id, $event->pollId);
            $this->assertIsArray($event->results);
            $this->assertArrayHasKey('total', $event->results);
            $this->assertArrayHasKey('options', $event->results);
            $this->assertEquals(1, $event->results['total']);
            return true;
        });
    }

    private function getServerIp(): string
    {
        return '127.0.0.1';
    }
}