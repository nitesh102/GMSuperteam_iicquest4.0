<?php

namespace App\Services;

use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\ResponseMimeType;
use Gemini\Laravel\Facades\Gemini;

class VoiceCommandService
{
  public function interpret(string $transcript, string $locale = 'en', ?string $currentPage = null, ?string $pageContext = null): array
  {
    $transcript = trim($transcript);

    if ($transcript === '') {
      return $this->fallback('empty', $locale);
    }

    $local = $this->matchLocalCommand($transcript, $locale);
    if ($local) {
      return $local;
    }

    $keyword = $this->matchKeywordFallback($transcript, $locale);
    if ($keyword) {
      return $keyword;
    }

    try {
      return $this->interpretWithGemini($transcript, $locale, $currentPage, $pageContext);
    } catch (\Throwable) {
      return $this->fallback($transcript, $locale);
    }
  }

  protected function matchLocalCommand(string $transcript, string $locale): ?array
  {
    $text = mb_strtolower(trim($transcript));

    // --- Create patterns (checked first to avoid overlap with navigation) ---
    if (preg_match('/(create|add|make|open|new|file|report)(\s+(a\s+)?)?(new\s+)?(department|departments|विभाग)/iu', $text)) {
      return $this->result('open_create', $locale, ['target' => 'department'], 'Opening create form.', 'सिर्जना फारम खोल्दै।');
    }
    if (preg_match('/(create|add|make|open|new)(\s+(a\s+)?)?(new\s+)?(category|categories|श्रेणी)/iu', $text)) {
      return $this->result('open_create', $locale, ['target' => 'category'], 'Opening create form.', 'सिर्जना फारम खोल्दै।');
    }
    if (preg_match('/(create|add|make|open|new|file|report)(\s+(a\s+)?)?(new\s+)?(complaint|complaints|गुनासो)/iu', $text)) {
      return $this->result('open_create', $locale, ['target' => 'complaint'], 'Opening create form.', 'सिर्जना फारम खोल्दै।');
    }

    // --- Navigation patterns (ordered specific → general) ---
    if (preg_match('/\bdashboard\b/u', $text) || preg_match('/\bड्यासबोर्ड\b/u', $text)) {
      return $this->navigate('dashboard', $locale);
    }
    if (preg_match('/\b(new|create|submit|file|report)\s+(complaint|गुनासो)\b/iu', $text)) {
      return $this->navigate('complaints.create', $locale);
    }
    if (preg_match('/\bcomplaint(s|\b|\s+list|\s+index|\s+page)?\b/iu', $text) || preg_match('/\bगुनासो(s|हरू)?\b/u', $text)) {
      return $this->navigate('complaints.index', $locale);
    }
    if (preg_match('/\bdepartments?\b/iu', $text) || preg_match('/\bविभाग(हरू)?\b/u', $text)) {
      return $this->navigate('departments.index', $locale);
    }
    if (preg_match('/\bcategor(y|ies)\b/iu', $text) || preg_match('/\bश्रेणी(हरू)?\b/u', $text)) {
      return $this->navigate('complaint-categories.index', $locale);
    }
    if (preg_match('/\bprofile\b/iu', $text) || preg_match('/\bप्रोफाइल\b/u', $text) || preg_match('/\bखाता\b/u', $text)) {
      return $this->navigate('profile.edit', $locale);
    }
    if (preg_match('/\blog(in| out|out)\b/iu', $text) || preg_match('/\bsign\s+in\b/iu', $text) || preg_match('/\bसाइन इन\b/u', $text) || preg_match('/\bलग इन\b/u', $text)) {
      return $this->navigate('login', $locale);
    }
    if (preg_match('/\bregister\b/iu', $text) || preg_match('/\bsign\s+up\b/iu', $text) || preg_match('/\bदर्ता\b/u', $text)) {
      return $this->navigate('register', $locale);
    }
    if (preg_match('/\b(home|welcome|landing)\b/iu', $text) || preg_match('/\bगृहपृष्ठ\b/u', $text) || preg_match('/\bस्वागत\b/u', $text)) {
      return $this->navigate('home', $locale);
    }

    // --- Language switching ---
    if (preg_match('/\b(english|अंग्रेजी)\b/iu', $text)) {
      return $this->result('change_language', $locale, ['locale' => 'en'], 'Switching to English.', 'अंग्रेजीमा बदल्दै।');
    }
    if (preg_match('/\b(nepali|नेपाली)\b/iu', $text)) {
      return $this->result('change_language', $locale, ['locale' => 'ne'], 'Switching to Nepali.', 'नेपालीमा बदल्दै।');
    }

    // --- Actions ---
    if (preg_match('/\b(log\s?out|sign\s?out|logout|लग आउट)\b/iu', $text)) {
      return $this->result('logout', $locale, [], 'Logging out.', 'लग आउट गर्दै।');
    }
    if (preg_match('/\b(go\s+back|back|previous|पछाडि|अघिल्लो)\b/iu', $text)) {
      return $this->result('go_back', $locale, [], 'Going back.', 'पछाडि जाँदै।');
    }
    if (preg_match('/\b(submit|send)\s*(form|complaint|it)?\b/iu', $text) || preg_match('/\b(पेश गर्नु|फारम पेश|पठाउ)\b/u', $text)) {
      return $this->result('submit_form', $locale, [], 'Submitting form.', 'फारम पेश गर्दै।');
    }
    if (preg_match('/\b(clear|reset)\s*filters?\b/iu', $text) || preg_match('/\bफिल्टर हटाउ\b/u', $text)) {
      return $this->result('clear_filters', $locale, [], 'Filters cleared.', 'फिल्टर हटाइयो।');
    }

    // --- Filters ---
    if (preg_match('/\b(under\s*review|समीक्षाधीन)\b/iu', $text)) {
      return $this->result('filter', $locale, ['filter' => 'status', 'value' => 'under_review'], 'Filtering under review.', 'समीक्षाधीन फिल्टर गरियो।');
    }
    if (preg_match('/\b(in\s*progress|progress|प्रगतिमा)\b/iu', $text)) {
      return $this->result('filter', $locale, ['filter' => 'status', 'value' => 'in_progress'], 'Filtering in progress.', 'प्रगतिमा फिल्टर गरियो।');
    }
    if (preg_match('/\b(resolved|समाधान)\b/iu', $text)) {
      return $this->result('filter', $locale, ['filter' => 'status', 'value' => 'resolved'], 'Filtering resolved.', 'समाधान फिल्टर गरियो।');
    }
    if (preg_match('/\b(pending|submitted|बाँकी)\b/iu', $text)) {
      return $this->result('filter', $locale, ['filter' => 'status', 'value' => 'submitted'], 'Filtering pending.', 'बाँकी फिल्टर गरियो।');
    }

    // --- Search ---
    if (preg_match('/^(search|find|look for|खोज)\s+(for\s+)?(.+)$/iu', $text, $m)) {
      return $this->result('search', $locale, ['value' => trim($m[3])], 'Searching.', 'खोजिरहेको छ।');
    }

    // --- Fill field ---
    if (preg_match('/^(title|शीर्षक)\s*(is|:)?\s*(.+)$/iu', $text, $m)) {
      return $this->result('fill_field', $locale, ['field' => 'title', 'value' => trim($m[3])], 'Title updated.', 'शीर्षक सेट गरियो।');
    }
    if (preg_match('/^(name|नाम)\s*(is|:)?\s*(.+)$/iu', $text, $m)) {
      return $this->result('fill_field', $locale, ['field' => 'name', 'value' => trim($m[3])], 'Name updated.', 'नाम सेट गरियो।');
    }
    if (preg_match('/^(description|विवरण)\s*(is|:)?\s*(.+)$/iu', $text, $m)) {
      return $this->result('fill_field', $locale, ['field' => 'description', 'value' => trim($m[3])], 'Description updated.', 'विवरण सेट गरियो।');
    }
    if (preg_match('/^(location|address|स्थान|ठेगाना)\s*(is|:)?\s*(.+)$/iu', $text, $m)) {
      return $this->result('fill_field', $locale, ['field' => 'location', 'value' => trim($m[3])], 'Location updated.', 'स्थान सेट गरियो।');
    }

    return null;
  }

