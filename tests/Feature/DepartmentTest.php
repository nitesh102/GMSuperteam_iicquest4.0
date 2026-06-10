<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        Department::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/departments');

        $response->assertOk();
    }

    public function test_department_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/departments', [
            'name' => 'Health Department',
            'description' => 'Responsible for public health.',
        ]);

        $response->assertRedirect(route('departments.index'));
        $response->assertSessionHas('success', 'Department created successfully.');

        $this->assertDatabaseHas('departments', [
            'name' => 'Health Department',
            'description' => 'Responsible for public health.',
        ]);
    }

    public function test_department_creation_requires_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/departments', [
            'description' => 'Missing name.',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_department_creation_name_cannot_exceed_255_chars(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/departments', [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_department_can_be_updated(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->create();

        $response = $this->actingAs($user)->put("/departments/{$department->id}", [
            'name' => 'Updated Department',
            'description' => 'Updated description.',
        ]);

        $response->assertRedirect(route('departments.index'));
        $response->assertSessionHas('success', 'Department updated successfully.');

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'Updated Department',
            'description' => 'Updated description.',
        ]);
    }

    public function test_department_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->create();

        $response = $this->actingAs($user)->delete("/departments/{$department->id}");

        $response->assertRedirect(route('departments.index'));
        $response->assertSessionHas('success', 'Department deleted successfully.');

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    public function test_unauthenticated_user_cannot_access_departments(): void
    {
        $response = $this->get('/departments');
        $response->assertRedirect(route('login'));
    }
}
