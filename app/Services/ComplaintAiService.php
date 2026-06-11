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
                'is_spam' => new Schema(
                    type: DataType::BOOLEAN,
                    description: 'true if this complaint is spam, fake, irrelevant, or if any attached image does not match the described civic issue (e.g. screenshot, meme, unrelated photo). false if the complaint and images appear genuine.',
                ),
                'spam_reason' => new Schema(
                    type: DataType::STRING,
                    description: 'If is_spam is true, a short explanation of why (e.g. "Attached image is a software screenshot unrelated to the reported civic issue"). Empty string if not spam.',
                ),
            ],
            required: ['department_name', 'category_name', 'priority', 'summary', 'is_spam', 'spam_reason'],
        );

        $prompt = "You are the CiviSense AI municipal triage engine.\n";
        $prompt .= "Analyze this citizen complaint data and assign it to the correct department and category.\n\n";
        $prompt .= "SPAM DETECTION — set is_spam=true if ANY of the following apply:\n";
        $prompt .= "  - The title or description is clearly fake, nonsensical, or a test submission.\n";
        $prompt .= "  - An attached image does not match the described civic issue (e.g. a dashboard screenshot, meme, selfie, or any image unrelated to roads, infrastructure, sanitation, utilities, or public property).\n";
        $prompt .= "  - The complaint is abusive, promotional, or entirely off-topic for a municipal grievance system.\n";
        $prompt .= "If is_spam=true, still fill in department_name, category_name, priority (use 'low'), and summary, but set spam_reason.\n\n";
        $prompt .= "Available departments: " . implode(', ', $deptList) . "\n";
        $prompt .= "Available categories: " . implode(', ', $catList) . "\n\n";
        $prompt .= "Title: {$title}\n";
        $prompt .= "Description: {$description}\n\n";
        $prompt .= "Carefully examine any attached images. Verify they actually show a real civic problem matching the title/description.";

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

        $response = Gemini::generativeModel(model: 'gemini-2.5-pro')
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