  protected function matchKeywordFallback(string $transcript, string $locale): ?array
  {
    $text = mb_strtolower(trim($transcript));

    $keywords = [
      'dashboard'     => ['navigate', 'dashboard'],
      'complaint'     => ['navigate', 'complaints.index'],
      'department'    => ['navigate', 'departments.index'],
      'category'      => ['navigate', 'complaint-categories.index'],
      'profile'       => ['navigate', 'profile.edit'],
      'login'         => ['navigate', 'login'],
      'register'      => ['navigate', 'register'],
      'logout'        => ['logout', null],
      'गुनासो'        => ['navigate', 'complaints.index'],
      'विभाग'         => ['navigate', 'departments.index'],
      'श्रेणी'        => ['navigate', 'complaint-categories.index'],
      'प्रोफाइल'      => ['navigate', 'profile.edit'],
      'ड्यासबोर्ड'    => ['navigate', 'dashboard'],
    ];

    if (preg_match('/\b(home|welcome|गृहपृष्ठ)\b/u', $text)) {
      return $this->navigate('home', $locale);
    }

    $matched = [];
    foreach ($keywords as $word => [$action, $route]) {
      if (preg_match('/\b' . preg_quote($word, '/') . '\b/iu', $text)) {
        $matched[] = [$word, $action, $route];
      }
    }

    if (count($matched) === 1) {
      [$word, $action, $route] = $matched[0];
      if ($action === 'navigate' && $route) {
        return $this->navigate($route, $locale);
      }
      if ($action === 'logout') {
        return $this->result('logout', $locale, [], 'Logging out.', 'लग आउट गर्दै।');
      }
    }

    if (count($matched) > 1) {
      usort($matched, fn($a, $b) => strlen($b[0]) <=> strlen($a[0]));
      [$word, $action, $route] = $matched[0];
      if ($action === 'navigate' && $route) {
        return $this->navigate($route, $locale);
      }
    }

    return null;
  }

