<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    complaint:   Object,
    departments: Array,
    categories:  Array,
})

const flashMessage = ref(null)
let flashTimer = null
watch(() => usePage().props.flash?.success, (val) => {
    if (flashTimer) clearTimeout(flashTimer)
    flashMessage.value = val || null
    if (flashMessage.value) flashTimer = setTimeout(() => flashMessage.value = null, 4000)
}, { immediate: true })

const LIFECYCLE = ['submitted', 'under_review', 'assigned', 'in_progress', 'resolved', 'closed']

const STATUS_LABELS = {
    submitted:    'Submitted',
    under_review: 'Under Review',
    assigned:     'Assigned',
    in_progress:  'In Progress',
    resolved:     'Resolved',
    rejected:     'Rejected',
    closed:       'Closed',
}

function statusLabel(s) { return STATUS_LABELS[s] ?? s }

function statusColor(s) {
    return {
        submitted:    { dot: 'bg-blue-500',   badge: 'bg-blue-50 text-blue-700 ring-blue-600/20'   },
        under_review: { dot: 'bg-yellow-500', badge: 'bg-yellow-50 text-yellow-700 ring-yellow-600/20' },
        assigned:     { dot: 'bg-purple-500', badge: 'bg-purple-50 text-purple-700 ring-purple-600/20' },
        in_progress:  { dot: 'bg-indigo-500', badge: 'bg-indigo-50 text-indigo-700 ring-indigo-600/20' },
        resolved:     { dot: 'bg-green-500',  badge: 'bg-green-50 text-green-700 ring-green-600/20'   },
        rejected:     { dot: 'bg-red-500',    badge: 'bg-red-50 text-red-700 ring-red-600/20'         },
        closed:       { dot: 'bg-gray-400',   badge: 'bg-gray-50 text-gray-600 ring-gray-500/20'      },
    }[s] ?? { dot: 'bg-gray-400', badge: 'bg-gray-50 text-gray-600 ring-gray-500/20' }
}

function priorityBadge(p) {
    return {
        low:       'bg-gray-50 text-gray-600 ring-gray-500/20',
        medium:    'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
        high:      'bg-orange-50 text-orange-700 ring-orange-600/20',
        emergency: 'bg-red-50 text-red-700 ring-red-600/20',
    }[p] ?? 'bg-gray-50 text-gray-600 ring-gray-500/20'
}

function priorityIcon(p) {
    return { low: 'fa-arrow-down', medium: 'fa-minus', high: 'fa-arrow-up', emergency: 'fa-exclamation-circle' }[p] ?? 'fa-minus'
}

