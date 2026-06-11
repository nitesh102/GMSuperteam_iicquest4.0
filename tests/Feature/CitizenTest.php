<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CitizenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Citizen']);
        Role::firstOrCreate(['name' => 'Superadmin']);
    }

    protected function forceSimulationFallback(): void
    {
        $mock = $this->createMock(\App\Services\ComplaintAiService::class);
        $mock->method('analyzeComplaint')
            ->willThrowException(new \Exception('Simulated AI failure for testing'));
        $this->app->instance(\App\Services\ComplaintAiService::class, $mock);
    }

    public function test_citizen_dashboard_page_can_be_rendered(): void
    {
        $user = User::factory()->citizen()->create();

        $response = $this->actingAs($user)->get('/citizen/dashboard');

        $response->assertOk();
    }

    public function test_citizen_dashboard_shows_only_own_stats(): void
    {
        $user = User::factory()->citizen()->create();
        $otherUser = User::factory()->citizen()->create();

        Complaint::factory()->count(3)->create(['citizen_id' => $user->id]);
        Complaint::factory()->count(2)->create(['citizen_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get('/citizen/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Citizen/Dashboard')
            ->where('stats.total', 3)
        );
    }

    public function test_citizen_complaints_index_returns_only_own(): void
    {
        $user = User::factory()->citizen()->create();
        $otherUser = User::factory()->citizen()->create();

        Complaint::factory()->count(2)->create(['citizen_id' => $user->id]);
        Complaint::factory()->count(3)->create(['citizen_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get('/citizen/complaints');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Citizen/Index')
            ->has('complaints', 2)
        );
    }

    public function test_citizen_create_page_can_be_rendered(): void
    {
        $user = User::factory()->citizen()->create();

        $response = $this->actingAs($user)->get('/citizen/complaints/create');

        $response->assertOk();
    }

    public function test_citizen_can_create_complaint(): void
    {
        $user = User::factory()->citizen()->create();
        $category = ComplaintCategory::factory()->create();

        $this->forceSimulationFallback();

        $response = $this->actingAs($user)->post('/citizen/complaints', [
            'title' => 'Pothole on Main Street',
            'description' => 'There is a large pothole causing traffic issues.',
            'category_id' => $category->id,
            'location' => 'Main Street, Downtown',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

        $response->assertRedirect(route('citizen.complaints.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('complaints', [
            'title' => 'Pothole on Main Street',
            'citizen_id' => $user->id,
            'category_id' => $category->id,
            'current_status' => 'submitted',
        ]);
    }

    public function test_citizen_can_view_own_complaint(): void
    {
        $user = User::factory()->citizen()->create();
        $complaint = Complaint::factory()->create(['citizen_id' => $user->id]);

        $response = $this->actingAs($user)->get("/citizen/complaints/{$complaint->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Citizen/Show')
            ->where('complaint.id', $complaint->id)
        );
    }

    public function test_citizen_cannot_view_others_complaint(): void
    {
        $user = User::factory()->citizen()->create();
        $otherUser = User::factory()->citizen()->create();
        $complaint = Complaint::factory()->create(['citizen_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/citizen/complaints/{$complaint->id}");

        $response->assertForbidden();
    }

    public function test_superadmin_cannot_access_citizen_routes(): void
    {
        $user = User::factory()->superadmin()->create();

        $response = $this->actingAs($user)->get('/citizen/dashboard');

        $response->assertForbidden();
    }

    public function test_citizen_cannot_access_admin_complaint_routes(): void
    {
        $user = User::factory()->citizen()->create();
        $complaint = Complaint::factory()->create(['citizen_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/complaints/{$complaint->id}");

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_citizen_routes(): void
    {
        $response = $this->get('/citizen/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_citizen_registration_assigns_citizen_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Citizen',
            'email' => 'citizen@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'citizenship_number' => '12-3456-78901234',
            'phone_number' => '1234567890',
            'address' => '123 Main St',
        ]);

        $response->assertRedirect(route('citizen.dashboard'));

        $user = User::where('email', 'citizen@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Citizen'));
        $this->assertEquals('12-3456-78901234', $user->citizenship_number);
    }
}
