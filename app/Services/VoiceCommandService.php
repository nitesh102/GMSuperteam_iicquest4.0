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

    try {
      return $this->interpretWithGemini($transcript, $locale, $currentPage, $pageContext);
    } catch (\Throwable) {
      return $this->fallback($transcript, $locale);
    }
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

    $prompt = "You are the CiviSense voice command interpreter for a civic complaint platform.\n";
    $prompt .= "Parse spoken commands for both admin and citizen users.\n";
    $prompt .= "App locale: {$locale}. Respond message in that language.\n";
    if ($currentPage) {
      $prompt .= "Current page: {$currentPage}\n";
    }
    if ($pageContext) {
      $prompt .= "Current page context: {$pageContext}\n";
    }
    $prompt .= "\nRoutes: dashboard, complaints.index, complaints.create, departments.index, complaint-categories.index, profile.edit, login, register, home\n";
    $prompt .= "Actions: navigate, fill_field, submit_form, change_language, search, filter, clear_filters, open_create, logout, go_back\n";
    $prompt .= "Fields: title (for complaints), name (for departments/categories), description, location\n";
    $prompt .= "User said: \"{$transcript}\"";

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

  protected function matchLocalCommand(string $transcript, string $locale): ?array
  {
    $text = mb_strtolower($transcript);

    $createPatterns = [
      ['/(create|add|make|open)(\s+(a\s+)?)?(new\s+)?(department|departments|विभाग)/iu', 'open_create', 'department'],
      ['/^(new)\s+(department|departments|विभाग)/iu', 'open_create', 'department'],
      ['/(नयाँ|नया|सिर्जना|बनाउ|थप).*(विभाग)/u', 'open_create', 'department'],
      ['/(create|add|make|open)(\s+(a\s+)?)?(new\s+)?(category|categories|श्रेणी)/iu', 'open_create', 'category'],
      ['/(नयाँ|नया|सिर्जना|बनाउ|थप).*(श्रेणी)/u', 'open_create', 'category'],
      ['/(create|add|make|open|file|report)(\s+(a\s+)?)?(new\s+)?(complaint|complaints|गुनासो)/iu', 'open_create', 'complaint'],
      ['/(नयाँ|नया|सिर्जना|बनाउ|थप).*(गुनासो)/u', 'open_create', 'complaint'],
    ];

    foreach ($createPatterns as $pattern) {
      if (preg_match($pattern[0], $transcript)) {
        return [
          'action' => 'open_create',
          'target' => $pattern[2],
          'message' => $locale === 'ne' ? 'सिर्जना फारम खोल्दै।' : 'Opening create form.',
          'source' => 'local',
        ];
      }
    }

    $patterns = [
      ['/(go\s+to|open|show|navigate\s+to|जाउ|खोल|हेर्नु)\s*(the\s+)?dashboard/iu', 'navigate', 'dashboard'],
      ['/(dashboard|ड्यासबोर्ड)/u', 'navigate', 'dashboard'],
      ['/(new complaint|submit complaint|create complaint|नयाँ गुनासो|गुनासो पेश|गुनासो दर्ता)/u', 'navigate', 'complaints.create'],
      ['/(go\s+to|open|show|navigate\s+to|जाउ|खोल)\s*(the\s+)?complaints?/iu', 'navigate', 'complaints.index'],
      ['/(complaints? list|view complaints?|गुनासो सूची|गुनासोहरू)$/u', 'navigate', 'complaints.index'],
      ['/(go\s+to|open|show|navigate\s+to|जाउ|खोल)\s*(the\s+)?departments?/iu', 'navigate', 'departments.index'],
      ['/^(departments?|manage departments?|विभाग|विभागहरू)$/u', 'navigate', 'departments.index'],
      ['/(go\s+to|open|show|navigate\s+to|जाउ|खोल)\s*(the\s+)?categor(y|ies)/iu', 'navigate', 'complaint-categories.index'],
      ['/^(categories?|complaint categories?|श्रेणी|श्रेणीहरू)$/u', 'navigate', 'complaint-categories.index'],
      ['/(go\s+to|open|show|navigate\s+to|जाउ|खोल)\s*(the\s+)?profile/iu', 'navigate', 'profile.edit'],
      ['/(profile|प्रोफाइल|खाता)/u', 'navigate', 'profile.edit'],
      ['/(login|sign in|log in|साइन इन|लग इन)/u', 'navigate', 'login'],
      ['/(register|sign up|दर्ता)/u', 'navigate', 'register'],
      ['/(home|welcome|landing|गृहपृष्ठ|स्वागत)/u', 'navigate', 'home'],
      ['/(switch\s+to\s+)?(english|अंग्रेजी)/iu', 'change_language', null, 'en'],
      ['/(switch\s+to\s+)?(nepali|नेपाली)/iu', 'change_language', null, 'ne'],
      ['/(log\s?out|sign\s?out|logout|लग आउट)/iu', 'logout', null],
      ['/(go\s+back|back|पछाडि)/iu', 'go_back', null],
      ['/(submit form|send complaint|पेश गर्नु|फारम पेश)/u', 'submit_form', null],
      ['/(clear|reset)\s+(filters?|फिल्टर)/iu', 'clear_filters', null],
      ['/(show|filter)\s+(pending|बाँकी)/iu', 'filter', 'status', 'submitted'],
      ['/(show|filter)\s+(resolved|समाधान)/iu', 'filter', 'status', 'resolved'],
    ];

    foreach ($patterns as $pattern) {
      if (! preg_match($pattern[0], $text)) {
        continue;
      }

      $action = $pattern[1];

      if ($action === 'change_language') {
        return [
          'action' => 'change_language',
          'locale' => $pattern[3],
          'message' => $locale === 'ne' ? 'भाषा बदल्दै।' : 'Changing language.',
          'source' => 'local',
        ];
      }

      if ($action === 'filter') {
        return [
          'action' => 'filter',
          'filter' => $pattern[2],
          'value' => $pattern[3],
          'message' => $locale === 'ne' ? 'फिल्टर लागू गरियो।' : 'Filter applied.',
          'source' => 'local',
        ];
      }

      if ($action === 'navigate') {
        return [
          'action' => 'navigate',
          'route' => $pattern[2],
          'message' => $locale === 'ne' ? 'पृष्ठमा जाँदै।' : 'Navigating.',
          'source' => 'local',
        ];
      }

      return [
        'action' => $action,
        'message' => $locale === 'ne' ? 'आदेश पूरा गर्दै।' : 'Executing command.',
        'source' => 'local',
      ];
    }

    if (preg_match('/^(search|find|खोज)\s+(for\s+)?(.+)$/iu', $transcript, $m)) {
      return [
        'action' => 'search',
        'value' => trim($m[3]),
        'message' => $locale === 'ne' ? 'खोजिरहेको छ।' : 'Searching.',
        'source' => 'local',
      ];
    }

    if (preg_match('/^(title|name|शीर्षक|नाम)\s*(is|:)?\s*(.+)$/ui', $transcript, $m)) {
      $field = str_contains(strtolower($m[1]), 'name') || str_contains($m[1], 'नाम') ? 'name' : 'title';
      return [
        'action' => 'fill_field',
        'field' => $field,
        'value' => trim($m[3]),
        'message' => $locale === 'ne' ? ($field === 'name' ? 'नाम सेट गरियो।' : 'शीर्षक सेट गरियो।') : ($field === 'name' ? 'Name updated.' : 'Title updated.'),
        'source' => 'local',
      ];
    }

    if (preg_match('/^(description|विवरण)\s*(is|:)?\s*(.+)$/ui', $transcript, $m)) {
      return [
        'action' => 'fill_field',
        'field' => 'description',
        'value' => trim($m[3]),
        'message' => $locale === 'ne' ? 'विवरण सेट गरियो।' : 'Description updated.',
        'source' => 'local',
      ];
    }

    if (preg_match('/^(location|address|स्थान|ठेगाना)\s*(is|:)?\s*(.+)$/ui', $transcript, $m)) {
      return [
        'action' => 'fill_field',
        'field' => 'location',
        'value' => trim($m[3]),
        'message' => $locale === 'ne' ? 'स्थान सेट गरियो।' : 'Location updated.',
        'source' => 'local',
      ];
    }

    return null;
  }

  protected function fallback(string $transcript, string $locale): array
  {
    return [
      'action' => 'unknown',
      'message' => $locale === 'ne'
        ? 'माफ गर्नुहोस्, त्यो आदेश बुझिएन।'
        : 'Sorry, I could not understand that command.',
      'source' => 'fallback',
    ];
  }
}
