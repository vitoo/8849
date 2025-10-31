<?php

namespace Tests\Feature;

use App\Jobs\AyonSyncJob;
use App\Models\Talent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TalentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Crée un user admin
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
    }

    public function test_index_returns_ok()
    {
        $response = $this->actingAs($this->admin)->get(route('talents.index'));
        $response->assertStatus(200);
    }

    public function test_store_creates_talent_and_dispatches_job()
    {
        Queue::fake();

        $this->actingAs($this->admin);
        $response = $this->post(route('talents.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertRedirect(route('talents.index'));
        $this->assertDatabaseHas('talents', ['email' => 'john@example.com']);

        Queue::assertPushed(AyonSyncJob::class);
    }

    public function test_update_talent_and_dispatches_job()
    {
        Queue::fake();

        $talent = Talent::factory()->create();

        $this->actingAs($this->admin);
        $response = $this->put(route('talents.update', $talent), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect(route('talents.index'));
        $this->assertDatabaseHas('talents', ['email' => 'updated@example.com']);

        Queue::assertPushed(AyonSyncJob::class);
    }

    public function test_destroy_talent_as_admin_dispatches_job()
    {
        Queue::fake();

        $talent = Talent::factory()->create();

        $this->actingAs($this->admin);
        $response = $this->delete(route('talents.destroy', $talent));

        $response->assertRedirect(route('talents.index'));
        $this->assertDatabaseMissing('talents', ['id' => $talent->id]);

        Queue::assertPushed(AyonSyncJob::class);
    }

    public function test_destroy_talent_as_non_admin_returns403()
    {
        $talent = Talent::factory()->create();

        $this->actingAs($this->user);
        $response = $this->delete(route('talents.destroy', $talent));

        $response->assertStatus(403);
        $this->assertDatabaseHas('talents', ['id' => $talent->id]);
    }
}
