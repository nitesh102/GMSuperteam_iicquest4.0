<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useLeafletMap } from '@/Composables/useLeafletMap'

const { t } = useI18n()
const { L, createMap } = useLeafletMap()

const props = defineProps({
    latitude: { type: Number, default: null },
    longitude: { type: Number, default: null },
    height: { type: String, default: '400px' },
})

const emit = defineEmits(['update:latitude', 'update:longitude', 'update:reporterLatitude', 'update:reporterLongitude'])

const mapContainer = ref(null)
const map = ref(null)
const complaintMarker = ref(null)
const reporterMarker = ref(null)

const gpsStatus = ref('idle')
const gpsError = ref(null)
const reporterLat = ref(null)
const reporterLng = ref(null)
const distance = ref(null)
const mapReady = ref(false)

const searchQuery = ref('')
const searchResults = ref([])
const showResults = ref(false)
let searchTimeout = null

const defaultCenter = { lat: 28.3949, lng: 84.1240 }

const distanceText = computed(() => {
    if (distance.value === null) return null
    if (distance.value < 1000) return `${Math.round(distance.value)} m`
    return `${(distance.value / 1000).toFixed(2)} km`
})

function haversineDistance(lat1, lng1, lat2, lng2) {
    const R = 6371000
    const dLat = (lat2 - lat1) * Math.PI / 180
    const dLng = (lng2 - lng1) * Math.PI / 180
    const a = Math.sin(dLat / 2) ** 2
        + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
}

function captureGps() {
    if (!navigator.geolocation) {
        gpsStatus.value = 'error'
        gpsError.value = 'Geolocation is not supported by your browser.'
        return
    }

    gpsStatus.value = 'locating'
    gpsError.value = null

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            reporterLat.value = pos.coords.latitude
            reporterLng.value = pos.coords.longitude
            gpsStatus.value = 'done'

            // Emit reporter location to parent
            emit('update:reporterLatitude', pos.coords.latitude)
            emit('update:reporterLongitude', pos.coords.longitude)

            // Auto-set complaint location from GPS (create mode only)
            if (!props.latitude && !props.longitude) {
                emit('update:latitude', pos.coords.latitude)
                emit('update:longitude', pos.coords.longitude)
                updateComplaintMarker(pos.coords.latitude, pos.coords.longitude)
            }

            updateReporterMarker()
            updateDistance()
        },
        (err) => {
            gpsStatus.value = 'error'
            switch (err.code) {
                case err.PERMISSION_DENIED:
                    gpsError.value = 'Location access is required to submit a complaint. Please enable location permissions in your browser settings.'
                    break
                case err.POSITION_UNAVAILABLE:
                    gpsError.value = 'Location information is unavailable. Try again later.'
                    break
                case err.TIMEOUT:
                    gpsError.value = 'The request to get your location timed out. Please try again.'
                    break
                default:
                    gpsError.value = 'An unknown error occurred while getting your location.'
            }
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 }
    )
}

function updateReporterMarker() {
    if (!map.value || !reporterLat.value || !reporterLng.value) return
    const position = [reporterLat.value, reporterLng.value]

    if (reporterMarker.value) {
        reporterMarker.value.setLatLng(position)
    } else {
        reporterMarker.value = L.circleMarker(position, {
            radius: 8,
            fillColor: '#3B82F6',
            fillOpacity: 1,
            strokeColor: '#FFFFFF',
            weight: 2,
        }).addTo(map.value)
        reporterMarker.value.bindPopup('<div style="font-size:13px;font-weight:500">Your location (GPS)</div>')
    }

    map.value.setView(position, Math.max(map.value.getZoom(), 14))
}

function updateComplaintMarker(lat, lng) {
    const position = [lat, lng]

    if (complaintMarker.value) {
        complaintMarker.value.setLatLng(position)
    } else {
        complaintMarker.value = L.marker(position, {
            draggable: true,
        }).addTo(map.value)
        complaintMarker.value.bindPopup('<div style="font-size:13px;font-weight:500">Complaint Location</div>')
        complaintMarker.value.on('dragend', () => {
            const pos = complaintMarker.value.getLatLng()
            emit('update:latitude', pos.lat)
            emit('update:longitude', pos.lng)
            updateDistance()
        })
    }

    updateDistance()
}

function updateDistance() {
    if (reporterLat.value && reporterLng.value && props.latitude && props.longitude) {
        distance.value = haversineDistance(reporterLat.value, reporterLng.value, props.latitude, props.longitude)
    } else {
        distance.value = null
    }
}