  protected function interpretWithGemini(string $transcript, string $locale, ?string $currentPage, ?string $pageContext): array
  {
    $schema = new Schema(
      type: DataType::OBJECT,
      properties: [
        'action' => new Schema(
          type: DataType::STRING,
          enum: ['navigate', 'fill_field', 'submit_form', 'change_language', 'search', 'filter', 'clear_filters', 'open_create', 'logout', 'go_back', 'unknown'],
        ),
        'route' => new Schema(
          type: DataType::STRING,
          enum: ['dashboard', 'complaints.index', 'complaints.create', 'departments.index', 'complaint-categories.index', 'profile.edit', 'login', 'register', 'home'],
        ),
        'field' => new Schema(
          type: DataType::STRING,
          enum: ['title', 'name', 'description', 'location'],
        ),
        'value' => new Schema(type: DataType::STRING),
        'filter' => new Schema(type: DataType::STRING, enum: ['status']),
        'target' => new Schema(type: DataType::STRING, enum: ['department', 'category', 'complaint', 'auto']),
        'locale' => new Schema(type: DataType::STRING, enum: ['en', 'ne']),
        'message' => new Schema(type: DataType::STRING),
      ],
      required: ['action', 'message'],
    );

    $examples = [
      ['user' => 'go to dashboard', 'action' => 'navigate', 'route' => 'dashboard'],
      ['user' => 'show me complaints', 'action' => 'navigate', 'route' => 'complaints.index'],
      ['user' => 'create new complaint', 'action' => 'open_create', 'target' => 'complaint'],
      ['user' => 'title is broken pipe', 'action' => 'fill_field', 'field' => 'title', 'value' => 'broken pipe'],
      ['user' => 'location is main street', 'action' => 'fill_field', 'field' => 'location', 'value' => 'main street'],
      ['user' => 'submit form', 'action' => 'submit_form'],
      ['user' => 'switch to nepali', 'action' => 'change_language', 'locale' => 'ne'],
      ['user' => 'search for water', 'action' => 'search', 'value' => 'water'],
      ['user' => 'show pending', 'action' => 'filter', 'filter' => 'status', 'value' => 'submitted'],
      ['user' => 'log out', 'action' => 'logout'],
      ['user' => 'go back', 'action' => 'go_back'],
    ];

    $prompt = "You are CiviSense voice command interpreter. Parse the user's spoken command into a structured action.\n";
    $prompt .= "Locale: {$locale}. Respond in that language.\n";
    if ($currentPage) {
      $prompt .= "Current page: {$currentPage}\n";
    }
    if ($pageContext) {
      $prompt .= "Context: {$pageContext}\n";
    }
    $prompt .= "\nExamples:\n";
    foreach ($examples as $ex) {
      $prompt .= "- \"" . $ex['user'] . "\" → " . json_encode($ex) . "\n";
    }
    $prompt .= "\nUser said: \"{$transcript}\"\n";
    $prompt .= "Respond with JSON only.";

    $response = Gemini::generativeModel(model: 'gemini-2.5-flash')
      ->withGenerationConfig(new GenerationConfig(
        responseMimeType: ResponseMimeType::APPLICATION_JSON,
        responseSchema: $schema,
      ))
      ->generateContent($prompt);

    $parsed = json_decode($response->text(), true, 512, JSON_THROW_ON_ERROR);

    return [
      'action' => $parsed['action'] ?? 'unknown',
      'route' => $parsed['route'] ?? null,
      'field' => $parsed['field'] ?? null,
      'value' => $parsed['value'] ?? null,
      'filter' => $parsed['filter'] ?? null,
      'target' => $parsed['target'] ?? null,
      'locale' => $parsed['locale'] ?? null,
      'message' => $parsed['message'] ?? ($locale === 'ne' ? 'आदेश बुझियो।' : 'Command understood.'),
      'source' => 'gemini',
    ];
  }

  private function navigate(string $route, string $locale): array
  {
    return [
      'action' => 'navigate',
      'route' => $route,
      'message' => $locale === 'ne' ? 'पृष्ठमा जाँदै।' : 'Navigating.',
      'source' => 'local',
    ];
  }

  private function result(string $action, string $locale, array $extra, string $enMsg, string $neMsg): array
  {
    return array_merge([
      'action' => $action,
      'message' => $locale === 'ne' ? $neMsg : $enMsg,
      'source' => 'local',
    ], $extra);
  }

  protected function fallback(string $transcript, string $locale): array
  {
    $suggestions = [
      'Try: "go to dashboard", "show complaints", "create complaint", "title is ...", "search for ..."',
      'Examples: "dashboard", "log out", "go back", "switch to nepali"',
    ];

    return [
      'action' => 'unknown',
      'message' => $locale === 'ne'
        ? 'माफ गर्नुहोस्, त्यो आदेश बुझिएन। ' . ($locale === 'ne' ? 'प्रयास गर्नुहोस्: ड्यासबोर्ड, गुनासो, फिल्टर' : '')
        : 'Sorry, I could not understand that command. ' . $suggestions[0],
      'source' => 'fallback',
    ];
  }
}
