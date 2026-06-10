<?php

namespace App\Services;

use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\ResponseMimeType;
use Gemini\Laravel\Facades\Gemini;

class VoiceCommandService
{
  public function interpret(string $transcript, string $locale = 'en', ?string $currentPage = null): array
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
      return $this->interpretWithGemini($transcript, $locale, $currentPage);
    } catch (\Throwable) {
      return $this->fallback($transcript, $locale);
    }
  }

  protected function interpretWithGemini(string $transcript, string $locale, ?string $currentPage): array
  {
    $schema = new Schema(
      type: DataType::OBJECT,
      properties: [
        'action' => new Schema(
          type: DataType::STRING,
          enum: ['navigate', 'fill_field', 'submit_form', 'change_language', 'unknown'],
        ),
        'route' => new Schema(
          type: DataType::STRING,
          enum: ['dashboard', 'complaints.index', 'complaints.create', 'departments.index', 'complaint-categories.index', 'profile.edit', 'home'],
          description: 'Target route when action is navigate.',
        ),
        'field' => new Schema(
          type: DataType::STRING,
          enum: ['title', 'description', 'location'],
          description: 'Form field when action is fill_field.',
        ),
        'value' => new Schema(
          type: DataType::STRING,
          description: 'Text value for fill_field action.',
        ),
        'locale' => new Schema(
          type: DataType::STRING,
          enum: ['en', 'ne'],
        ),
        'message' => new Schema(
          type: DataType::STRING,
          description: 'Short user-facing confirmation in the requested app locale.',
        ),
      ],
      required: ['action', 'message'],
    );

    $prompt = "You are the CiviSense voice command interpreter.\n";
    $prompt .= "Parse the user's spoken command into a structured action.\n";
    $prompt .= "App locale: {$locale}. Respond message in that language.\n";
    if ($currentPage) {
      $prompt .= "Current page: {$currentPage}\n";
    }
    $prompt .= "\nAvailable navigation routes:\n";
    $prompt .= "- dashboard: analytics home\n";
    $prompt .= "- complaints.index: list complaints\n";
    $prompt .= "- complaints.create: submit new complaint\n";
    $prompt .= "- departments.index: manage departments\n";
    $prompt .= "- complaint-categories.index: manage categories\n";
    $prompt .= "- profile.edit: user profile\n";
    $prompt .= "- home: welcome/landing page\n\n";
    $prompt .= "For fill_field, extract spoken text into value for title, description, or location.\n";
    $prompt .= "For change_language, set locale to en or ne.\n";
    $prompt .= "For submit_form, use when user wants to submit/send the current complaint form.\n";
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
      'locale' => $parsed['locale'] ?? null,
      'message' => $parsed['message'] ?? ($locale === 'ne' ? 'आदेश बुझियो।' : 'Command understood.'),
      'source' => 'gemini',
    ];
  }

  protected function matchLocalCommand(string $transcript, string $locale): ?array
  {
    $text = mb_strtolower($transcript);

    $patterns = [
      ['/(go\s+to|open|show|navigate\s+to|जाउ|खोल|हेर्नु)\s*(the\s+)?dashboard/iu', 'navigate', 'dashboard'],
      ['/(dashboard|ड्यासबोर्ड)/u', 'navigate', 'dashboard'],
      ['/(new complaint|submit complaint|create complaint|नयाँ गुनासो|गुनासो पेश|गुनासो दर्ता)/u', 'navigate', 'complaints.create'],
      ['/(go\s+to|open|show|navigate\s+to|जाउ|खोल)\s*(the\s+)?complaints?/iu', 'navigate', 'complaints.index'],
      ['/(complaints? list|view complaints?|गुनासो सूची|गुनासोहरू|गुनासो)/u', 'navigate', 'complaints.index'],
      ['/(go\s+to|open|show|navigate\s+to|जाउ|खोल)\s*(the\s+)?departments?/iu', 'navigate', 'departments.index'],
      ['/(departments?|विभाग)/u', 'navigate', 'departments.index'],
      ['/(go\s+to|open|show|navigate\s+to|जाउ|खोल)\s*(the\s+)?categor(y|ies)/iu', 'navigate', 'complaint-categories.index'],
      ['/(categories?|श्रेणी)/u', 'navigate', 'complaint-categories.index'],
      ['/(go\s+to|open|show|navigate\s+to|जाउ|खोल)\s*(the\s+)?profile/iu', 'navigate', 'profile.edit'],
      ['/(profile|प्रोफाइल|खाता)/u', 'navigate', 'profile.edit'],
      ['/(home|welcome|landing|गृहपृष्ठ|स्वागत)/u', 'navigate', 'home'],
      ['/(switch\s+to\s+)?(english|अंग्रेजी)/iu', 'change_language', null, 'en'],
      ['/(switch\s+to\s+)?(nepali|नेपाली)/iu', 'change_language', null, 'ne'],
      ['/(submit form|send complaint|पेश गर्नु|फारम पेश)/u', 'submit_form', null],
    ];

    foreach ($patterns as $pattern) {
      if (preg_match($pattern[0], $text)) {
        $result = [
          'action' => $pattern[1],
          'route' => $pattern[2] ?? null,
          'field' => null,
          'value' => null,
          'locale' => $pattern[3] ?? null,
          'message' => $locale === 'ne' ? 'आदेश पूरा गर्दै।' : 'Executing command.',
          'source' => 'local',
        ];

        return $result;
      }
    }

    if (preg_match('/^(title|शीर्षक)\s*(is|:)?\s*(.+)$/ui', $transcript, $m)) {
      return [
        'action' => 'fill_field',
        'route' => null,
        'field' => 'title',
        'value' => trim($m[3]),
        'locale' => null,
        'message' => $locale === 'ne' ? 'शीर्षक सेट गरियो।' : 'Title updated.',
        'source' => 'local',
      ];
    }

    if (preg_match('/^(description|विवरण)\s*(is|:)?\s*(.+)$/ui', $transcript, $m)) {
      return [
        'action' => 'fill_field',
        'route' => null,
        'field' => 'description',
        'value' => trim($m[3]),
        'locale' => null,
        'message' => $locale === 'ne' ? 'विवरण सेट गरियो।' : 'Description updated.',
        'source' => 'local',
      ];
    }

    return null;
  }

  protected function fallback(string $transcript, string $locale): array
  {
    return [
      'action' => 'unknown',
      'route' => null,
      'field' => null,
      'value' => null,
      'locale' => null,
      'message' => $locale === 'ne'
        ? 'माफ गर्नुहोस्, त्यो आदेश बुझिएन।'
        : 'Sorry, I could not understand that command.',
      'source' => 'fallback',
    ];
  }
}
