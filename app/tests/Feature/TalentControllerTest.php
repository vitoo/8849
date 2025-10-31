<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Talent;
use App\Jobs\AyonSyncJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Models\User;

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

    public function testIndexReturnsOk()
    {
        $response = $this->actingAs($this->admin)->get(route('talents.index'));
        $response->assertStatus(200);
    }

    public function testStoreCreatesTalentAndDispatchesJob()
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

    public function testUpdateTalentAndDispatchesJob()
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

    public function testDestroyTalentAsAdminDispatchesJob()
    {
        Queue::fake();

        $talent = Talent::factory()->create();

        $this->actingAs($this->admin);
        $response = $this->delete(route('talents.destroy', $talent));

        $response->assertRedirect(route('talents.index'));
        $this->assertDatabaseMissing('talents', ['id' => $talent->id]);

        Queue::assertPushed(AyonSyncJob::class);
    }

    public function testDestroyTalentAsNonAdminReturns403()
    {
        $talent = Talent::factory()->create();

        $this->actingAs($this->user);
        $response = $this->delete(route('talents.destroy', $talent));

        $response->assertStatus(403);
        $this->assertDatabaseHas('talents', ['id' => $talent->id]);
    }
}
