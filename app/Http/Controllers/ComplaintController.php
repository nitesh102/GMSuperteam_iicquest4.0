<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintCategory;
use App\Models\ComplaintTrack;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\ComplaintAiService;

class ComplaintController extends Controller
{
    private const STATUS_TRANSITIONS = [
        'submitted'    => ['under_review', 'rejected'],
        'under_review' => ['assigned', 'rejected', 'submitted'],
        'assigned'     => ['in_progress', 'under_review', 'rejected'],
        'in_progress'  => ['resolved', 'rejected', 'assigned'],
        'resolved'     => ['closed', 'in_progress'],
        'rejected'     => ['submitted'],
        'closed'       => [],
    ];

    protected ComplaintAiService $aiService;

    public function __construct(ComplaintAiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function create(): Response
    {
        return Inertia::render('Complaint/Create');
    }

    public function index(): Response
    {
        $complaints = Complaint::with([
            'citizen',
            'category.department',
            'assignee',
            'attachments',
            'tracks.changer',
        ])->orderBy('created_at', 'desc')->get();

        return Inertia::render('Complaint/Index', [
            'complaints'  => $complaints,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'categories'  => ComplaintCategory::with('department')->orderBy('name')->get(),
            'users'       => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function getGroupedByLocation(): Response
    {
        $complaints = Complaint::with([
            'citizen',
            'category.department',
            'assignee',
        ])
        ->whereNotNull('location')
        ->where('location', '!=', '')
        ->orderBy('created_at', 'desc')
        ->get();

        $groupedComplaints = $complaints
            ->groupBy(function ($complaint) {
                $location = strtolower(trim($complaint->location));
                $parts = explode(',', $location);
                return trim($parts[0]);
            })
            ->map(function ($group, $location) {
                return [
                    'location' => ucfirst($location),
                    'count' => $group->count(),
                    'complaints' => $group->take(5)->map(function ($complaint) {
                        return [
                            'id' => $complaint->id,
                            'complaint_no' => $complaint->complaint_no,
                            'title' => $complaint->title,
                            'description' => $complaint->description,
                            'location' => $complaint->location,
                            'priority' => $complaint->priority,
                            'current_status' => $complaint->current_status,
                            'created_at' => $complaint->created_at->format('Y-m-d H:i'),
                            'citizen' => $complaint->citizen?->name,
                            'category' => $complaint->category?->name,
                            'department' => $complaint->category?->department?->name,
                        ];
                    }),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $total  = Complaint::count();
        $open   = Complaint::whereIn('current_status', ['submitted', 'under_review'])->count();
        $inProg = Complaint::where('current_status', 'in_progress')->count();
        $resolved = Complaint::where('current_status', 'resolved')->count();

        return Inertia::render('Dashboard', [
            'groupedComplaints' => $groupedComplaints,
            'stats' => [
                'total'      => $total,
                'pending'    => $open,
                'inProgress' => $inProg,
                'resolved'   => $resolved,
            ],
        ]);
    }

    public function show(Complaint $complaint): Response
    {
        $complaint->load([
            'citizen',
            'category.department',
            'attachments',
            'tracks.changer',
            'aiAnalysis',
        ]);

        return Inertia::render('Complaint/Show', [
            'complaint'   => $complaint,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'categories'  => ComplaintCategory::with('department')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string', 'max:10000'],
            'category_id'   => ['nullable', 'exists:complaint_categories,id'],
            'location'      => ['nullable', 'string', 'max:500'],
            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
            'attachments'   => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        $tempPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $tempPaths[] = $file->getRealPath();
            }
        }

        // Phase 1: deterministic local filter — always runs first, never skipped
        if ($this->isObviousSpam($validated['title'], $validated['description'])) {
            throw ValidationException::withMessages(['spam' => 'This submission does not appear to be a genuine civic complaint. Please describe a real issue.']);
        }

        $departments = Department::orderBy('name')->get(['id', 'name']);
        $categories  = ComplaintCategory::with('department')->orderBy('name')->get();

        try {
            $ai = $this->aiService->analyzeComplaint(
                $validated['title'],
                $validated['description'],
                $tempPaths,
                $departments->toArray(),
                $categories->toArray(),
            );

            if (!empty($ai['is_spam'])) {
                $reason = !empty($ai['spam_reason'])
                    ? $ai['spam_reason']
                    : 'This submission was flagged as spam by CiviSense AI.';
                throw ValidationException::withMessages(['spam' => $reason]);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::warning("CiviSense AI failed: " . $e->getMessage());

            $lower = strtolower($validated['description']);
            $ai = [
                'priority'        => $this->simulatePriority($lower),
                'summary'         => $this->simulateSummary($validated['description']),
                'department_name' => null,
                'category_name'   => null,
            ];
        }

        // Preserve user-selected category when provided; otherwise use AI determination
        if (empty($validated['category_id'])) {
            $deptName     = !empty($ai['department_name']) ? $ai['department_name'] : 'General';
            $categoryName = !empty($ai['category_name'])  ? $ai['category_name']  : 'General Complaint';

            $department = Department::firstOrCreate(
                ['name' => $deptName],
                ['description' => 'Auto-created by AI'],
            );

            $category = ComplaintCategory::firstOrCreate(
                ['name' => $categoryName, 'department_id' => $department->id],
                ['description' => 'Auto-created by AI', 'created_by' => $request->user()->id],
            );

            $validated['category_id'] = $category->id;
        }

        $validated['complaint_no']     = 'CMP-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        $validated['citizen_id']       = $request->user()->id;
        $validated['created_by']       = $request->user()->id;
        $validated['current_status']   = 'submitted';
        $validated['priority']         = $ai['priority'];
        $validated['is_spam']          = false;
        $validated['ai_summary']       = $ai['summary'];
        $validated['escalation_level'] = 0;
        $validated['due_at']           = $this->calculateDueAt($ai['priority']);

        $complaint = Complaint::create($validated);

        $complaint->aiAnalysis()->create([
            'detected_category' => $complaint->category?->name ?? 'Uncategorized',
            'detected_priority' => $ai['priority'],
            'confidence_score'  => 0.85,
            'ai_summary'        => $ai['summary'],
            'created_by'        => $request->user()->id,
            'updated_by'        => $request->user()->id,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('complaints/' . $complaint->id, 'public');
                ComplaintAttachment::create([
                    'complaint_id' => $complaint->id,
                    'file_path'    => $path,
                    'file_name'    => $file->getClientOriginalName(),
                    'file_type'    => $file->getMimeType(),
                    'uploaded_by'  => $request->user()->id,
                    'created_by'   => $request->user()->id,
                ]);
            }
        }

        return redirect()->route('complaints.index')
            ->with('success', 'Complaint submitted successfully. CiviSense AI analysis complete.');
    }

    public function update(Request $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string', 'max:10000'],
            'category_id'      => ['required', 'exists:complaint_categories,id'],
            'current_status'   => ['required', 'string', 'in:submitted,under_review,assigned,in_progress,resolved,rejected,closed'],
            'priority'         => ['required', 'string', 'in:low,medium,high,emergency'],
            'location'         => ['nullable', 'string', 'max:500'],
            'latitude'         => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'        => ['nullable', 'numeric', 'between:-180,180'],
            'assigned_to'      => ['nullable', 'exists:users,id'],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
            'track_notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $complaint->current_status;
        $newStatus = $validated['current_status'];

        if ($oldStatus !== $newStatus) {
            $allowed = self::STATUS_TRANSITIONS[$oldStatus] ?? [];
            if (!in_array($newStatus, $allowed)) {
                throw ValidationException::withMessages([
                    'current_status' => "Cannot transition from \"{$oldStatus}\" to \"{$newStatus}\".",
                ]);
            }
        }

        $updateData = [
            'title'            => $validated['title'],
            'description'      => $validated['description'],
            'category_id'      => $validated['category_id'],
            'current_status'   => $newStatus,
            'priority'         => $validated['priority'],
            'location'         => $validated['location'] ?? null,
            'latitude'         => $validated['latitude'] ?? null,
            'longitude'        => $validated['longitude'] ?? null,
            'assigned_to'      => $validated['assigned_to'] ?? null,
            'resolution_notes' => $validated['resolution_notes'] ?? null,
            'updated_by'       => $request->user()->id,
        ];

        if ($oldStatus !== 'resolved' && $newStatus === 'resolved') {
            $updateData['resolved_at'] = now();
        } elseif ($oldStatus === 'resolved' && $newStatus !== 'resolved') {
            $updateData['resolved_at'] = null;
        }

        $complaint->update($updateData);

        if ($oldStatus !== $newStatus) {
            ComplaintTrack::create([
                'complaint_id' => $complaint->id,
                'old_status'   => $oldStatus,
                'new_status'   => $newStatus,
                'changed_by'   => $request->user()->id,
                'notes'        => $validated['track_notes'] ?? null,
            ]);
        }

        return redirect()->route('complaints.index')
            ->with('success', 'Complaint updated successfully.');
    }

    public function destroy(Complaint $complaint): RedirectResponse
    {
        $complaint->delete();

        return redirect()->route('complaints.index')
            ->with('success', 'Complaint deleted successfully.');
    }

    private function calculateDueAt(string $priority): \Carbon\Carbon
    {
        return match ($priority) {
            'emergency' => now()->addHours(4),
            'high'      => now()->addHours(24),
            'medium'    => now()->addHours(72),
            default     => now()->addDays(7),
        };
    }

    private function isObviousSpam(string $title, string $description): bool
    {
        $title = trim($title);
        $desc  = trim($description);

        // ── Rule 1: title made entirely of filler / greeting / test / stop words ──
        $fillerWords = [
            // greetings & farewells
            'hello', 'hi', 'hey', 'howdy', 'sup', 'yo', 'hola', 'namaste', 'greetings', 'salut',
            'bye', 'goodbye', 'cya', 'later', 'adios', 'tata', 'farewell',
            // affirmations / negations
            'yes', 'no', 'yep', 'nope', 'yeah', 'nah', 'ok', 'okay', 'sure', 'alright',
            // test / dummy
            'test', 'testing', 'tested', 'check', 'checking', 'checked',
            'try', 'trying', 'tried', 'demo', 'dummy', 'sample', 'fake', 'trial', 'temp', 'temporary',
            // gibberish keyboard patterns
            'asdf', 'qwerty', 'zxcv', 'qazwsx', 'asd', 'qwe', 'zxc',
            'abcd', 'abcde', 'efgh', 'ijkl', 'mnop', 'qrst', 'uvwx', 'wxyz',
            'aaaa', 'bbbb', 'cccc', 'dddd', 'eeee', 'ffff', 'gggg', 'hhhh',
            'aaa', 'bbb', 'ccc', 'ddd', 'eee', 'fff', 'ggg', 'hhh', 'iii', 'jjj', 'kkk', 'lll',
            'abc', 'xyz', 'xyzabc', 'abcxyz',
            // informal / social media
            'lol', 'omg', 'wtf', 'lmao', 'rofl', 'lmfao', 'brb', 'afk',
            'idk', 'tbh', 'imho', 'imo', 'smh', 'fyi', 'btw', 'ttyl', 'haha', 'hehe', 'hihi',
            // common filler expressions
            'wow', 'great', 'nice', 'good', 'bad', 'cool', 'awesome', 'amazing',
            'terrible', 'horrible', 'please', 'sorry', 'thanks', 'thank', 'thx', 'fine',
            'interesting', 'random', 'whatever', 'nothing', 'none', 'na', 'nil', 'null',
            // articles, pronouns, conjunctions, question words (stop words)
            'a', 'an', 'the', 'this', 'that', 'these', 'those',
            'it', 'its', 'i', 'me', 'my', 'mine', 'we', 'us', 'our', 'you', 'your',
            'he', 'she', 'they', 'them', 'their', 'his', 'her',
            'and', 'or', 'but', 'so', 'yet', 'for', 'nor', 'just', 'only', 'even',
            'what', 'how', 'why', 'who', 'when', 'where', 'which', 'whose',
            'is', 'are', 'was', 'were', 'be', 'been', 'being', 'do', 'does', 'did',
            'here', 'there', 'now', 'then', 'any', 'some', 'many', 'new', 'old',
        ];
        $titleWords = preg_split('/[\s\d\W]+/', strtolower($title), -1, PREG_SPLIT_NO_EMPTY);
        if (!empty($titleWords) && count(array_diff($titleWords, $fillerWords)) === 0) {
            return true;
        }

        // ── Rule 2: check for genuine civic / problem / location keywords ──
        $genuinePattern = '/\b('
            // roads & transport
            . 'road|roads|pothole|potholes|speed\s*bump|divider|median|footover|overbridge|underbridge|'
            . 'pedestrian|crossing|zebra|flyover|highway|expressway|bypass|service\s*road|mud\s*road|'
            . 'bridge|underpass|junction|roundabout|lane|avenue|street|streets|footpath|path|pavement|'
            . 'sidewalk|culvert|manhole|parking|bus\s*stand|auto\s*stand|taxi\s*stand|'
            // water & drainage
            . 'water|drinking\s*water|tap|pipeline|pipe|pipes|waterline|leakage|seepage|'
            . 'flood|flooding|waterlogging|puddle|stagnant|drain|drainage|gutter|sewage|sewer|'
            . 'borewell|tanker|reservoir|canal|ditch|trench|clogged|overflow|blockage|'
            . 'contamination|dirty\s*water|water\s*supply|shortage|outage|'
            // electricity & utilities
            . 'electricity|electric|power|blackout|load\s*shedding|transformer|substation|'
            . 'short\s*circuit|sparking|wire|wiring|pylon|pole|post|meter|'
            // waste & sanitation
            . 'garbage|waste|trash|rubbish|debris|litter|sewage|dump|dumping|landfill|'
            . 'open\s*defecation|urinal|toilet|dustbin|bins|collection|segregation|compost|'
            . 'cleanliness|sanitation|hygiene|slaughterhouse|'
            // lighting & public infrastructure
            . 'light|lamp|streetlight|street\s*light|signal|traffic\s*signal|'
            // public spaces & parks
            . 'park|garden|playground|ground|open\s*space|walkway|bench|public\s*property|'
            // buildings & construction
            . 'building|construction|demolition|structure|wall|compound|boundary|encroachment|'
            . 'unauthorized|illegal|unsafe|dangerous|premises|demolish|collapse|crack|broken|'
            // trees & environment
            . 'tree|branch|fallen|overgrown|trimming|pruning|pollution|dust|smoke|burning|'
            . 'noise|odor|smell|stench|fumes|landslide|erosion|'
            // animals & pests
            . 'stray|dog|cow|cattle|animal|pest|rat|rodent|mosquito|dengue|malaria|insect|'
            // emergency & safety
            . 'danger|hazard|risk|accident|injury|death|fire|explosion|emergency|'
            . 'live\s*wire|slippery|visibility|missing|'
            // civic services
            . 'hospital|dispensary|clinic|ambulance|school|college|teacher|education|'
            . 'library|ration|pension|welfare|certificate|license|permit|fee|tax|bill|penalty|'
            // general complaint language
            . 'complaint|issue|problem|concern|request|fix|repair|replace|remove|clean|'
            . 'damage|damaged|blocked|missing|broken|not\s*working|need|needs|required|'
            . 'maintenance|supply|collection|infrastructure|civic|municipal|government|'
            . 'authority|department|office|grievance|vandalism|report|'
            // location words
            . 'area|zone|ward|sector|locality|colony|neighborhood|society|town|village|'
            . 'district|near|beside|opposite|station|market|'
            . ')\b/i';

        $hasGenuineContent = preg_match($genuinePattern, $title) || preg_match($genuinePattern, $desc);

        if (!$hasGenuineContent) {
            // No genuine civic content — reject if description is short
            if (mb_strlen($desc) < 120) {
                return true;
            }
            // Longer submissions with zero civic keywords: check for meta/test language
            $metaPattern = '/\b('
                . 'test|testing|demo|dummy|sample|fake|trial|'
                . 'check|checking|spam|filter|'
                . 'just\s+(a\s+)?test|does\s+this|how\s+(did|does)|'
                . 'passed|went\s+through|is\s+this\s+working|'
                . 'hello|hi\s+there|trying\s+out|random\s+text|ignore\s+this|'
                . 'lorem\s+ipsum|placeholder|filler'
                . ')\b/i';
            return (bool) preg_match($metaPattern, $desc);
        }

        // ── Rule 3: has civic content but is clearly a test / meta submission ──
        $metaCommentaryPattern = '/('
            // explicit test declarations
            . 'just\s+test(ing)?|'
            . 'this\s+(is\s+)?(a\s+|just\s+(a\s+)?)?(test|check|dummy|fake|sample)|'
            . 'i\s+am\s+(just\s+)?(testing|checking)|'
            . 'i\'m\s+(just\s+)?(testing|checking)|'
            . 'testing\s+this|checking\s+this|'
            // spam filter probing
            . 'checking\s+(spam|filter)|testing\s+(spam|filter)|bypass\s+(spam|filter)|'
            . 'does\s+this\s+(pass|work|go\s+through|bypass)|'
            . 'will\s+this\s+(pass|work|go\s+through)|'
            . 'is\s+this\s+spam|this\s+is\s+spam|mark(ed)?\s+as\s+spam|'
            . 'how\s+(did|does|can)\s+this\s+(pass|work|go)|'
            . 'why\s+(did|does|has|is)\s+this\s+(pass|go|work|allow|accept)|'
            . 'went\s+through\s+(the\s+)?(spam|filter)|'
            . 'passed\s+(the\s+)?(spam|filter)|'
            . 'how\s+did\s+this\s+pass|how\s+is\s+this\s+passing|'
            // explicit dismissals
            . 'ignore\s+this|please\s+ignore|delete\s+this|discard\s+this|'
            . 'not\s+a\s+real\s+complaint|fake\s+complaint|this\s+is\s+fake|'
            . 'random\s+text|filler\s+text|lorem\s+ipsum|placeholder'
            . ')/i';

        return (bool) preg_match($metaCommentaryPattern, $desc);
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
}
