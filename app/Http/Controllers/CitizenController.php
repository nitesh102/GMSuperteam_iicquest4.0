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

class CitizenController extends Controller
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

    public function dashboard(): Response
    {
        $userId = request()->user()->id;

        $complaints = Complaint::where('citizen_id', $userId)
            ->with(['category.department', 'department'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Citizen/Dashboard', [
            'stats' => [
                'total'      => $complaints->count(),
                'pending'    => $complaints->whereIn('current_status', ['submitted', 'under_review'])->count(),
                'inProgress' => $complaints->whereIn('current_status', ['assigned', 'in_progress'])->count(),
                'resolved'   => $complaints->where('current_status', 'resolved')->count(),
            ],
            'recentComplaints' => $complaints->take(5)->values(),
        ]);
    }

    public function index(): Response
    {
        $complaints = Complaint::where('citizen_id', request()->user()->id)
            ->with([
                'category.department',
                'department',
                'attachments',
                'tracks.changer',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Citizen/Index', [
            'complaints'  => $complaints,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'categories'  => ComplaintCategory::with('department')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Citizen/Create', [
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

        $validated['department_id'] = ComplaintCategory::where('id', $validated['category_id'])->value('department_id');

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

        return redirect()->route('citizen.complaints.index')
            ->with('success', 'Complaint submitted successfully. CiviSense AI analysis complete.');
    }

    public function show(Complaint $complaint): Response
    {
        if ($complaint->citizen_id !== request()->user()->id) {
            abort(403, 'You can only view your own complaints.');
        }

        $complaint->load([
            'category.department',
            'department',
            'attachments',
            'tracks.changer',
            'aiAnalysis',
        ]);

        return Inertia::render('Citizen/Show', [
            'complaint'   => $complaint,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'categories'  => ComplaintCategory::with('department')->orderBy('name')->get(),
        ]);
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

        $fillerWords = [
            'hello', 'hi', 'hey', 'howdy', 'sup', 'yo', 'hola', 'namaste', 'greetings', 'salut',
            'bye', 'goodbye', 'cya', 'later', 'adios', 'tata', 'farewell',
            'yes', 'no', 'yep', 'nope', 'yeah', 'nah', 'ok', 'okay', 'sure', 'alright',
            'test', 'testing', 'tested', 'check', 'checking', 'checked',
            'try', 'trying', 'tried', 'demo', 'dummy', 'sample', 'fake', 'trial', 'temp', 'temporary',
            'asdf', 'qwerty', 'zxcv', 'qazwsx', 'asd', 'qwe', 'zxc',
            'abcd', 'abcde', 'efgh', 'ijkl', 'mnop', 'qrst', 'uvwx', 'wxyz',
            'aaaa', 'bbbb', 'cccc', 'dddd', 'eeee', 'ffff', 'gggg', 'hhhh',
            'aaa', 'bbb', 'ccc', 'ddd', 'eee', 'fff', 'ggg', 'hhh', 'iii', 'jjj', 'kkk', 'lll',
            'abc', 'xyz', 'xyzabc', 'abcxyz',
            'lol', 'omg', 'wtf', 'lmao', 'rofl', 'lmfao', 'brb', 'afk',
            'idk', 'tbh', 'imho', 'imo', 'smh', 'fyi', 'btw', 'ttyl', 'haha', 'hehe', 'hihi',
            'wow', 'great', 'nice', 'good', 'bad', 'cool', 'awesome', 'amazing',
            'terrible', 'horrible', 'please', 'sorry', 'thanks', 'thank', 'thx', 'fine',
            'interesting', 'random', 'whatever', 'nothing', 'none', 'na', 'nil', 'null',
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

        $genuinePattern = '/\b('
            . 'road|roads|pothole|potholes|speed\s*bump|divider|median|footover|overbridge|underbridge|'
            . 'pedestrian|crossing|zebra|flyover|highway|expressway|bypass|service\s*road|mud\s*road|'
            . 'bridge|underpass|junction|roundabout|lane|avenue|street|streets|footpath|path|pavement|'
            . 'sidewalk|culvert|manhole|parking|bus\s*stand|auto\s*stand|taxi\s*stand|'
            . 'water|drinking\s*water|tap|pipeline|pipe|pipes|waterline|leakage|seepage|'
            . 'flood|flooding|waterlogging|puddle|stagnant|drain|drainage|gutter|sewage|sewer|'
            . 'borewell|tanker|reservoir|canal|ditch|trench|clogged|overflow|blockage|'
            . 'contamination|dirty\s*water|water\s*supply|shortage|outage|'
            . 'electricity|electric|power|blackout|load\s*shedding|transformer|substation|'
            . 'short\s*circuit|sparking|wire|wiring|pylon|pole|post|meter|'
            . 'garbage|waste|trash|rubbish|debris|litter|sewage|dump|dumping|landfill|'
            . 'open\s*defecation|urinal|toilet|dustbin|bins|collection|segregation|compost|'
            . 'cleanliness|sanitation|hygiene|slaughterhouse|'
            . 'light|lamp|streetlight|street\s*light|signal|traffic\s*signal|'
            . 'park|garden|playground|ground|open\s*space|walkway|bench|public\s*property|'
            . 'building|construction|demolition|structure|wall|compound|boundary|encroachment|'
            . 'unauthorized|illegal|unsafe|dangerous|premises|demolish|collapse|crack|broken|'
            . 'tree|branch|fallen|overgrown|trimming|pruning|pollution|dust|smoke|burning|'
            . 'noise|odor|smell|stench|fumes|landslide|erosion|'
            . 'stray|dog|cow|cattle|animal|pest|rat|rodent|mosquito|dengue|malaria|insect|'
            . 'danger|hazard|risk|accident|injury|death|fire|explosion|emergency|'
            . 'live\s*wire|slippery|visibility|missing|'
            . 'hospital|dispensary|clinic|ambulance|school|college|teacher|education|'
            . 'library|ration|pension|welfare|certificate|license|permit|fee|tax|bill|penalty|'
            . 'complaint|issue|problem|concern|request|fix|repair|replace|remove|clean|'
            . 'damage|damaged|blocked|missing|broken|not\s*working|need|needs|required|'
            . 'maintenance|supply|collection|infrastructure|civic|municipal|government|'
            . 'authority|department|office|grievance|vandalism|report|'
            . 'area|zone|ward|sector|locality|colony|neighborhood|society|town|village|'
            . 'district|near|beside|opposite|station|market|'
            . ')\b/i';

        $hasGenuineContent = preg_match($genuinePattern, $title) || preg_match($genuinePattern, $desc);

        if (!$hasGenuineContent) {
            if (mb_strlen($desc) < 120) {
                return true;
            }
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

        $metaCommentaryPattern = '/('
            . 'just\s+test(ing)?|'
            . 'this\s+(is\s+)?(a\s+|just\s+(a\s+)?)?(test|check|dummy|fake|sample)|'
            . 'i\s+am\s+(just\s+)?(testing|checking)|'
            . 'i\'m\s+(just\s+)?(testing|checking)|'
            . 'testing\s+this|checking\s+this|'
            . 'checking\s+(spam|filter)|testing\s+(spam|filter)|bypass\s+(spam|filter)|'
            . 'does\s+this\s+(pass|work|go\s+through|bypass)|'
            . 'will\s+this\s+(pass|work|go\s+through)|'
            . 'is\s+this\s+spam|this\s+is\s+spam|mark(ed)?\s+as\s+spam|'
            . 'how\s+(did|does|can)\s+this\s+(pass|work|go)|'
            . 'why\s+(did|does|has|is)\s+this\s+(pass|go|work|allow|accept)|'
            . 'went\s+through\s+(the\s+)?(spam|filter)|'
            . 'passed\s+(the\s+)?(spam|filter)|'
            . 'how\s+did\s+this\s+pass|how\s+is\s+this\s+passing|'
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