function formatDate(d) {
    if (!d) return ''
    return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

function formatDateTime(d) {
    if (!d) return ''
    return new Date(d).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' })
}

function relativeTime(d) {
    if (!d) return ''
    const diff = Date.now() - new Date(d).getTime()
    const mins = Math.floor(diff / 60000)
    if (mins < 1) return 'Just now'
    if (mins < 60) return `${mins}m ago`
    const hrs = Math.floor(mins / 60)
    if (hrs < 24) return `${hrs}h ago`
    const days = Math.floor(hrs / 24)
    if (days < 7) return `${days}d ago`
    if (days < 30) return `${Math.floor(days / 7)}w ago`
    return formatDate(d)
}

function groupByDate(items) {
    const groups = {}
    const today = new Date().toDateString()
    const yesterday = new Date(Date.now() - 86400000).toDateString()
    items.forEach(item => {
        const d = new Date(item.created_at).toDateString()
        let label
        if (d === today) label = 'Today'
        else if (d === yesterday) label = 'Yesterday'
        else label = formatDate(item.created_at)
        if (!groups[label]) groups[label] = []
        groups[label].push(item)
    })
    return groups
}

const lifecycleStep = computed(() => {
    const s = props.complaint.current_status
    if (s === 'rejected') return -1
    return LIFECYCLE.indexOf(s)
})

const activityGroups = computed(() => props.complaint.tracks?.length ? groupByDate(props.complaint.tracks) : {})

const copied = ref(false)
const coordsCopied = ref(false)
function copyComplaintNo() {
    navigator.clipboard?.writeText(props.complaint.complaint_no).then(() => {
        copied.value = true
        setTimeout(() => copied.value = false, 2000)
    })
}
function copyCoords() {
    if (!props.complaint.latitude || !props.complaint.longitude) return
    navigator.clipboard?.writeText(`${props.complaint.latitude},${props.complaint.longitude}`).then(() => {
        coordsCopied.value = true
        setTimeout(() => coordsCopied.value = false, 2000)
    })
}

const lightboxOpen = ref(false)
const lightboxIndex = ref(0)
const allImages = computed(() => {
    const imgs = []
    if (props.complaint.attachments?.length) {
        props.complaint.attachments.forEach(a => imgs.push({ src: '/storage/' + a.file_path, alt: a.file_name || 'Attachment' }))
    }
    if (props.complaint.before_photo) imgs.push({ src: '/storage/' + props.complaint.before_photo, alt: 'Before photo' })
    if (props.complaint.after_photo) imgs.push({ src: '/storage/' + props.complaint.after_photo, alt: 'After photo' })
    return imgs
})

function openLightbox(idx) { lightboxIndex.value = idx; lightboxOpen.value = true }
function closeLightbox() { lightboxOpen.value = false }
function prevImage() { lightboxIndex.value = (lightboxIndex.value - 1 + allImages.value.length) % allImages.value.length }
function nextImage() { lightboxIndex.value = (lightboxIndex.value + 1) % allImages.value.length }

function handleKeydown(e) {
    if (!lightboxOpen.value) return
    if (e.key === 'Escape') closeLightbox()
    if (e.key === 'ArrowLeft') prevImage()
    if (e.key === 'ArrowRight') nextImage()
}

onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))

const visibleSections = ref(new Set())
function observeSection(el) {
    if (!el) return
    const obs = new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting) {
            visibleSections.value.add(entry.target.dataset.section)
            obs.unobserve(entry.target)
        }
    }, { threshold: 0.1 })
    obs.observe(el)
}

const ELA = computed(() => {
    if (!props.complaint.created_at) return null
    const ms = Date.now() - new Date(props.complaint.created_at).getTime()
    const days = Math.floor(ms / 86400000)
    const hrs = Math.floor((ms % 86400000) / 3600000)
    if (days > 0) return `${days}d ${hrs}h`
    return `${hrs}h`
})

const dayCount = computed(() => {
    if (!props.complaint.created_at) return 0
    return Math.ceil((Date.now() - new Date(props.complaint.created_at).getTime()) / 86400000)
})
</script>

