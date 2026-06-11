<script setup>
import { ref, onMounted, computed } from 'vue'
import { useLeafletMap } from '@/Composables/useLeafletMap'

const { L, createMap } = useLeafletMap()

const props = defineProps({
    latitude: { type: Number, required: true },
    longitude: { type: Number, required: true },
    reporterLatitude: { type: Number, default: null },
    reporterLongitude: { type: Number, default: null },
    locationVerified: { type: Boolean, default: false },
    locationDistance: { type: Number, default: null },
    location: { type: String, default: null },
})

const mapContainer = ref(null)

const distanceText = computed(() => {
    if (props.locationDistance === null) return null
    if (props.locationDistance < 1000) return `${Math.round(props.locationDistance)} m`
    return `${(props.locationDistance / 1000).toFixed(2)} km`
})

function getGoogleMapsLink(lat, lng) {
    return `https://www.google.com/maps?q=${lat},${lng}`
}

function initMap() {
    const center = [props.latitude, props.longitude]
    const map = createMap(mapContainer.value, {
        center,
        zoom: 15,
        zoomControl: true,
    })

    const complaintPopup = L.popup().setContent(`
        <div style="font-size:13px;font-weight:500;margin-bottom:2px;">Complaint Location</div>
        ${props.location ? `<div style="font-size:12px;color:#6b7280;">${props.location}</div>` : ''}
        <div style="font-size:11px;color:#9ca3af;margin-top:2px;">
            ${props.latitude.toFixed(5)}, ${props.longitude.toFixed(5)}
        </div>
    `)

    L.marker(center).addTo(map)
        .bindPopup(complaintPopup)
        .openPopup()

    if (props.reporterLatitude && props.reporterLongitude) {
        const reporterPos = [props.reporterLatitude, props.reporterLongitude]

        L.circleMarker(reporterPos, {
            radius: 7,
            fillColor: '#3B82F6',
            fillOpacity: 1,
            strokeColor: '#FFFFFF',
            weight: 2,
        }).addTo(map)
            .bindPopup('<div style="font-size:13px;font-weight:500;">Reporter Location (GPS)</div>')
            .openPopup()

        L.polyline([reporterPos, center], {
            color: '#6366F1',
            opacity: 0.6,
            weight: 2,
        }).addTo(map)

        const bounds = L.latLngBounds(reporterPos, center)
        map.fitBounds(bounds)
    }
}

onMounted(initMap)
</script>

<template>
    <div class="space-y-3">
        <div ref="mapContainer" class="rounded-lg border border-gray-200 overflow-hidden" style="height: 350px"></div>

        <div v-if="latitude && longitude" class="flex items-center gap-4 text-sm">
            <a
                :href="getGoogleMapsLink(latitude, longitude)"
                target="_blank"
                class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-700 font-medium"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Open in Google Maps
            </a>
            <span class="text-gray-400">|</span>
            <span class="text-gray-500 font-mono text-xs">{{ latitude.toFixed(5) }}, {{ longitude.toFixed(5) }}</span>
        </div>
    </div>
</template>
