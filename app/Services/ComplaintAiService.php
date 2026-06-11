<?php

namespace App\Services;

use App\Models\Complaint;
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
        $catList  = collect($categories)->pluck('name')->values()->toArray();

        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'is_spam' => new Schema(
                    type: DataType::BOOLEAN,
                    description: 'true if this is spam or not a genuine civic complaint.',
                ),
                'spam_reason' => new Schema(
                    type: DataType::STRING,
                    description: 'Short reason why it is spam. Empty string if not spam.',
                ),
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
            required: ['is_spam', 'spam_reason', 'department_name', 'category_name', 'priority', 'summary'],
        );

        $hasImages = collect($attachmentPaths)->filter(fn($p) => $p && file_exists($p))->isNotEmpty();

        $prompt  = "You are the CiviSense AI municipal triage engine.\n\n";

        $prompt .= "STEP 1 — SPAM CHECK: Set is_spam=true if ANY apply:\n";
        $prompt .= "  • Title is greetings, filler, or test words only (hello, hi, test, check, asdf, abc, etc.)\n";
        $prompt .= "  • Title and description together do NOT describe a real civic/municipal problem\n";
        $prompt .= "  • Description is meta-commentary (e.g. 'this is a test', 'checking if this works', 'why did this pass spam')\n";
        $prompt .= "  • Description is gibberish, random text, personal message, or promotional content\n";
        if ($hasImages) {
            $prompt .= "  • Attached image does NOT show a real civic problem, or does not match what is described\n";
        }
        $prompt .= "Real civic problems: roads, potholes, water supply, electricity, garbage, sewage, streetlights, flooding, public property damage, etc.\n";
        $prompt .= "If is_spam=true, set spam_reason to a short explanation. Still fill the other fields (use 'low' for priority).\n\n";

        $prompt .= "STEP 2 — TRIAGE (only if not spam): Assign to the correct department and category.\n";
        $prompt .= "Available departments: " . implode(', ', $deptList) . "\n";
        $prompt .= "Available categories: " . implode(', ', $catList) . "\n\n";

        $prompt .= "Title: {$title}\n";
        $prompt .= "Description: {$description}";

        $arguments = [$prompt];

        foreach (array_slice($attachmentPaths, 0, 3) as $path) {
            if ($path && file_exists($path)) {
                $mime = match (mime_content_type($path)) {
                    'image/png'  => MimeType::IMAGE_PNG,
                    'image/webp' => MimeType::IMAGE_WEBP,
                    'image/gif'  => MimeType::IMAGE_PNG,
                    default      => MimeType::IMAGE_JPEG,
                };
                $arguments[] = new Blob(
                    mimeType: $mime,
                    data: base64_encode(file_get_contents($path)),
                );
            }
        }

        $response = Gemini::generativeModel(model: 'gemini-2.5-flash')
            ->withGenerationConfig(new GenerationConfig(
                responseMimeType: ResponseMimeType::APPLICATION_JSON,
                responseSchema: $schema,
            ))
            ->generateContent(...$arguments);

        return json_decode($response->text(), true);
    }

    public function checkDuplicates(
        string $title,
        string $description,
        array $nearbyComplaints,
        ?float $latitude = null,
        ?float $longitude = null,
    ): array {
        if (empty($nearbyComplaints)) {
            return ['is_duplicate' => false, 'matched_complaint_id' => null, 'match_reason' => ''];
        }

        $existingList = collect($nearbyComplaints)->map(fn($c) => sprintf(
            "[ID:%d] \"%s\"\n   Location: %s\n   Description: %s",
            $c['id'],
            $c['title'],
            $c['location'] ?? 'N/A',
            mb_strlen($c['description'] ?? '') > 300 ? mb_substr($c['description'], 0, 300) . '...' : ($c['description'] ?? 'N/A'),
        ))->implode("\n\n");

        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'is_duplicate' => new Schema(
                    type: DataType::BOOLEAN,
                    description: 'true if this new complaint describes the same real-world issue as any existing complaint above.',
                ),
                'matched_complaint_id' => new Schema(
                    type: DataType::INTEGER,
                    description: 'The ID of the existing complaint that reports the same issue. Return 0 if no match.',
                ),
                'match_reason' => new Schema(
                    type: DataType::STRING,
                    description: 'Explain why these are duplicates — what specific detail matches (e.g. same location, same problem description). Empty if not a duplicate.',
                ),
            ],
            required: ['is_duplicate', 'matched_complaint_id', 'match_reason'],
        );

        $prompt  = "You are the CiviSense AI duplicate detection engine.\n\n";
        $prompt .= "A new complaint has been submitted at the same location as existing complaints:\n";
        $prompt .= "Title: {$title}\n";
        $prompt .= "Description: {$description}\n";
        if ($latitude && $longitude) {
            $prompt .= "GPS: {$latitude}, {$longitude}\n";
        }
        $prompt .= "\nThese existing complaints are nearby. Determine if the NEW complaint reports the IDENTICAL real-world issue as any of them.\n\n";
        $prompt .= "{$existingList}\n\n";
        $prompt .= "Guidelines:\n";
        $prompt .= "  • Set is_duplicate=true ONLY if they are clearly about the SAME SPECIFIC problem (e.g. same pothole, same broken streetlight, same garbage pile)\n";
        $prompt .= "  • A different problem type at the same location is NOT a duplicate (e.g. pothole vs broken light at the same spot)\n";
        $prompt .= "  • General similarity (both about roads or both about water) is NOT enough\n";
        $prompt .= "  • If not a duplicate, return matched_complaint_id=0 and empty match_reason";

        $response = Gemini::generativeModel(model: 'gemini-2.5-flash')
            ->withGenerationConfig(new GenerationConfig(
                responseMimeType: ResponseMimeType::APPLICATION_JSON,
                responseSchema: $schema,
            ))
            ->generateContent($prompt);

        $result = json_decode($response->text(), true);

        return [
            'is_duplicate' => $result['is_duplicate'] ?? false,
            'matched_complaint_id' => $result['is_duplicate'] ? ($result['matched_complaint_id'] ?? null) : null,
            'match_reason' => $result['is_duplicate'] ? ($result['match_reason'] ?? '') : '',
        ];
    }
}
