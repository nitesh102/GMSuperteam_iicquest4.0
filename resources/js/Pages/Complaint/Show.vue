<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    complaint:   Object,
    departments: Array,
    categories:  Array,
})

const page = usePage()
const isAdmin = computed(() => (page.props.auth?.roles ?? []).includes('Superadmin'))

const flashMessage = ref(null)
let flashTimer = null
watch(() => usePage().props.flash?.success, (val) => {
    if (flashTimer) clearTimeout(flashTimer)
    flashMessage.value = val || null
    if (flashMessage.value) flashTimer = setTimeout(() => flashMessage.value = null, 4000)
}, { immediate: true })

const STATUS_TRANSITIONS = {
    submitted:    ['under_review', 'rejected'],
    under_review: ['assigned', 'rejected', 'submitted'],
    assigned:     ['in_progress', 'under_review', 'rejected'],
    in_progress:  ['resolved', 'rejected', 'assigned'],
    resolved:     ['closed', 'in_progress'],
    rejected:     ['submitted'],
    closed:       [],
}

const STATUS_LABELS = {
    submitted:    'Submitted',
    under_review: 'Under Review',
    assigned:     'Assigned',
    in_progress:  'In Progress',
    resolved:     'Resolved',
    rejected:     'Rejected',
    closed:       'Closed',
}

const LIFECYCLE = ['submitted', 'under_review', 'assigned', 'in_progress', 'resolved', 'closed']

const showStatusForm = ref(false)
const beforePhotoFile = ref(null)
const afterPhotoFile  = ref(null)

const form = useForm({
    title:            props.complaint.title,
    description:      props.complaint.description,
    category_id:      props.complaint.category_id,
    current_status:   props.complaint.current_status,
    priority:         props.complaint.priority,
    location:         props.complaint.location ?? '',
    latitude:         props.complaint.latitude ?? '',
    longitude:        props.complaint.longitude ?? '',
    assigned_to:      props.complaint.assigned_to ?? null,
    resolution_notes: props.complaint.resolution_notes ?? '',
    remarks:          '',
})

const allowedNextStatuses = computed(() =>
    STATUS_TRANSITIONS[props.complaint.current_status] ?? []
)

