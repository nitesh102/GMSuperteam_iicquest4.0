<?php

namespace App\Services;

use App\Models\Complaint;
use Illuminate\Support\Collection;

class DuplicateDetectionService
{
    private const EARTH_RADIUS_KM = 6371;
    private const DEFAULT_RADIUS_METERS = 500;
    private const TITLE_SIMILARITY_THRESHOLD = 65;
    private const DISTANCE_THRESHOLD_METERS = 500;

    public function __construct(
        private GeolocationService $geo,
    ) {}

    public function checkForDuplicates(
        float $lat,
        float $lng,
        string $title,
        ?int $excludeId = null,
        float $radiusMeters = self::DEFAULT_RADIUS_METERS,
    ): Collection {
        $nearby = $this->findNearbyActiveComplaints($lat, $lng, $radiusMeters, $excludeId);

        if ($nearby->isEmpty()) {
            return collect();
        }

        $results = collect();
        $title = strtolower(trim($title));

        foreach ($nearby as $complaint) {
            similar_text($title, strtolower(trim($complaint->title)), $similarity);

            if ($similarity < self::TITLE_SIMILARITY_THRESHOLD) {
                continue;
            }

            $distance = $this->geo->haversineDistance(
                $lat, $lng,
                (float) $complaint->latitude,
                (float) $complaint->longitude,
            );

            if ($distance > $radiusMeters) {
                continue;
            }

            $locationScore = $this->calculateLocationScore($distance);
            $confidence = $this->calculateConfidence($similarity, $locationScore);

            $results->push([
                'id' => $complaint->id,
                'complaint_no' => $complaint->complaint_no,
                'title' => $complaint->title,
                'current_status' => $complaint->current_status,
                'distance_meters' => round($distance, 1),
                'title_similarity' => round($similarity, 1),
                'confidence' => round($confidence, 1),
            ]);
        }

        return $results->sortByDesc('confidence')->values();
    }

    private function findNearbyActiveComplaints(
        float $lat,
        float $lng,
        float $radiusMeters,
        ?int $excludeId = null,
    ): Collection {
        $latDelta = $radiusMeters / 111320;
        $lngDelta = $radiusMeters / (111320 * cos(deg2rad($lat)));

        $query = Complaint::whereNull('duplicate_of_id')
            ->whereIn('current_status', ['submitted', 'under_review', 'assigned', 'in_progress'])
            ->where('is_spam', false)
            ->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
            ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta]);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        $candidates = $query->get([
            'id', 'complaint_no', 'title', 'current_status', 'latitude', 'longitude',
        ]);

        return $candidates->filter(function ($c) use ($lat, $lng, $radiusMeters) {
            if ($c->latitude === null || $c->longitude === null) return false;
            $distance = $this->geo->haversineDistance(
                $lat, $lng,
                (float) $c->latitude,
                (float) $c->longitude,
            );
            return $distance <= $radiusMeters;
        })->values();
    }

    private function calculateLocationScore(float $distanceMeters): float
    {
        return match (true) {
            $distanceMeters < 100  => 1.0,
            $distanceMeters < 200  => 0.8,
            $distanceMeters < 300  => 0.6,
            $distanceMeters < 500  => 0.4,
            default                => 0.0,
        };
    }

    private function calculateConfidence(float $titleSimilarity, float $locationScore): float
    {
        return ($titleSimilarity * 0.7) + ($locationScore * 0.3);
    }
}
