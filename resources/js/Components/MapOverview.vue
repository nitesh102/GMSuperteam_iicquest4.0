<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { useLeafletMap } from '@/Composables/useLeafletMap'
import 'leaflet.markercluster'
import 'leaflet.markercluster/dist/MarkerCluster.css'
import 'leaflet.markercluster/dist/MarkerCluster.Default.css'

const { L, createMap } = useLeafletMap()

const props = defineProps({
    complaints: { type: Array, default: () => [] },
    height: { type: String, default: '600px' },
})

const mapContainer = ref(null)
let mapInstance = null

const STATUS_COLORS = {
    submitted: '#3B82F6',
    under_review: '#F59E0B',
    assigned: '#8B5CF6',
    in_progress: '#6366F1',
    resolved: '#22C55E',
    rejected: '#EF4444',
    closed: '#6B7280',
}

function isValidCoords(lat, lng) {
    if (lat === null || lat === undefined || lng === null || lng === undefined) return false
    if (lat === '' || lng === '') return false
    const n = parseFloat(lat)
    const e = parseFloat(lng)
    return !isNaN(n) && !isNaN(e) && isFinite(n) && isFinite(e)
}

const complaintsWithCoords = computed(() =>
    props.complaints.filter(c => isValidCoords(c.latitude, c.longitude))
)

const hasValidCoords = computed(() => complaintsWithCoords.value.length > 0)

function createClusterIcon(color, count) {
    const size = count < 10 ? 36 : count < 100 ? 44 : 52
    return L.divIcon({
        html: `<div style="width:${size}px;height:${size}px;border-radius:50%;background:${color};color:white;display:flex;align-items:center;justify-content:center;font-size:${size < 44 ? 13 : 15}px;font-weight:600;border:3px solid rgba(255,255,255,0.8);box-shadow:0 2px 6px rgba(0,0,0,0.25);">${count}</div>`,
        className: '',
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2],
    })
}

function initMap() {
    if (mapInstance) {
        mapInstance.remove()
        mapInstance = null
    }

    const map = createMap(mapContainer.value, {
        center: [28.3949, 84.1240],
        zoom: 7,
    })
    mapInstance = map

    if (!hasValidCoords.value) return

    const clusterGroup = L.markerClusterGroup({
        iconCreateFunction: (cluster) => {
            const count = cluster.getChildCount()
            const markers = cluster.getAllChildMarkers()
            const statusCounts = {}
            markers.forEach((m) => {
                const s = m.options.status
                if (s) statusCounts[s] = (statusCounts[s] || 0) + 1
            })
            const dominantStatus = Object.keys(statusCounts).sort((a, b) => statusCounts[b] - statusCounts[a])[0]
            const color = STATUS_COLORS[dominantStatus] || '#6B7280'
            return createClusterIcon(color, count)
        },
        maxClusterRadius: 50,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        chunkedLoading: true,
    })

    const bounds = L.latLngBounds()

    complaintsWithCoords.value.forEach((c) => {
        const lat = parseFloat(c.latitude)
        const lng = parseFloat(c.longitude)
        const color = STATUS_COLORS[c.current_status] || '#6B7280'

        const marker = L.marker([lat, lng], {
            status: c.current_status,
            icon: L.divIcon({
                html: `<div style="width:14px;height:14px;border-radius:50%;background:${color};border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.3);"></div>`,
                className: '',
                iconSize: [14, 14],
                iconAnchor: [7, 7],
            }),
        })

        const content = `
            <div style="min-width:200px;">
                <div style="font-size:14px;font-weight:600;margin-bottom:4px;">${c.title}</div>
                <div style="font-size:12px;color:#6b7280;margin-bottom:6px;">
                    ${c.location || `${lat.toFixed(4)}, ${lng.toFixed(4)}`}
                </div>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:500;background:${color}20;color:${color};">
                        <span style="width:6px;height:6px;border-radius:50%;background:${color};display:inline-block;"></span>
                        ${c.current_status?.replace(/_/g, ' ')}
                    </span>
                    <span style="font-size:11px;color:#9ca3af;">${c.complaint_no}</span>
                </div>
                <a href="${route('complaints.show', c.id)}" style="display:inline-block;font-size:12px;font-weight:500;color:#6366F1;text-decoration:none;">
                    View Details →
                </a>
            </div>
        `

        marker.bindPopup(content)
        clusterGroup.addLayer(marker)
        bounds.extend([lat, lng])
    })

    map.addLayer(clusterGroup)

    if (bounds.isValid()) {
        map.fitBounds(bounds)
        if (map.getZoom() > 15) map.setZoom(15)
    }
}

watch(() => props.complaints, () => {
    initMap()
}, { deep: true })

onMounted(initMap)
</script>

<template>
    <div>
        <div class="relative" :style="{ height }">
            <div
                ref="mapContainer"
                class="rounded-lg border border-gray-200 overflow-hidden absolute inset-0"
                :class="{ 'opacity-0': complaints.length > 0 && !hasValidCoords }"
            ></div>
            <div
                v-if="complaints.length > 0 && !hasValidCoords"
                class="absolute inset-0 flex items-center justify-center bg-gray-50 rounded-lg border border-gray-200"
            >
                <p class="text-sm text-gray-400">No location data available for complaints</p>
            </div>
        </div>

        <div v-if="hasValidCoords" class="flex items-center gap-4 mt-3 text-xs text-gray-500">
            <div v-for="(color, status) in STATUS_COLORS" :key="status" class="flex items-center gap-1">
                <span class="w-2.5 h-2.5 rounded-full inline-block" :style="{ background: color }"></span>
                {{ status.replace(/_/g, ' ') }}
            </div>
        </div>
    </div>
</template>
