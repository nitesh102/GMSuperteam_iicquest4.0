<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintCategory;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComplaintTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        Complaint::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/complaints');

        $response->assertOk();
    }

    public function test_complaint_can_be_created(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $response = $this->actingAs($user)->post('/complaints', [
            'title' => 'Pothole on Main Street',
            'description' => 'There is a large pothole causing traffic issues.',
            'category_id' => $category->id,
            'location' => 'Main Street, Downtown',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

        $response->assertRedirect(route('complaints.index'));
        $response->assertSessionHas('success', 'Complaint submitted successfully. AI analysis complete.');

        $this->assertDatabaseHas('complaints', [
            'title' => 'Pothole on Main Street',
            'citizen_id' => $user->id,
            'category_id' => $category->id,
            'location' => 'Main Street, Downtown',
            'current_status' => 'submitted',
            'created_by' => $user->id,
        ]);
    }

    public function test_complaint_creation_requires_title(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $response = $this->actingAs($user)->post('/complaints', [
            'description' => 'Missing title.',
            'category_id' => $category->id,
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_complaint_creation_requires_description(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $response = $this->actingAs($user)->post('/complaints', [
            'title' => 'Test',
            'category_id' => $category->id,
        ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_complaint_creation_requires_category_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/complaints', [
            'title' => 'Test',
            'description' => 'Test description.',
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    public function test_complaint_creation_requires_valid_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/complaints', [
            'title' => 'Test',
            'description' => 'Test description.',
            'category_id' => 999,
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    public function test_complaint_generates_complaint_no(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $this->actingAs($user)->post('/complaints', [
            'title' => 'Test Complaint',
            'description' => 'Test description.',
            'category_id' => $category->id,
        ]);

        $complaint = Complaint::first();
        $this->assertNotNull($complaint);
        $this->assertStringStartsWith('CMP-' . now()->format('Ymd') . '-', $complaint->complaint_no);
    }

    public function test_complaint_can_be_updated(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();
        $complaint = Complaint::factory()->create();

        $response = $this->actingAs($user)->put("/complaints/{$complaint->id}", [
            'title' => 'Updated Complaint',
            'description' => 'Updated description.',
            'category_id' => $category->id,
            'current_status' => 'in_progress',
            'priority' => 'high',
            'location' => 'Updated Location',
        ]);

        $response->assertRedirect(route('complaints.index'));
        $response->assertSessionHas('success', 'Complaint updated successfully.');

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'title' => 'Updated Complaint',
            'description' => 'Updated description.',
            'current_status' => 'in_progress',
            'priority' => 'high',
            'location' => 'Updated Location',
            'updated_by' => $user->id,
        ]);
    }

    public function test_complaint_update_requires_valid_status(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();
        $complaint = Complaint::factory()->create();

        $response = $this->actingAs($user)->put("/complaints/{$complaint->id}", [
            'title' => 'Test',
            'description' => 'Test.',
            'category_id' => $category->id,
            'current_status' => 'invalid_status',
            'priority' => 'low',
        ]);

        $response->assertSessionHasErrors('current_status');
    }

    public function test_complaint_update_requires_valid_priority(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();
        $complaint = Complaint::factory()->create();

        $response = $this->actingAs($user)->put("/complaints/{$complaint->id}", [
            'title' => 'Test',
            'description' => 'Test.',
            'category_id' => $category->id,
            'current_status' => 'submitted',
            'priority' => 'invalid',
        ]);

        $response->assertSessionHasErrors('priority');
    }

    public function test_complaint_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $complaint = Complaint::factory()->create();

        $response = $this->actingAs($user)->delete("/complaints/{$complaint->id}");

        $response->assertRedirect(route('complaints.index'));
        $response->assertSessionHas('success', 'Complaint deleted successfully.');

        $this->assertDatabaseHas('complaints', ['id' => $complaint->id]);
        $this->assertNotNull($complaint->fresh()->deleted_at);
    }

    public function test_unauthenticated_user_cannot_access_complaints(): void
    {
        $response = $this->get('/complaints');
        $response->assertRedirect(route('login'));
    }

    // --- AI Simulation Tests ---

    public function test_ai_simulates_emergency_priority_for_fire_keywords(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $this->actingAs($user)->post('/complaints', [
            'title' => 'Fire Report',
            'description' => 'Fire reported at the downtown market! Emergency response needed.',
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('complaints', [
            'priority' => 'emergency',
        ]);
    }

    public function test_ai_simulates_high_priority_for_urgent_keywords(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $this->actingAs($user)->post('/complaints', [
            'title' => 'Urgent Water Leak',
            'description' => 'Urgent: water leak reported on Main Street causing flooding.',
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('complaints', [
            'priority' => 'high',
        ]);
    }

    public function test_ai_simulates_medium_priority_for_repair_keywords(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $this->actingAs($user)->post('/complaints', [
            'title' => 'Street Light Repair',
            'description' => 'Repair request for the street light near the park.',
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('complaints', [
            'priority' => 'medium',
        ]);
    }

    public function test_ai_simulates_low_priority_for_generic_description(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $this->actingAs($user)->post('/complaints', [
            'title' => 'General Inquiry',
            'description' => 'I would like to know more about the recycling program.',
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('complaints', [
            'priority' => 'low',
        ]);
    }

    public function test_ai_detects_spam_content(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $this->actingAs($user)->post('/complaints', [
            'title' => 'Special Offer',
            'description' => 'Buy now and get free money! Limited offer.',
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('complaints', [
            'is_spam' => true,
        ]);
    }

    public function test_ai_marks_non_spam_as_false(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $this->actingAs($user)->post('/complaints', [
            'title' => 'Legitimate Complaint',
            'description' => 'There is a broken bench in the park that needs repair.',
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('complaints', [
            'is_spam' => false,
        ]);
    }

    public function test_ai_generates_summary_truncated_to_200_chars(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();
        $longText = str_repeat('Lorem ipsum dolor sit amet ', 20); // ~400 chars

        $this->actingAs($user)->post('/complaints', [
            'title' => 'Long Complaint',
            'description' => $longText,
            'category_id' => $category->id,
        ]);

        $complaint = Complaint::first();
        $this->assertNotNull($complaint);
        $this->assertStringEndsWith('...', $complaint->ai_summary);
        $this->assertLessThanOrEqual(203, strlen($complaint->ai_summary));
    }

    public function test_ai_generates_summary_without_truncation_for_short_text(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();
        $shortText = 'Short complaint description.';

        $this->actingAs($user)->post('/complaints', [
            'title' => 'Short Complaint',
            'description' => $shortText,
            'category_id' => $category->id,
        ]);

        $complaint = Complaint::first();
        $this->assertNotNull($complaint);
        $this->assertEquals($shortText, $complaint->ai_summary);
    }

    public function test_ai_handles_html_tags_in_description(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $this->actingAs($user)->post('/complaints', [
            'title' => 'HTML Test',
            'description' => '<script>alert("xss")</script>Clean text after script.',
            'category_id' => $category->id,
        ]);

        $complaint = Complaint::first();
        $this->assertNotNull($complaint);
        $this->assertStringNotContainsString('<script>', $complaint->ai_summary);
        $this->assertStringContainsString('Clean text after script.', $complaint->ai_summary);
    }

    public function test_ai_detects_multiple_spam_patterns(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $patterns = ['click here', 'free money', 'limited offer', 'act now', 'congratulations', 'you won', 'lottery', 'casino', 'gambling', 'xxx'];

        foreach ($patterns as $pattern) {
            $this->actingAs($user)->post('/complaints', [
                'title' => 'Spam Test',
                'description' => "This complaint contains {$pattern} and should be flagged.",
                'category_id' => $category->id,
            ]);

            $complaint = Complaint::where('is_spam', true)->latest()->first();
            $this->assertNotNull($complaint, "Failed for pattern: {$pattern}");
            $this->assertTrue($complaint->is_spam);
        }
    }

    public function test_complaint_index_returns_complaints_with_relations(): void
    {
        $user = User::factory()->create();
        Complaint::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/complaints');

        $response->assertOk();
    }

    public function test_complaint_creation_sets_default_status_to_submitted(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $this->actingAs($user)->post('/complaints', [
            'title' => 'Status Test',
            'description' => 'Testing default status.',
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('complaints', [
            'current_status' => 'submitted',
        ]);
    }

    public function test_complaint_creation_with_optional_fields_null(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $this->actingAs($user)->post('/complaints', [
            'title' => 'Minimal Complaint',
            'description' => 'Just a test.',
            'category_id' => $category->id,
        ]);

        $complaint = Complaint::first();
        $this->assertNull($complaint->location);
        $this->assertNull($complaint->latitude);
        $this->assertNull($complaint->longitude);
    }

    public function test_create_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/complaints/create');

        $response->assertOk();
    }

    public function test_create_page_redirects_guest_to_login(): void
    {
        $response = $this->get('/complaints/create');

        $response->assertRedirect(route('login'));
    }

    public function test_complaint_can_be_created_with_image_attachments(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $file = UploadedFile::fake()->image('pothole.jpg', 200, 200);

        $response = $this->actingAs($user)->post('/complaints', [
            'title' => 'Test with Photo',
            'description' => 'Complaint with an image attachment.',
            'category_id' => $category->id,
            'attachments' => [$file],
        ]);

        $response->assertRedirect(route('complaints.index'));

        $complaint = Complaint::where('title', 'Test with Photo')->first();
        $this->assertNotNull($complaint);
        $this->assertCount(1, $complaint->attachments);

        $attachment = $complaint->attachments->first();
        $this->assertEquals('image/jpeg', $attachment->file_type);
        $this->assertEquals('pothole.jpg', $attachment->file_name);
        Storage::disk('public')->assertExists($attachment->file_path);
    }

    public function test_complaint_attachments_limited_to_5_images(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $files = [];
        for ($i = 0; $i < 6; $i++) {
            $files[] = UploadedFile::fake()->image("photo_{$i}.jpg", 100, 100);
        }

        $response = $this->actingAs($user)->post('/complaints', [
            'title' => 'Too Many Photos',
            'description' => 'Testing attachment limit.',
            'category_id' => $category->id,
            'attachments' => $files,
        ]);

        $response->assertSessionHasErrors('attachments');
    }

    public function test_complaint_attachment_must_be_an_image(): void
    {
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)->post('/complaints', [
            'title' => 'Invalid File',
            'description' => 'Testing file type validation.',
            'category_id' => $category->id,
            'attachments' => [$file],
        ]);

        $response->assertSessionHasErrors('attachments.0');
    }

    public function test_complaint_attachments_persist_after_soft_delete(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $category = ComplaintCategory::factory()->create();

        $file = UploadedFile::fake()->image('soft-delete-test.jpg');
        $this->actingAs($user)->post('/complaints', [
            'title' => 'Soft Delete Test',
            'description' => 'Testing attachment persistence.',
            'category_id' => $category->id,
            'attachments' => [$file],
        ]);

        $complaint = Complaint::where('title', 'Soft Delete Test')->first();
        $attachmentId = $complaint->attachments->first()->id;

        $this->actingAs($user)->delete("/complaints/{$complaint->id}");

        $this->assertSoftDeleted($complaint);
        $this->assertDatabaseHas('complaint_attachments', ['id' => $attachmentId]);
    }
}
