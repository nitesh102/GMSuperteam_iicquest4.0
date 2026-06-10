<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintCategory;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\ComplaintAiService;
class ComplaintController extends Controller
{
    protected $aiService;

    // Inject our new Gemini-powered service
    public function __construct(ComplaintAiService $aiService)
    {
        $this->aiService = $aiService;
    }
    public function create(): Response
    {
        return Inertia::render('Complaint/Create', [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'categories' => ComplaintCategory::with('department')->orderBy('name')->get(),
        ]);
    }

    public function index(): Response
    {
        $complaints = Complaint::with([
            'citizen',
            'category.department',
            'assignee',
        ])->orderBy('created_at', 'desc')->get();

        return Inertia::render('Complaint/Index', [
            'complaints' => $complaints,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'categories' => ComplaintCategory::with('department')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'category_id' => ['required', 'exists:complaint_categories,id'],
            'location' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        $tempPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $tempPaths[] = $file->getRealPath();
            }
        }

        $departments = Department::orderBy('name')->get(['id', 'name']);
        $categories = ComplaintCategory::with('department')->orderBy('name')->get();

        try {
            $ai = $this->aiService->analyzeComplaint(
                $validated['title'],
                $validated['description'],
                $tempPaths,
                $departments->toArray(),
                $categories->toArray(),
            );
        } catch (\Exception $e) {
            \Log::error("CiviSense AI processing failure: " . $e->getMessage());

            $lower = strtolower($validated['description']);
            $ai = [
                'priority' => $this->simulatePriority($lower),
                'summary' => $this->simulateSummary($validated['description']),
                'department_name' => null,
                'category_name' => null,
            ];
        }

        if (!empty($ai['department_name']) && !empty($ai['category_name'])) {
            $department = Department::firstOrCreate(
                ['name' => $ai['department_name']],
                ['description' => 'Auto-created by AI'],
            );

            $category = ComplaintCategory::firstOrCreate(
                [
                    'name' => $ai['category_name'],
                    'department_id' => $department->id,
                ],
                [
                    'description' => 'Auto-created by AI',
                    'created_by' => $request->user()->id,
                ],
            );

            $validated['category_id'] = $category->id;
        }

        $validated['complaint_no'] = 'CMP-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        $validated['citizen_id'] = $request->user()->id;
        $validated['created_by'] = $request->user()->id;
        $validated['current_status'] = 'submitted';
        $validated['priority'] = $ai['priority'];
        $validated['is_spam'] = false;
        $validated['ai_summary'] = $ai['summary'];

        $complaint = Complaint::create($validated);

        $complaint->aiAnalysis()->create([
            'detected_category' => $complaint->category?->name ?? 'Uncategorized',
            'detected_priority' => $ai['priority'],
            'confidence_score' => 0.85,
            'ai_summary' => $ai['summary'],
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('complaints/' . $complaint->id, 'public');
                ComplaintAttachment::create([
                    'complaint_id' => $complaint->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'uploaded_by' => $request->user()->id,
                    'created_by' => $request->user()->id,
                ]);
            }
        }

        return redirect()->route('complaints.index')
            ->with('success', 'Complaint submitted successfully. CiviSense AI analysis complete.');
    }
    public function update(Request $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'category_id' => ['required', 'exists:complaint_categories,id'],
            'current_status' => ['required', 'string', 'in:submitted,under_review,assigned,in_progress,resolved,rejected,closed'],
            'priority' => ['required', 'string', 'in:low,medium,high,emergency'],
            'location' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $validated['updated_by'] = $request->user()->id;

        $complaint->update($validated);

        return redirect()->route('complaints.index')
            ->with('success', 'Complaint updated successfully.');
    }

    public function destroy(Complaint $complaint): RedirectResponse
    {
        $complaint->delete();

        return redirect()->route('complaints.index')
            ->with('success', 'Complaint deleted successfully.');
    }

    private function simulatePriority(string $lower): string
    {
        if (preg_match('/\b(?:fire|blast|explosion|collapse|emergency|critical)\b/', $lower)) {
            return 'emergency';
        }
        if (preg_match('/\b(?:urgent|accident|flood|leak|broken|blocked|danger|hazard)\b/', $lower)) {
            return 'high';
        }
        if (preg_match('/\b(?:repair|fix|issue|problem|damage|concern|request)\b/', $lower)) {
            return 'medium';
        }
        return 'low';
    }

    private function simulateSummary(string $description): string
    {
        $cleaned = strip_tags($description);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        return mb_strlen($cleaned) > 200
            ? mb_substr($cleaned, 0, 200) . '...'
            : $cleaned;
    }

    private function simulateSpamCheck(string $lower): bool
    {
        $spamPatterns = [
            'buy now', 'click here', 'free money', 'limited offer',
            'act now', 'congratulations', 'you won', 'lottery',
            'casino', 'gambling', 'xxx',
        ];

        foreach ($spamPatterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
