<?php

namespace Tests\Feature;

use App\Models\SpamSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpamSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_spam_submissions_older_than_ninety_days_are_pruned(): void
    {
        $this->freezeTime();

        $expired = SpamSubmission::factory()->create([
            'created_at' => now()->subDays(90),
        ]);
        $kept = SpamSubmission::factory()->create([
            'created_at' => now()->subDays(90)->addSecond(),
        ]);

        $this->artisan('model:prune')->assertSuccessful();

        $this->assertModelMissing($expired);
        $this->assertModelExists($kept);
    }

    public function test_model_prune_is_scheduled_daily(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('model:prune')
            ->assertSuccessful();
    }
}
