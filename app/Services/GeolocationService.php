<?php

namespace App\Services;

use App\Models\Complaint;
use Illuminate\Database\Eloquent\Collection;

class GeolocationService
{
    private const EARTH_RADIUS_METERS = 6371000;
    private const VERIFICATION_THRESHOLD_METERS = 2000;

    public function haversineDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $lng1Rad = deg2rad($lng1);
        $lng2Rad = deg2rad($lng2);

        $dlat = $lat2Rad - $lat1Rad;
        $dlng = $lng2Rad - $lng1Rad;

        $a = sin($dlat / 2) ** 2
           + cos($lat1Rad) * cos($lat2Rad) * sin($dlng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    public function verifyLocation(
        float $reporterLat,
        float $reporterLng,
        float $complaintLat,
        float $complaintLng,
        int $threshold = self::VERIFICATION_THRESHOLD_METERS
    ): array {
        $distance = $this->haversineDistance(
            $reporterLat,
            $reporterLng,
            $complaintLat,
            $complaintLng
        );

        return [
            'verified' => $distance <= $threshold,
            'distance' => round($distance, 1),
        ];
    }

    public function findNearbyComplaints(
        float $lat,
        float $lng,
        float $radiusMeters = 50,
    ): Collection {
        $latDelta = $radiusMeters / 111320;
        $lngDelta = $radiusMeters / (111320 * cos(deg2rad($lat)));

        $candidates = Complaint::whereNull('duplicate_of_id')
            ->whereIn('current_status', ['submitted', 'under_review', 'assigned', 'in_progress'])
            ->where('is_spam', false)
            ->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
            ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta])
            ->get(['id', 'complaint_no', 'title', 'description', 'location', 'latitude', 'longitude']);

        return $candidates->filter(function ($c) use ($lat, $lng, $radiusMeters) {
            if ($c->latitude === null || $c->longitude === null) return false;
            $distance = $this->haversineDistance($lat, $lng, (float) $c->latitude, (float) $c->longitude);
            $c->distance = round($distance, 1);
            return $distance <= $radiusMeters;
        })->values();
    }
}