function submitStatusChange() {
    const hasFiles = beforePhotoFile.value || afterPhotoFile.value

    if (hasFiles) {
        form.transform((data) => {
            const fd = new FormData()
            Object.entries(data).forEach(([key, val]) => {
                if (val !== null && val !== undefined) fd.append(key, val)
            })
            if (beforePhotoFile.value) fd.append('before_photo', beforePhotoFile.value)
            if (afterPhotoFile.value)  fd.append('after_photo', afterPhotoFile.value)
            fd.append('_method', 'PUT')
            return fd
        })

        form.post(route('complaints.update', props.complaint.id), {
            preserveScroll: true,
            onSuccess: () => {
                showStatusForm.value = false
                form.remarks = ''
                beforePhotoFile.value = null
                afterPhotoFile.value  = null
            },
        })
    } else {
        form.put(route('complaints.update', props.complaint.id), {
            preserveScroll: true,
            onSuccess: () => {
                showStatusForm.value = false
                form.remarks = ''
            },
        })
    }
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

function priorityColor(p) {
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
    if (!d) return '—'
    return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

function formatDateTime(d) {
    if (!d) return '—'
    return new Date(d).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' })
}

function dueStatus() {
    if (!props.complaint.due_at) return null
    const diff = new Date(props.complaint.due_at) - new Date()
    if (diff < 0) return 'overdue'
    if (diff < 3600000) return 'critical'
    if (diff < 86400000) return 'warning'
    return 'ok'
}

function dueLabel() {
    if (!props.complaint.due_at) return null
    const diff = new Date(props.complaint.due_at) - new Date()
    if (diff < 0) {
        const h = Math.round(Math.abs(diff) / 3600000)
        return h < 24 ? `Overdue by ${h}h` : `Overdue by ${Math.round(h/24)}d`
    }
    const h = Math.round(diff / 3600000)
    return h < 24 ? `Due in ${h}h` : `Due in ${Math.round(h/24)}d`
}

function getGoogleMapsLink(lat, lng) {
    return `https://www.google.com/maps?q=${lat},${lng}`
}

const lifecycleStep = computed(() => {
    const s = props.complaint.current_status
    if (s === 'rejected') return -1
    return LIFECYCLE.indexOf(s)
})
</script>

<template>
    <Head :title="`${complaint.complaint_no} — Complaint`" />

    <teleport to="body">
        <div v-if="flashMessage" class="fixed top-5 right-5 z-[100] animate-slide-in" @click="flashMessage = null">
            <div class="bg-white border border-green-200 rounded-xl shadow-lg px-5 py-3.5 flex items-center gap-3 cursor-pointer">
                <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500 text-sm"></i>
                </div>
                <p class="text-sm font-medium text-gray-800">{{ flashMessage }}</p>
            </div>
        </div>
    </teleport>

    <AdminLayout>
        <div class="min-h-[calc(100vh-64px)] flex flex-col -m-4 sm:-m-6">
            <div class="flex-1 w-full px-4 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-6">

                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <Link
                            :href="route('complaints.index')"
                            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors flex-shrink-0"
                        >
                            <i class="fas fa-arrow-left text-sm"></i>
                        </Link>
                        <div>
                            <p class="text-xs font-mono text-gray-400">{{ complaint.complaint_no }}</p>
                            <h1 class="text-xl font-bold text-gray-900 mt-0.5 leading-tight">{{ complaint.title }}</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span
                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                            :class="priorityColor(complaint.priority)"
                        >
                            <i class="fas text-xs" :class="priorityIcon(complaint.priority)"></i>
                            {{ complaint.priority }}
                        </span>
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                            :class="statusColor(complaint.current_status).badge"
                        >
                            {{ statusLabel(complaint.current_status) }}
                        </span>
                    </div>
                </div>

                <div
                    v-if="complaint.due_at && !['closed','rejected'].includes(complaint.current_status)"
                    :class="[
                        'rounded-xl px-4 py-3 flex items-center justify-between gap-4',
                        dueStatus() === 'overdue'  ? 'bg-red-50 border border-red-200' :
                        dueStatus() === 'critical' ? 'bg-orange-50 border border-orange-200' :
                        dueStatus() === 'warning'  ? 'bg-yellow-50 border border-yellow-200' :
                        'bg-gray-50 border border-gray-200'
                    ]"
                >
                    <div class="flex items-center gap-2.5">
                        <i class="fas text-sm" :class="[
                            dueStatus() === 'overdue'  ? 'fa-exclamation-circle text-red-500' :
                            dueStatus() === 'critical' ? 'fa-exclamation-triangle text-orange-500' :
                            dueStatus() === 'warning'  ? 'fa-clock text-yellow-500' :
                            'fa-clock text-gray-400'
                        ]"></i>
                        <span class="text-sm font-medium"
                            :class="dueStatus() === 'overdue' ? 'text-red-700' : dueStatus() === 'critical' ? 'text-orange-700' : dueStatus() === 'warning' ? 'text-yellow-700' : 'text-gray-600'"
                        >{{ dueLabel() }}</span>
                        <span class="text-xs text-gray-400">(SLA deadline: {{ formatDateTime(complaint.due_at) }})</span>
                    </div>
                    <div v-if="complaint.escalation_level > 0" class="flex items-center gap-1.5 text-xs font-medium text-orange-600">
                        <i class="fas fa-level-up-alt"></i>
                        Escalated {{ complaint.escalation_level }}×
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-5">

                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                            <h2 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-align-left text-gray-400 text-xs"></i>
                                Description
                            </h2>
                            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ complaint.description }}</p>
                        </div>

                        <!-- ── AI Analysis Card ────────────────────────────── -->
                        <div
                            class="rounded-xl border shadow-sm p-5"
                            :class="complaint.aiAnalysis ? 'bg-indigo-50 border-indigo-200' : 'bg-gray-50 border-gray-200'"
                        >
                            <div class="flex items-center gap-2.5 mb-4">
                                <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-sm font-semibold" :class="complaint.aiAnalysis ? 'text-indigo-800' : 'text-gray-500'">AI Analysis</h2>
                                    <p class="text-xs" :class="complaint.aiAnalysis ? 'text-indigo-600' : 'text-gray-400'">
                                        {{ complaint.aiAnalysis ? 'Powered by Gemini 2.0 Flash' : 'AI analysis pending' }}
                                    </p>
                                </div>
                                <span v-if="complaint.aiAnalysis?.confidence_score"
                                    class="ml-auto inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700"
                                >
                                    {{ (complaint.aiAnalysis.confidence_score * 100).toFixed(0) }}% confidence
                                </span>
                            </div>

                            <div v-if="complaint.aiAnalysis" class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                                <div class="bg-white/70 rounded-lg px-3 py-2.5 border border-indigo-100">
                                    <p class="text-xs text-indigo-500 mb-0.5">Department</p>
                                    <p class="text-sm font-semibold text-indigo-900">{{ complaint.aiAnalysis.detected_category || '—' }}</p>
                                </div>
                                <div class="bg-white/70 rounded-lg px-3 py-2.5 border border-indigo-100">
                                    <p class="text-xs text-indigo-500 mb-0.5">Category</p>
                                    <p class="text-sm font-semibold text-indigo-900">{{ complaint.category?.name || '—' }}</p>
                                </div>
                                <div class="bg-white/70 rounded-lg px-3 py-2.5 border border-indigo-100">
                                    <p class="text-xs text-indigo-500 mb-0.5">Priority</p>
                                    <p class="text-sm font-semibold text-indigo-900 capitalize">{{ complaint.priority }}</p>
                                </div>
                            </div>

                            <div v-if="complaint.ai_summary" class="bg-white/70 rounded-lg px-3 py-2.5 border border-indigo-100">
                                <p class="text-xs text-indigo-500 mb-0.5">Summary</p>
                                <p class="text-sm text-indigo-800 leading-relaxed">{{ complaint.ai_summary }}</p>
                            </div>
                        </div>

                        <div v-if="complaint.attachments?.length" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                            <h2 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-images text-gray-400 text-xs"></i>
                                Photos
                                <span class="text-xs font-normal text-gray-400">({{ complaint.attachments.length }})</span>
                            </h2>
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                <a
                                    v-for="att in complaint.attachments"
                                    :key="att.id"
                                    :href="'/storage/' + att.file_path"
                                    target="_blank"
                                    class="group relative aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-50 hover:shadow-md transition-shadow"
                                >
                                    <img
                                        :src="'/storage/' + att.file_path"
                                        :alt="att.file_name"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                    />
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                                        <i class="fas fa-expand text-white opacity-0 group-hover:opacity-100 transition-opacity text-sm"></i>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div
                            v-if="complaint.location || complaint.latitude || complaint.longitude"
                            class="bg-white rounded-xl border border-gray-200 shadow-sm p-5"
                        >
                            <h2 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-map-marker-alt text-gray-400 text-xs"></i>
                                Location
                                <a
                                    v-if="complaint.latitude && complaint.longitude"
                                    :href="getGoogleMapsLink(complaint.latitude, complaint.longitude)"
                                    target="_blank"
                                    class="ml-auto inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    Open in Google Maps
                                </a>
                            </h2>
                            <p v-if="complaint.location" class="text-sm text-gray-700">{{ complaint.location }}</p>
                            <p v-if="complaint.latitude && complaint.longitude" class="text-xs text-gray-400 mt-1">
                                {{ complaint.latitude }}, {{ complaint.longitude }}
                            </p>
                        </div>

                        <!-- ── Resolution Workflow ──────────────────────────── -->
                        <div
                            v-if="complaint.resolved_at || complaint.resolution_notes || complaint.before_photo || complaint.after_photo"
                            class="bg-green-50 border border-green-200 rounded-xl p-5"
                        >
                            <h2 class="text-sm font-semibold text-green-700 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Resolution
                            </h2>

                            <div v-if="complaint.resolution_notes" class="mb-3">
                                <p class="text-xs font-medium text-green-600 mb-1">Notes</p>
                                <p class="text-sm text-green-800 leading-relaxed bg-white/70 rounded-lg px-3 py-2 border border-green-100">
                                    {{ complaint.resolution_notes }}
                                </p>
                            </div>

                            <div v-if="complaint.before_photo || complaint.after_photo" class="grid grid-cols-2 gap-3 mt-3">
                                <div v-if="complaint.before_photo">
                                    <p class="text-xs font-medium text-green-600 mb-1">Before</p>
                                    <a :href="'/storage/' + complaint.before_photo" target="_blank"
                                        class="block aspect-video rounded-lg overflow-hidden border border-green-200 bg-white/70 hover:shadow-md transition-shadow">
                                        <img :src="'/storage/' + complaint.before_photo" class="w-full h-full object-cover" alt="Before photo" />
                                    </a>
                                </div>
                                <div v-if="complaint.after_photo">
                                    <p class="text-xs font-medium text-green-600 mb-1">After</p>
                                    <a :href="'/storage/' + complaint.after_photo" target="_blank"
                                        class="block aspect-video rounded-lg overflow-hidden border border-green-200 bg-white/70 hover:shadow-md transition-shadow">
                                        <img :src="'/storage/' + complaint.after_photo" class="w-full h-full object-cover" alt="After photo" />
                                    </a>
                                </div>
                            </div>

                            <p v-if="complaint.resolved_at" class="text-xs text-green-600 mt-3 flex items-center gap-1">
                                <i class="fas fa-check-circle"></i>
                                Resolved on {{ formatDateTime(complaint.resolved_at) }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-5">

                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-3.5">
                            <h2 class="text-sm font-semibold text-gray-700">Details</h2>
                            <div class="space-y-2.5 text-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="text-gray-400 flex-shrink-0">Citizen</span>
                                    <span class="text-gray-700 text-right font-medium">{{ complaint.citizen?.name || '—' }}</span>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <span class="text-gray-400 flex-shrink-0">Department</span>
                                    <span class="text-right">
                                        <span v-if="complaint.department?.name || complaint.category?.department?.name" class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                                            <i class="fas fa-building text-[10px]"></i>
                                            {{ complaint.department?.name || complaint.category?.department?.name }}
                                        </span>
                                        <span v-else class="text-gray-400">—</span>
                                    </span>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <span class="text-gray-400 flex-shrink-0">Category</span>
                                    <span class="text-gray-700 text-right">{{ complaint.category?.name || '—' }}</span>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <span class="text-gray-400 flex-shrink-0">Submitted</span>
                                    <span class="text-gray-700 text-right">{{ formatDate(complaint.created_at) }}</span>
                                </div>
                                <div v-if="complaint.resolved_at" class="flex items-start justify-between gap-3">
                                    <span class="text-gray-400 flex-shrink-0">Resolved</span>
                                    <span class="text-gray-700 text-right">{{ formatDate(complaint.resolved_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                            <h2 class="text-sm font-semibold text-gray-700 mb-4">Lifecycle</h2>

                            <div v-if="complaint.current_status === 'rejected'" class="flex items-center gap-3 p-3 bg-red-50 rounded-lg border border-red-100">
                                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-times text-red-500 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-red-700">Rejected</p>
                                    <p class="text-xs text-red-500 mt-0.5">This complaint was rejected.</p>
                                </div>
                            </div>

                            <div v-else class="space-y-0">
                                <div
                                    v-for="(step, idx) in LIFECYCLE"
                                    :key="step"
                                    class="flex items-start gap-3"
                                >
                                    <div class="flex flex-col items-center flex-shrink-0">
                                        <div
                                            :class="[
                                                'w-7 h-7 rounded-full flex items-center justify-center border-2 transition-all duration-300',
                                                idx < lifecycleStep
                                                    ? 'bg-indigo-600 border-indigo-600'
                                                    : idx === lifecycleStep
                                                        ? statusColor(step).dot + ' border-transparent ring-4 ring-offset-1 ring-indigo-200'
                                                        : 'bg-white border-gray-200'
                                            ]"
                                        >
                                            <i v-if="idx < lifecycleStep" class="fas fa-check text-white" style="font-size:9px"></i>
                                            <div v-else-if="idx === lifecycleStep" class="w-2.5 h-2.5 rounded-full bg-white"></div>
                                        </div>
                                        <div v-if="idx < LIFECYCLE.length - 1" :class="['w-0.5 h-6 my-0.5 rounded', idx < lifecycleStep ? 'bg-indigo-400' : 'bg-gray-200']"></div>
                                    </div>
                                    <div class="pb-1 pt-0.5 min-w-0">
                                        <p :class="[
                                            'text-sm font-medium leading-tight',
                                            idx < lifecycleStep  ? 'text-indigo-600' :
                                            idx === lifecycleStep ? 'text-gray-900' :
                                            'text-gray-400'
                                        ]">{{ statusLabel(step) }}</p>
                                        <p v-if="idx === lifecycleStep" class="text-xs text-gray-400 mt-0.5">Current stage</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="isAdmin && allowedNextStatuses.length > 0 && complaint.current_status !== 'closed'" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                            <h2 class="text-sm font-semibold text-gray-700 mb-3">Update Status</h2>

                            <div v-if="!showStatusForm" class="space-y-2">
                                <button
                                    v-for="s in allowedNextStatuses"
                                    :key="s"
                                    @click="form.current_status = s; showStatusForm = true"
                                    :class="[
                                        'w-full text-left px-3 py-2 rounded-lg text-sm font-medium border transition-all duration-150',
                                        statusColor(s).badge + ' border-transparent hover:shadow-sm'
                                    ]"
                                >
                                    <i class="fas fa-arrow-right text-xs mr-2 opacity-60"></i>
                                    Move to {{ statusLabel(s) }}
                                </button>
                            </div>

                            <div v-else>
                                <div class="flex items-center gap-2 mb-3 p-2.5 rounded-lg border" :class="statusColor(form.current_status).badge">
                                    <div :class="['w-2 h-2 rounded-full flex-shrink-0', statusColor(form.current_status).dot]"></div>
                                    <span class="text-sm font-medium">{{ statusLabel(complaint.current_status) }}</span>
                                    <i class="fas fa-arrow-right text-xs mx-1 opacity-50"></i>
                                    <span class="text-sm font-semibold">{{ statusLabel(form.current_status) }}</span>
                                </div>

                                <div class="mb-3">
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">
                                        Remarks <span class="text-red-400">*</span>
                                    </label>
                                    <textarea
                                        v-model="form.remarks"
                                        rows="2"
                                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 resize-none placeholder:text-gray-400"
                                        :placeholder="`Reason for moving to ${statusLabel(form.current_status)}...`"
                                        maxlength="1000"
                                    ></textarea>
                                    <p v-if="form.errors.remarks" class="text-xs text-red-500 mt-1">{{ form.errors.remarks }}</p>
                                </div>

                                <textarea
                                    v-if="form.current_status === 'resolved' || form.current_status === 'rejected'"
                                    v-model="form.resolution_notes"
                                    rows="3"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 resize-none placeholder:text-gray-400 mb-3"
                                    placeholder="Resolution / rejection notes..."
                                    maxlength="5000"
                                ></textarea>

                                <!-- Before/After photo uploads -->
                                <div v-if="form.current_status === 'resolved'" class="space-y-3 mb-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Before Photo</label>
                                        <input
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            @change="beforePhotoFile = $event.target.files[0] || null"
                                            class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">After Photo</label>
                                        <input
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            @change="afterPhotoFile = $event.target.files[0] || null"
                                            class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-green-50 file:text-green-600 hover:file:bg-green-100"
                                        />
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <button @click="showStatusForm = false; form.reset()" class="flex-1 px-3 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        Cancel
                                    </button>
                                    <button
                                        @click="submitStatusChange"
                                        :disabled="form.processing"
                                        class="flex-1 px-3 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg disabled:opacity-50 transition-colors"
                                    >
                                        <i v-if="form.processing" class="fas fa-spinner fa-spin mr-1.5 text-xs"></i>
                                        Confirm
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="complaint.current_status === 'closed'" class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
                            <i class="fas fa-lock text-gray-300 text-xl mb-2"></i>
                            <p class="text-sm font-medium text-gray-500">Complaint Closed</p>
                            <p class="text-xs text-gray-400 mt-0.5">No further actions available.</p>
                        </div>
                    </div>
                </div>

                <div v-if="complaint.tracks?.length" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-5 flex items-center gap-2">
                        <i class="fas fa-history text-gray-400 text-xs"></i>
                        Activity History
                        <span class="text-xs font-normal text-gray-400">({{ complaint.tracks.length }} events)</span>
                    </h2>
                    <div class="relative">
                        <div class="absolute left-[13px] top-2 bottom-2 w-0.5 bg-gray-100 rounded"></div>
                        <div class="space-y-6">
                            <div
                                v-for="(track, tIdx) in complaint.tracks"
                                :key="track.id"
                                class="relative pl-10"
                            >
                                <div
                                    :class="[
                                        'absolute left-0 top-1 w-[26px] h-[26px] rounded-full flex items-center justify-center z-10 ring-4 ring-white',
                                        statusColor(track.new_status).dot
                                    ]"
                                >
                                    <i class="fas fa-arrow-right text-white" style="font-size:8px"></i>
                                </div>

                                <div class="bg-gray-50 rounded-xl border border-gray-100 p-3.5">
                                    <div class="flex items-center gap-2 flex-wrap mb-2">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset"
                                            :class="statusColor(track.old_status).badge"
                                        >{{ statusLabel(track.old_status) }}</span>
                                        <svg class="w-3.5 h-3.5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset"
                                            :class="statusColor(track.new_status).badge"
                                        >{{ statusLabel(track.new_status) }}</span>
                                        <span class="ml-auto text-xs text-gray-400 whitespace-nowrap">{{ formatDateTime(track.created_at) }}</span>
                                    </div>

                                    <p v-if="track.remarks" class="text-sm text-gray-700 leading-relaxed border-l-2 border-indigo-200 pl-3 ml-0.5">
                                        {{ track.remarks }}
                                    </p>

                                    <div class="flex items-center gap-1.5 mt-2 text-xs text-gray-400">
                                        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span>{{ track.changer?.name ?? 'System' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
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
</style>
