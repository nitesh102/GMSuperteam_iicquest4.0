<?php

namespace App\Http\Controllers;

use App\Services\VoiceCommandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoiceCommandController extends Controller
{
  public function __construct(private VoiceCommandService $voiceCommandService) {}

  public function interpret(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'transcript' => ['required', 'string', 'max:1000'],
      'locale' => ['nullable', 'string', 'in:en,ne'],
      'current_page' => ['nullable', 'string', 'max:255'],
    ]);

    $result = $this->voiceCommandService->interpret(
      $validated['transcript'],
      $validated['locale'] ?? app()->getLocale(),
      $validated['current_page'] ?? null,
    );

    return response()->json($result);
  }
}
