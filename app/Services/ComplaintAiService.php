<?php

namespace App\Services;

use Gemini\Data\Blob;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\MimeType;
use Gemini\Enums\ResponseMimeType;
use Gemini\Laravel\Facades\Gemini;

class ComplaintAiService
{
    public function analyzeComplaint(
        string $title,
        string $description,
        array $attachmentPaths = [],
        array $departments = [],
        array $categories = [],
    ): array {
        $deptList = collect($departments)->pluck('name')->values()->toArray();
        $catList = collect($categories)->pluck('name')->values()->toArray();

        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'department_name' => new Schema(
                    type: DataType::STRING,
                    enum: $deptList,
                    description: 'The single most relevant department for this complaint.',
                ),
                'category_name' => new Schema(
                    type: DataType::STRING,
                    enum: $catList,
                    description: 'The most specific category under the chosen department.',
                ),
                'priority' => new Schema(
                    type: DataType::STRING,
                    enum: ['low', 'medium', 'high', 'emergency'],
                ),
                'summary' => new Schema(
                    type: DataType::STRING,
                    description: 'A clean, technical summary under 200 characters. Absolutely no HTML tags.',
                ),
            ],
            required: ['department_name', 'category_name', 'priority', 'summary'],
        );

        $prompt = "You are the CiviSense AI municipal triage engine.\n";
        $prompt .= "Analyze this citizen complaint data and assign it to the correct department and category.\n\n";
        $prompt .= "Available departments: " . implode(', ', $deptList) . "\n";
        $prompt .= "Available categories: " . implode(', ', $catList) . "\n\n";
        $prompt .= "Title: {$title}\n";
        $prompt .= "Description: {$description}\n\n";
        $prompt .= "Assess any attached image proof to judge emergency severity.";

        $arguments = [$prompt];

        foreach (array_slice($attachmentPaths, 0, 3) as $path) {
            if ($path && file_exists($path)) {
                $mime = match (mime_content_type($path)) {
                    'image/png' => MimeType::IMAGE_PNG,
                    'image/webp' => MimeType::IMAGE_WEBP,
                    'image/gif' => MimeType::IMAGE_PNG,
                    default => MimeType::IMAGE_JPEG,
                };
                $arguments[] = new Blob(
                    mimeType: $mime,
                    data: base64_encode(file_get_contents($path)),
                );
            }
        }

        $response = Gemini::generativeModel(model: 'gemini-2.5-flash')
            ->withGenerationConfig(
                new GenerationConfig(
                    responseMimeType: ResponseMimeType::APPLICATION_JSON,
                    responseSchema: $schema,
                ),
            )
            ->generateContent(...$arguments);

        return json_decode($response->text(), true);
    }
}
