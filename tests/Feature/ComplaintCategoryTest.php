<?php

namespace Tests\Feature;

use App\Models\ComplaintCategory;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplaintCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Superadmin']);
    }

    public function test_index_page_can_be_rendered(): void
    {
        $user = User::factory()->superadmin()->create();
        ComplaintCategory::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/complaint-categories');

        $response->assertOk();
    }

    public function test_category_can_be_created(): void
    {
        $user = User::factory()->superadmin()->create();
        $department = Department::factory()->create();

        $response = $this->actingAs($user)->post('/complaint-categories', [
            'department_id' => $department->id,
            'name' => 'Road Repair',
            'description' => 'Issues related to road damage.',
        ]);

        $response->assertRedirect(route('complaint-categories.index'));
        $response->assertSessionHas('success', 'Complaint category created successfully.');

        $this->assertDatabaseHas('complaint_categories', [
            'department_id' => $department->id,
            'name' => 'Road Repair',
            'description' => 'Issues related to road damage.',
            'created_by' => $user->id,
        ]);
    }

    public function test_category_creation_requires_department_id(): void
    {
        $user = User::factory()->superadmin()->create();

        $response = $this->actingAs($user)->post('/complaint-categories', [
            'name' => 'No Department',
        ]);

        $response->assertSessionHasErrors('department_id');
    }

    public function test_category_creation_requires_valid_department(): void
    {
        $user = User::factory()->superadmin()->create();

        $response = $this->actingAs($user)->post('/complaint-categories', [
            'department_id' => 999,
            'name' => 'Invalid Dept',
        ]);

        $response->assertSessionHasErrors('department_id');
    }

    public function test_category_creation_requires_name(): void
    {
        $user = User::factory()->superadmin()->create();
        $department = Department::factory()->create();

        $response = $this->actingAs($user)->post('/complaint-categories', [
            'department_id' => $department->id,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_category_can_be_updated(): void
    {
        $user = User::factory()->superadmin()->create();
        $department = Department::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $response = $this->actingAs($user)->put("/complaint-categories/{$category->id}", [
            'department_id' => $department->id,
            'name' => 'Updated Category',
            'description' => 'Updated description.',
        ]);

        $response->assertRedirect(route('complaint-categories.index'));
        $response->assertSessionHas('success', 'Complaint category updated successfully.');

        $this->assertDatabaseHas('complaint_categories', [
            'id' => $category->id,
            'department_id' => $department->id,
            'name' => 'Updated Category',
            'description' => 'Updated description.',
            'updated_by' => $user->id,
        ]);
    }

    public function test_category_can_be_deleted(): void
    {
        $user = User::factory()->superadmin()->create();
        $category = ComplaintCategory::factory()->create();

        $response = $this->actingAs($user)->delete("/complaint-categories/{$category->id}");

        $response->assertRedirect(route('complaint-categories.index'));
        $response->assertSessionHas('success', 'Complaint category deleted successfully.');

        $this->assertDatabaseHas('complaint_categories', ['id' => $category->id]);
        $this->assertNotNull($category->fresh()->deleted_at);
    }

    public function test_unauthenticated_user_cannot_access_categories(): void
    {
        $response = $this->get('/complaint-categories');
        $response->assertRedirect(route('login'));
    }
}
