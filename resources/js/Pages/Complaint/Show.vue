<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    complaint:   Object,
    departments: Array,
    categories:  Array,
})

// ── flash ──────────────────────────────────────────────────────────────────
const flashMessage = ref(null)
let flashTimer = null
watch(() => usePage().props.flash?.success, (val) => {
    if (flashTimer) clearTimeout(flashTimer)
    flashMessage.value = val || null
    if (flashMessage.value) flashTimer = setTimeout(() => flashMessage.value = null, 4000)
}, { immediate: true })

// ── status change form ─────────────────────────────────────────────────────
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
    track_notes:      '',
})

const allowedNextStatuses = computed(() =>
    STATUS_TRANSITIONS[props.complaint.current_status] ?? []
)

function submitStatusChange() {
    form.put(route('complaints.update', props.complaint.id), {
        preserveScroll: true,
        onSuccess: () => {
            showStatusForm.value = false
            form.track_notes = ''
        },
    })
}

// ── helpers ────────────────────────────────────────────────────────────────
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

// current step index in lifecycle (rejected/closed handled separately)
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

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Back + header -->
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

                <!-- SLA / escalation bar -->
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
                        <i
                            class="fas text-sm"
                            :class="[
                                dueStatus() === 'overdue'  ? 'fa-exclamation-circle text-red-500' :
                                dueStatus() === 'critical' ? 'fa-exclamation-triangle text-orange-500' :
                                dueStatus() === 'warning'  ? 'fa-clock text-yellow-500' :
                                'fa-clock text-gray-400'
                            ]"
                        ></i>
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

                <!-- Two-column layout -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Left: main info -->
                    <div class="lg:col-span-2 space-y-5">

                        <!-- Description -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                            <h2 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-align-left text-gray-400 text-xs"></i>
                                Description
                            </h2>
                            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ complaint.description }}</p>
                        </div>

                        <!-- AI Summary -->
                        <div v-if="complaint.ai_summary" class="bg-indigo-50 border border-indigo-100 rounded-xl p-5">
                            <h2 class="text-sm font-semibold text-indigo-700 mb-2 flex items-center gap-2">
                                <i class="fas fa-robot text-indigo-500 text-xs"></i>
                                AI Summary
                            </h2>
                            <p class="text-sm text-indigo-800 leading-relaxed">{{ complaint.ai_summary }}</p>
                        </div>

                        <!-- Images -->
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

                        <!-- Location -->
                        <div v-if="complaint.location || complaint.latitude" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                            <h2 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-map-marker-alt text-gray-400 text-xs"></i>
                                Location
                            </h2>
                            <p v-if="complaint.location" class="text-sm text-gray-700">{{ complaint.location }}</p>
                            <p v-if="complaint.latitude && complaint.longitude" class="text-xs text-gray-400 mt-1">
                                {{ complaint.latitude }}, {{ complaint.longitude }}
                            </p>
                        </div>

                        <!-- Resolution notes -->
                        <div v-if="complaint.resolution_notes" class="bg-green-50 border border-green-200 rounded-xl p-5">
                            <h2 class="text-sm font-semibold text-green-700 mb-2 flex items-center gap-2">
                                <i class="fas fa-check-circle text-green-500 text-xs"></i>
                                Resolution Notes
                            </h2>
                            <p class="text-sm text-green-800 leading-relaxed">{{ complaint.resolution_notes }}</p>
                            <p v-if="complaint.resolved_at" class="text-xs text-green-600 mt-2">
                                Resolved on {{ formatDateTime(complaint.resolved_at) }}
                            </p>
                        </div>
                    </div>

                    <!-- Right: meta + lifecycle -->
                    <div class="space-y-5">

                        <!-- Meta card -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-3.5">
                            <h2 class="text-sm font-semibold text-gray-700">Details</h2>
                            <div class="space-y-2.5 text-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="text-gray-400 flex-shrink-0">Citizen</span>
                                    <span class="text-gray-700 text-right font-medium">{{ complaint.citizen?.name || '—' }}</span>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <span class="text-gray-400 flex-shrink-0">Department</span>
                                    <span class="text-gray-700 text-right">{{ complaint.category?.department?.name || '—' }}</span>
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

                        <!-- Lifecycle stepper -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                            <h2 class="text-sm font-semibold text-gray-700 mb-4">Lifecycle</h2>

                            <!-- Rejected state -->
                            <div v-if="complaint.current_status === 'rejected'" class="flex items-center gap-3 p-3 bg-red-50 rounded-lg border border-red-100">
                                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-times text-red-500 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-red-700">Rejected</p>
                                    <p class="text-xs text-red-500 mt-0.5">This complaint was rejected.</p>
                                </div>
                            </div>

                            <!-- Normal lifecycle -->
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
                                        <p
                                            :class="[
                                                'text-sm font-medium leading-tight',
                                                idx < lifecycleStep  ? 'text-indigo-600' :
                                                idx === lifecycleStep ? 'text-gray-900' :
                                                'text-gray-400'
                                            ]"
                                        >{{ statusLabel(step) }}</p>
                                        <p v-if="idx === lifecycleStep" class="text-xs text-gray-400 mt-0.5">Current stage</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Update status -->
                        <div v-if="allowedNextStatuses.length > 0 && complaint.current_status !== 'closed'" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
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

                                <textarea
                                    v-model="form.track_notes"
                                    rows="2"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 resize-none placeholder:text-gray-400 mb-3"
                                    placeholder="Note (optional)..."
                                    maxlength="1000"
                                ></textarea>

                                <textarea
                                    v-if="form.current_status === 'resolved' || form.current_status === 'rejected'"
                                    v-model="form.resolution_notes"
                                    rows="3"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 resize-none placeholder:text-gray-400 mb-3"
                                    placeholder="Resolution / rejection notes..."
                                    maxlength="5000"
                                ></textarea>

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

                        <!-- Closed badge -->
                        <div v-if="complaint.current_status === 'closed'" class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
                            <i class="fas fa-lock text-gray-300 text-xl mb-2"></i>
                            <p class="text-sm font-medium text-gray-500">Complaint Closed</p>
                            <p class="text-xs text-gray-400 mt-0.5">No further actions available.</p>
                        </div>
                    </div>
                </div>

                <!-- Full audit timeline -->
                <div v-if="complaint.tracks?.length" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-history text-gray-400 text-xs"></i>
                        Activity History
                        <span class="text-xs font-normal text-gray-400">({{ complaint.tracks.length }} events)</span>
                    </h2>
                    <div class="relative">
                        <div class="absolute left-3.5 top-0 bottom-0 w-px bg-gray-100"></div>
                        <div class="space-y-4">
                            <div
                                v-for="track in complaint.tracks"
                                :key="track.id"
                                class="flex items-start gap-4 pl-0"
                            >
                                <div
                                    :class="['w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 z-10 border-2 border-white', statusColor(track.new_status).dot]"
                                >
                                    <i class="fas fa-arrow-right text-white" style="font-size:8px"></i>
                                </div>
                                <div class="flex-1 min-w-0 pb-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                                            :class="statusColor(track.old_status).badge"
                                        >{{ statusLabel(track.old_status) }}</span>
                                        <i class="fas fa-long-arrow-alt-right text-gray-300 text-xs"></i>
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                                            :class="statusColor(track.new_status).badge"
                                        >{{ statusLabel(track.new_status) }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1 text-xs text-gray-400">
                                        <i class="fas fa-user text-gray-300"></i>
                                        <span>{{ track.changer?.name ?? 'System' }}</span>
                                        <span>&bull;</span>
                                        <span>{{ formatDateTime(track.created_at) }}</span>
                                    </div>
                                    <p v-if="track.notes" class="mt-1 text-xs text-gray-500 italic bg-gray-50 rounded px-2 py-1">
                                        "{{ track.notes }}"
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes slide-in {
    from { transform: translateX(100%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
.animate-slide-in { animation: slide-in 0.3s ease-out; }
</style>