<template>
    <Head :title="`${complaint.complaint_no} — Complaint`" />

    <Teleport to="body">
        <div v-if="flashMessage" class="fixed top-5 right-5 z-[100] animate-slide-in" @click="flashMessage = null">
            <div class="bg-white border border-green-200 rounded-xl shadow-lg px-5 py-3.5 flex items-center gap-3 cursor-pointer">
                <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500 text-sm"></i>
                </div>
                <p class="text-sm font-medium text-gray-800">{{ flashMessage }}</p>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="lightboxOpen && allImages.length" class="fixed inset-0 z-[200] bg-black/90 flex items-center justify-center select-none" @click.self="closeLightbox">
                <button @click="closeLightbox" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors z-10">
                    <i class="fas fa-times text-sm"></i>
                </button>
                <button v-if="allImages.length > 1" @click="prevImage" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors z-10">
                    <i class="fas fa-chevron-left text-sm"></i>
                </button>
                <button v-if="allImages.length > 1" @click="nextImage" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors z-10">
                    <i class="fas fa-chevron-right text-sm"></i>
                </button>
                <div class="absolute top-4 left-1/2 -translate-x-1/2 px-3 py-1.5 rounded-full bg-white/10 text-white text-xs font-medium z-10">
                    {{ lightboxIndex + 1 }} / {{ allImages.length }}
                </div>
                <img :src="allImages[lightboxIndex].src" :alt="allImages[lightboxIndex].alt" class="max-h-[85vh] max-w-[90vw] object-contain rounded-lg shadow-2xl" />
            </div>
        </Teleport>
    </Teleport>

    <AdminLayout>
        <div class="min-h-[calc(100vh-64px)] flex flex-col -m-4 sm:-m-6">
            <div class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-4 sm:space-y-6">

                <nav class="flex items-center gap-2 text-xs text-gray-400 mb-2">
                    <Link :href="route('citizen.dashboard')" class="hover:text-gray-600 transition-colors">Dashboard</Link>
                    <i class="fas fa-chevron-right" style="font-size:6px"></i>
                    <Link :href="route('citizen.complaints.index')" class="hover:text-gray-600 transition-colors">Complaints</Link>
                    <i class="fas fa-chevron-right" style="font-size:6px"></i>
                    <span class="text-gray-600 font-medium truncate max-w-[120px]">{{ complaint.complaint_no }}</span>
                </nav>

                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-6" data-section="header">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <Link
                                :href="route('citizen.complaints.index')"
                                class="mt-0.5 p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors flex-shrink-0 hidden sm:flex"
                            >
                                <i class="fas fa-arrow-left text-sm"></i>
                            </Link>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-mono text-gray-400">{{ complaint.complaint_no }}</span>
                                    <button @click="copyComplaintNo" class="p-1 rounded-md hover:bg-gray-100 text-gray-300 hover:text-gray-500 transition-colors" title="Copy complaint number">
                                        <i v-if="!copied" class="fas fa-copy text-[10px]"></i>
                                        <i v-else class="fas fa-check text-[10px] text-green-500"></i>
                                    </button>
                                </div>
                                <h1 class="text-lg sm:text-xl font-bold text-gray-900 leading-tight break-words">{{ complaint.title }}</h1>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset capitalize" :class="priorityBadge(complaint.priority)">
                                <i class="fas text-[10px]" :class="priorityIcon(complaint.priority)"></i>
                                {{ complaint.priority === 'emergency' ? 'Emergency' : complaint.priority }}
                            </span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset relative" :class="statusColor(complaint.current_status).badge">
                                <span class="relative flex h-1.5 w-1.5 mr-1.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="statusColor(complaint.current_status).dot"></span>
                                    <span class="relative inline-flex rounded-full h-1.5 w-1.5" :class="statusColor(complaint.current_status).dot"></span>
                                </span>
                                {{ statusLabel(complaint.current_status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                    <div class="lg:col-span-2 space-y-4 sm:space-y-5">

                        <div data-section="details-card" :ref="observeSection" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden transition-all duration-500 divide-y divide-gray-100" :class="visibleSections.has('details-card') ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">

                            <div v-if="complaint.aiAnalysis" class="p-4 sm:p-5">
                                <details class="group" open>
                                    <summary class="flex items-center gap-3 cursor-pointer list-none select-none">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h2 class="text-sm font-semibold text-indigo-800">AI Analysis</h2>
                                            <p class="text-xs text-indigo-500">Powered by Gemini 2.0 Flash</p>
                                        </div>
                                        <span v-if="complaint.aiAnalysis?.confidence_score" class="inline-flex items-center gap-1.5 rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 flex-shrink-0">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ (complaint.aiAnalysis.confidence_score * 100).toFixed(0) }}%
                                        </span>
                                        <i class="fas fa-chevron-down text-indigo-300 group-open:rotate-180 transition-transform text-xs"></i>
                                    </summary>
                                    <div class="mt-3 space-y-3">
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            <div class="bg-indigo-50/50 rounded-lg px-3.5 py-3 border border-indigo-100">
                                                <p class="text-xs text-indigo-400 mb-0.5 font-medium">Department</p>
                                                <p class="text-sm font-semibold text-indigo-900">{{ complaint.aiAnalysis.detected_category || '—' }}</p>
                                            </div>
                                            <div class="bg-indigo-50/50 rounded-lg px-3.5 py-3 border border-indigo-100">
                                                <p class="text-xs text-indigo-400 mb-0.5 font-medium">Category</p>
                                                <p class="text-sm font-semibold text-indigo-900">{{ complaint.category?.name || '—' }}</p>
                                            </div>
                                            <div class="bg-indigo-50/50 rounded-lg px-3.5 py-3 border border-indigo-100">
                                                <p class="text-xs text-indigo-400 mb-0.5 font-medium">Priority</p>
                                                <p class="text-sm font-semibold text-indigo-900 capitalize">{{ complaint.priority }}</p>
                                            </div>
                                        </div>
                                        <div v-if="complaint.ai_summary" class="bg-indigo-50/50 rounded-lg px-3.5 py-3 border border-indigo-100">
                                            <p class="text-xs text-indigo-400 mb-0.5 font-medium">Summary</p>
                                            <p class="text-sm text-indigo-800 leading-relaxed">{{ complaint.ai_summary }}</p>
                                        </div>
                                    </div>
                                </details>
                            </div>

                            <div class="p-4 sm:p-5">
                                <details class="group" :open="!!(complaint.attachments?.length || complaint.before_photo || complaint.after_photo)">
                                    <summary class="flex items-center gap-2 cursor-pointer list-none select-none">
                                        <i class="fas fa-images text-gray-400 text-xs"></i>
                                        <h2 class="text-sm font-semibold text-gray-700">Photos</h2>
                                        <span class="text-xs font-normal text-gray-400">({{ allImages.length }})</span>
                                        <i class="fas fa-chevron-down text-gray-300 ml-auto group-open:rotate-180 transition-transform text-xs"></i>
                                    </summary>
                                    <div class="mt-3" v-if="allImages.length">
                                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 sm:gap-3">
                                            <button v-for="(img, idx) in allImages" :key="idx" @click="openLightbox(idx)" class="group relative aspect-square rounded-xl overflow-hidden border border-gray-200 bg-gray-50 hover:shadow-md hover:border-gray-300 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                                <img :src="img.src" :alt="img.alt" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors flex items-center justify-center">
                                                    <i class="fas fa-expand text-white opacity-0 group-hover:opacity-100 transition-opacity text-sm drop-shadow-lg"></i>
                                                </div>
                                            </button>
                                        </div>
                                        <button @click="openLightbox(0)" class="mt-3 text-xs text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1.5">
                                            <i class="fas fa-expand text-[10px]"></i>
                                            View all {{ allImages.length }} photos
                                        </button>
                                    </div>
                                    <div v-else class="mt-3">
                                        <div class="flex flex-col items-center py-6 text-gray-300">
                                            <i class="fas fa-image text-2xl mb-2"></i>
                                            <p class="text-sm text-gray-400">No photos attached</p>
                                        </div>
                                    </div>
                                </details>
                            </div>

                            <div class="p-4 sm:p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                        <i class="fas fa-map-marker-alt text-gray-400 text-xs"></i>
                                        Location
                                    </h2>
                                    <a v-if="complaint.latitude && complaint.longitude" :href="`https://www.google.com/maps?q=${complaint.latitude},${complaint.longitude}`" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                        Open in Maps
                                    </a>
                                </div>
                                <div v-if="complaint.location" class="flex items-center gap-2 mb-3">
                                    <i class="fas fa-map-pin text-gray-300 text-xs"></i>
                                    <p class="text-sm text-gray-700">{{ complaint.location }}</p>
                                </div>
                                <div v-if="complaint.latitude && complaint.longitude" class="rounded-xl overflow-hidden border border-gray-200 bg-gray-50">
                                    <iframe
                                        :src="`https://www.openstreetmap.org/export/embed.html?bbox=${complaint.longitude - 0.01}%2C${complaint.latitude - 0.01}%2C${complaint.longitude + 0.01}%2C${complaint.latitude + 0.01}&layer=mapnik&marker=${complaint.latitude}%2C${complaint.longitude}`"
                                        class="w-full h-40 sm:h-52 border-0"
                                        loading="lazy"
                                        referrerpolicy="no-referrer"
                                    ></iframe>
                                    <div class="px-3 py-2 bg-white border-t border-gray-100 flex items-center justify-between">
                                        <span class="text-xs text-gray-400 font-mono">{{ parseFloat(complaint.latitude).toFixed(4) }}, {{ parseFloat(complaint.longitude).toFixed(4) }}</span>
                                        <button @click="copyCoords" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1">
                                            <i v-if="!coordsCopied" class="fas fa-copy text-[10px]"></i>
                                            <i v-else class="fas fa-check text-[10px] text-green-500"></i>
                                            {{ coordsCopied ? 'Copied' : 'Copy' }}
                                        </button>
                                    </div>
                                </div>
                                <div v-else-if="complaint.location" class="text-xs text-gray-400">
                                    <p>Coordinates not available</p>
                                </div>
                            </div>

                            <div v-if="complaint.resolved_at || complaint.resolution_notes || complaint.before_photo || complaint.after_photo" class="p-4 sm:p-5">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                        <i class="fas fa-check-circle text-green-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-sm font-semibold text-green-800">Resolution</h2>
                                        <p v-if="complaint.resolved_at" class="text-xs text-green-600">{{ relativeTime(complaint.resolved_at) }}</p>
                                    </div>
                                </div>
                                <div v-if="complaint.resolution_notes" class="mb-4">
                                    <p class="text-sm text-green-800 leading-relaxed bg-green-50/50 rounded-lg px-4 py-3 border border-green-100">{{ complaint.resolution_notes }}</p>
                                </div>
                                <div v-if="complaint.before_photo || complaint.after_photo" class="grid grid-cols-2 gap-3">
                                    <div v-if="complaint.before_photo" class="space-y-1.5">
                                        <p class="text-xs font-medium text-gray-500 flex items-center gap-1">
                                            <i class="fas fa-clock-rotate-left text-[10px]"></i>
                                            Before
                                        </p>
                                        <button @click="openLightbox(allImages.findIndex(i => i.src.includes(complaint.before_photo)))" class="block w-full aspect-video rounded-lg overflow-hidden border border-green-200 bg-green-50/50 hover:shadow-md transition-shadow group">
                                            <img :src="'/storage/' + complaint.before_photo" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Before photo" loading="lazy" />
                                        </button>
                                    </div>
                                    <div v-if="complaint.after_photo" class="space-y-1.5">
                                        <p class="text-xs font-medium text-gray-500 flex items-center gap-1">
                                            <i class="fas fa-check text-[10px]"></i>
                                            After
                                        </p>
                                        <button @click="openLightbox(allImages.findIndex(i => i.src.includes(complaint.after_photo)))" class="block w-full aspect-video rounded-lg overflow-hidden border border-green-200 bg-green-50/50 hover:shadow-md transition-shadow group">
                                            <img :src="'/storage/' + complaint.after_photo" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="After photo" loading="lazy" />
                                        </button>
                                    </div>
                                </div>
                                <p v-if="complaint.resolved_at" class="text-xs text-green-600 mt-3 flex items-center gap-1.5">
                                    <i class="fas fa-calendar-check text-[10px]"></i>
                                    Resolved {{ formatDateTime(complaint.resolved_at) }}
                                </p>
                            </div>

                            <div v-if="!complaint.aiAnalysis && !complaint.attachments?.length && !complaint.before_photo && !complaint.after_photo && !complaint.location && !complaint.latitude && !complaint.longitude" class="p-4 sm:p-5">
                                <div class="flex flex-col items-center py-8 text-gray-300">
                                    <i class="fas fa-inbox text-3xl mb-3"></i>
                                    <p class="text-sm font-medium text-gray-400">No additional details yet</p>
                                    <p class="text-xs text-gray-300 mt-1">Additional information will appear here as it becomes available.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 sm:space-y-5">
                        <div data-section="details" :ref="observeSection" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5 transition-all duration-500 lg:sticky lg:top-6" :class="visibleSections.has('details') ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                            <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                <i class="fas fa-info-circle text-gray-400 text-xs"></i>
                                Details
                            </h2>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-center justify-between gap-3 py-2 border-b border-gray-50 last:border-0">
                                    <span class="text-gray-400 flex-shrink-0 text-xs">Status</span>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset" :class="statusColor(complaint.current_status).badge">
                                        {{ statusLabel(complaint.current_status) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between gap-3 py-2 border-b border-gray-50 last:border-0">
                                    <span class="text-gray-400 flex-shrink-0 text-xs">Priority</span>
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset capitalize" :class="priorityBadge(complaint.priority)">
                                        <i class="fas text-[8px]" :class="priorityIcon(complaint.priority)"></i>
                                        {{ complaint.priority === 'emergency' ? 'Emergency' : complaint.priority }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between gap-3 py-2 border-b border-gray-50 last:border-0">
                                    <span class="text-gray-400 flex-shrink-0 text-xs">Department</span>
                                    <span v-if="complaint.department?.name || complaint.category?.department?.name" class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                                        <i class="fas fa-building text-[10px]"></i>
                                        {{ complaint.department?.name || complaint.category?.department?.name }}
                                    </span>
                                    <span v-else class="text-gray-300 text-xs">—</span>
                                </div>
                                <div class="flex items-center justify-between gap-3 py-2 border-b border-gray-50 last:border-0">
                                    <span class="text-gray-400 flex-shrink-0 text-xs">Category</span>
                                    <span class="text-gray-700 text-xs text-right max-w-[140px] truncate">{{ complaint.category?.name || '—' }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-3 py-2 border-b border-gray-50 last:border-0">
                                    <span class="text-gray-400 flex-shrink-0 text-xs">Created</span>
                                    <span class="text-gray-700 text-xs text-right">{{ formatDate(complaint.created_at) }}</span>
                                </div>
                                <div v-if="dayCount > 0" class="flex items-center justify-between gap-3 py-2 border-b border-gray-50 last:border-0">
                                    <span class="text-gray-400 flex-shrink-0 text-xs">Age</span>
                                    <span class="text-gray-700 text-xs font-medium">{{ ELA }}</span>
                                </div>
                                <div v-if="complaint.resolved_at" class="flex items-center justify-between gap-3 py-2 border-b border-gray-50 last:border-0">
                                    <span class="text-gray-400 flex-shrink-0 text-xs">Resolved</span>
                                    <span class="text-gray-700 text-xs text-right">{{ formatDate(complaint.resolved_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <div data-section="lifecycle" :ref="observeSection" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5 transition-all duration-500" :class="visibleSections.has('lifecycle') ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                            <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                <i class="fas fa-circle-nodes text-gray-400 text-xs"></i>
                                Lifecycle
                            </h2>
                            <div v-if="complaint.current_status === 'rejected'" class="flex items-center gap-3 p-4 bg-red-50 rounded-xl border border-red-100">
                                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-xmark text-red-500 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-red-700">Rejected</p>
                                    <p class="text-xs text-red-500 mt-0.5">This complaint was rejected and will not be processed.</p>
                                </div>
                            </div>
                            <div v-else class="space-y-0">
                                <div v-for="(step, idx) in LIFECYCLE" :key="step" class="flex items-start gap-3 group">
                                    <div class="flex flex-col items-center flex-shrink-0">
                                        <div :class="[
                                            'w-7 h-7 rounded-full flex items-center justify-center border-2 transition-all duration-500',
                                            idx < lifecycleStep
                                                ? 'bg-indigo-600 border-indigo-600 scale-100'
                                                : idx === lifecycleStep
                                                    ? statusColor(step).dot + ' border-transparent ring-4 ring-offset-2 ring-indigo-200 animate-pulse'
                                                    : 'bg-white border-gray-200 group-hover:border-gray-300'
                                        ]">
                                            <i v-if="idx < lifecycleStep" class="fas fa-check text-white" style="font-size:9px"></i>
                                            <div v-else-if="idx === lifecycleStep" class="w-2.5 h-2.5 rounded-full bg-white"></div>
                                        </div>
                                        <div v-if="idx < LIFECYCLE.length - 1" :class="['w-0.5 h-6 sm:h-8 my-0.5 rounded transition-all duration-500', idx < lifecycleStep ? 'bg-indigo-400' : 'bg-gray-200']"></div>
                                    </div>
                                    <div class="pb-2 pt-0.5 min-w-0">
                                        <p :class="[
                                            'text-sm font-medium leading-tight transition-colors duration-300',
                                            idx < lifecycleStep  ? 'text-indigo-600' :
                                            idx === lifecycleStep ? 'text-gray-900' :
                                            'text-gray-400'
                                        ]">{{ statusLabel(step) }}</p>
                                        <p v-if="idx === lifecycleStep" class="text-xs text-indigo-500 font-medium mt-0.5 flex items-center gap-1">
                                            <span class="w-1 h-1 rounded-full bg-indigo-500 animate-pulse"></span>
                                            Current stage
                                        </p>
                                        <p v-if="idx < lifecycleStep && idx === lifecycleStep - 1" class="text-xs text-gray-400 mt-0.5">{{ relativeTime(complaint.tracks?.find(t => t.new_status === step)?.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="complaint.description" data-section="desc" :ref="observeSection" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5 transition-all duration-500 lg:hidden" :class="visibleSections.has('desc') ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                            <h2 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-align-left text-gray-400 text-xs"></i>
                                Description
                            </h2>
                            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap break-words">{{ complaint.description }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="complaint.tracks?.length" data-section="activity" :ref="observeSection" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden transition-all duration-500" :class="visibleSections.has('activity') ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                    <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fas fa-timeline text-gray-400 text-xs"></i>
                            Activity History
                            <span class="text-xs font-normal text-gray-400">({{ complaint.tracks.length }})</span>
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <div v-for="(events, dateLabel) in activityGroups" :key="dateLabel">
                            <div class="px-4 sm:px-5 py-2 bg-gray-50/50 border-b border-gray-100">
                                <p class="text-xs font-semibold text-gray-500">{{ dateLabel }}</p>
                            </div>
                            <div class="relative">
                                <div class="absolute left-[21px] sm:left-[25px] top-0 bottom-0 w-0.5 bg-gray-100"></div>
                                <div class="space-y-0 divide-y divide-gray-50">
                                    <div v-for="track in events" :key="track.id" class="relative pl-12 sm:pl-14 pr-4 sm:pr-5 py-4 hover:bg-gray-50/50 transition-colors">
                                        <div :class="['absolute left-[10px] sm:left-[14px] top-4 w-[22px] h-[22px] sm:w-[24px] sm:h-[24px] rounded-full flex items-center justify-center z-10 ring-4 ring-white', statusColor(track.new_status).dot]">
                                            <i class="fas fa-arrow-right text-white" style="font-size:7px"></i>
                                        </div>
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                                            <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset" :class="statusColor(track.old_status).badge">{{ statusLabel(track.old_status) }}</span>
                                                <i class="fas fa-chevron-right text-gray-300 text-[9px] flex-shrink-0"></i>
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset" :class="statusColor(track.new_status).badge">{{ statusLabel(track.new_status) }}</span>
                                            </div>
                                            <span class="text-xs text-gray-400 sm:ml-auto whitespace-nowrap flex-shrink-0">{{ relativeTime(track.created_at) }}</span>
                                        </div>
                                        <p v-if="track.remarks" class="mt-2 text-sm text-gray-600 leading-relaxed border-l-2 border-indigo-200 pl-3 ml-0.5">{{ track.remarks }}</p>
                                        <div class="flex items-center gap-1.5 mt-2 text-xs text-gray-400">
                                            <i class="fas fa-user-circle text-gray-300 text-[11px]"></i>
                                            <span>{{ track.changer?.name ?? 'System' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!complaint.tracks?.length && complaint.current_status !== 'rejected'" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5">
                    <div class="flex flex-col items-center py-8 text-gray-300">
                        <i class="fas fa-clock text-3xl mb-3"></i>
                        <p class="text-sm font-medium text-gray-400">No activity yet</p>
                        <p class="text-xs text-gray-300 mt-1">Activity will appear here as your complaint is processed.</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes slide-in {
    from { transform: translateX(100%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
.animate-slide-in { animation: slide-in 0.3s ease-out; }

details > summary { -webkit-user-select: none; user-select: none; }
details > summary::-webkit-details-marker { display: none; }

@media (prefers-reduced-motion: reduce) {
    .animate-pulse { animation: none; }
    *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
}
</style>