function onMapClick(e) {
    const { lat, lng } = e.latlng
    emit('update:latitude', lat)
    emit('update:longitude', lng)
}

function retryGps() {
    gpsError.value = null
    captureGps()
}

async function doSearch() {
    const q = searchQuery.value.trim()
    if (q.length < 2) {
        searchResults.value = []
        return
    }
    try {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=5&countrycodes=NP`
        )
        const data = await res.json()
        searchResults.value = data
        showResults.value = data.length > 0
    } catch {
        searchResults.value = []
    }
}

function onSearchInput() {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(doSearch, 400)
}

function selectResult(place) {
    const lat = parseFloat(place.lat)
    const lng = parseFloat(place.lon)
    emit('update:latitude', lat)
    emit('update:longitude', lng)
    searchQuery.value = place.display_name
    showResults.value = false
    if (map.value) {
        map.value.setView([lat, lng], 15)
    }
    updateComplaintMarker(lat, lng)
}

function onSearchBlur() {
    setTimeout(() => { showResults.value = false }, 200)
}

function initMap() {
    const center = props.latitude && props.longitude
        ? [props.latitude, props.longitude]
        : [defaultCenter.lat, defaultCenter.lng]

    map.value = createMap(mapContainer.value, {
        center,
        zoom: 13,
    })
    mapReady.value = true

    map.value.on('click', onMapClick)

    if (props.latitude && props.longitude) {
        updateComplaintMarker(props.latitude, props.longitude)
    }

    captureGps()
}

watch(() => [props.latitude, props.longitude], ([lat, lng]) => {
    if (lat && lng && mapReady.value) {
        updateComplaintMarker(lat, lng)
        map.value.setView([lat, lng], Math.max(map.value.getZoom(), 14))
    }
})

onMounted(initMap)
</script>

<template>
    <div class="space-y-3">
        <div v-if="gpsStatus === 'error'" class="rounded-lg bg-red-50 border border-red-200 p-4">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-red-800">Location Required</p>
                    <p class="text-sm text-red-600 mt-1">{{ gpsError }}</p>
                    <button
                        @click="retryGps"
                        class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-red-700 hover:text-red-800"
                    >
                        <i class="fas fa-redo"></i>
                        Retry
                    </button>
                </div>
            </div>
        </div>

        <div v-if="gpsStatus === 'locating'" class="rounded-lg bg-blue-50 border border-blue-200 p-3 flex items-center gap-3">
            <i class="fas fa-spinner fa-spin text-blue-500"></i>
            <span class="text-sm text-blue-700">Detecting your location...</span>
        </div>

        <div v-if="gpsStatus === 'done'" class="rounded-lg bg-green-50 border border-green-200 p-3 flex items-center gap-3">
            <i class="fas fa-check-circle text-green-500"></i>
            <span class="text-sm text-green-700">Location detected</span>
            <span v-if="distanceText" class="ml-auto text-sm font-medium" :class="distance < 2000 ? 'text-green-700' : 'text-red-600'">
                {{ distance < 2000 ? '✅' : '⚠️' }} {{ distanceText }} from complaint
            </span>
        </div>

        <div class="relative">
            <div class="absolute top-3 left-3 right-3 z-10">
                <div class="relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        :placeholder="t('complaint.searchMap')"
                        @input="onSearchInput"
                        @blur="onSearchBlur"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 placeholder:text-gray-400"
                    />
                    <ul
                        v-if="showResults"
                        class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto z-20"
                    >
                        <li
                            v-for="place in searchResults"
                            :key="place.place_id"
                            @mousedown.prevent="selectResult(place)"
                            class="px-4 py-2.5 text-sm hover:bg-indigo-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                        >
                            <div class="text-gray-800">{{ place.display_name }}</div>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                ref="mapContainer"
                :style="{ height }"
                class="rounded-lg border border-gray-200 overflow-hidden"
            ></div>
        </div>

        <div class="flex items-center gap-4 text-xs text-gray-500">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-blue-500 border-2 border-white shadow-sm inline-block"></span>
                Your location
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3" style="filter: drop-shadow(0 1px 1px rgb(0 0 0 / 0.3));">
                    <svg viewBox="0 0 24 24" fill="#EF4444" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                </span>
                Complaint location
            </div>
            <div v-if="distanceText" class="ml-auto font-medium">
                Distance: {{ distanceText }}
            </div>
        </div>
    </div>
</template>
