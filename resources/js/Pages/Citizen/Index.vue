<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const { t } = useI18n()
const { complaints, departments, categories } = usePage().props

const items = ref([...(complaints ?? [])])

watch(() => usePage().props.complaints, (v) => {
    items.value = [...(v ?? [])]
})

const flashMessage = ref(null)
let flashTimer = null
watch(() => usePage().props.flash?.success, (val) => {
    if (flashTimer) clearTimeout(flashTimer)
    flashMessage.value = val || null
    if (flashMessage.value) flashTimer = setTimeout(() => flashMessage.value = null, 4000)
}, { immediate: true })

const search = ref('')
const filterStatus = ref('')
const currentPage = ref(1)
const pageSize = ref(10)

const filteredItems = computed(() => {
    let result = items.value
    const q = search.value.toLowerCase()
    if (q) {
        result = result.filter(c =>
            c.title.toLowerCase().includes(q) ||
            c.complaint_no.toLowerCase().includes(q) ||
            (c.description && c.description.toLowerCase().includes(q))
        )
    }
    if (filterStatus.value) {
        result = result.filter(c => c.current_status === filterStatus.value)
    }
    return result
})

const sortedItems = computed(() => {
    return [...filteredItems.value].sort((a, b) =>
        new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
    )
})

const totalPages = computed(() => Math.max(1, Math.ceil(sortedItems.value.length / pageSize.value)))

const paginatedItems = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value
    return sortedItems.value.slice(start, start + pageSize.value)
})

const startRecord = computed(() => filteredItems.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1)
const endRecord = computed(() => Math.min(currentPage.value * pageSize.value, filteredItems.value.length))

const hasSearchResults = computed(() => paginatedItems.value.length > 0)

function statusBadge(status) {
    const map = {
        submitted: 'bg-blue-50 text-blue-700 ring-blue-600/20',
        under_review: 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
        assigned: 'bg-purple-50 text-purple-700 ring-purple-600/20',
        in_progress: 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
        resolved: 'bg-green-50 text-green-700 ring-green-600/20',
        rejected: 'bg-red-50 text-red-700 ring-red-600/20',
        closed: 'bg-gray-50 text-gray-700 ring-gray-600/20',
    }
    return map[status] || 'bg-gray-50 text-gray-700 ring-gray-600/20'
}

function statusLabel(status) {
    const map = {
        submitted: t('complaint.statusSubmitted'),
        under_review: t('complaint.statusUnderReview'),
        assigned: t('complaint.statusAssigned'),
        in_progress: t('complaint.statusInProgress'),
        resolved: t('complaint.statusResolved'),
        rejected: t('complaint.statusRejected'),
        closed: t('complaint.statusClosed'),
    }
    return map[status] || status
}

function priorityBadge(priority) {
    const map = {
        low: 'bg-gray-50 text-gray-600 ring-gray-500/20',
        medium: 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
        high: 'bg-orange-50 text-orange-700 ring-orange-600/20',
        emergency: 'bg-red-50 text-red-700 ring-red-600/20',
    }
    return map[priority] || 'bg-gray-50 text-gray-600 ring-gray-500/20'
}

function priorityLabel(priority) {
    const map = {
        low: t('complaint.priorityLow'),
        medium: t('complaint.priorityMedium'),
        high: t('complaint.priorityHigh'),
        emergency: t('complaint.priorityEmergency'),
    }
    return map[priority] || priority
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

function clearFilters() {
    search.value = ''
    filterStatus.value = ''
    currentPage.value = 1
}

watch(search, () => { currentPage.value = 1 })
watch(filterStatus, () => { currentPage.value = 1 })
</script>

<template>
    <Head title="My Complaints" />

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
        <div class="py-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">My Complaints</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ items.length }} complaint{{ items.length !== 1 ? 's' : '' }} submitted</p>
                </div>
                <Link
                    :href="route('citizen.complaints.create')"
                    class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-5 py-3 text-sm font-medium text-white shadow-sm hover:bg-red-600"
                >
                    <i class="fas fa-plus text-xs"></i>
                    New Complaint
                </Link>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="relative flex-1 max-w-xs">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search complaints..."
                                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                            />
                            <button v-if="search" @click="search = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        <select v-model="filterStatus" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm bg-gray-50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            <option value="">All Statuses</option>
                            <option value="submitted">Submitted</option>
                            <option value="under_review">Under Review</option>
                            <option value="assigned">Assigned</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="rejected">Rejected</option>
                            <option value="closed">Closed</option>
                        </select>
                        <button v-if="search || filterStatus" @click="clearFilters" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                            Clear filters
                        </button>
                    </div>
                </div>

                <div v-if="!hasSearchResults" class="px-5 py-12 text-center">
                    <div class="w-14 h-14 rounded-xl bg-gray-50 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-search text-gray-300 text-xl"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-900">No complaints found</p>
                    <p class="text-sm text-gray-500 mt-1">{{ search ? 'Try a different search term.' : 'Submit your first complaint to get started.' }}</p>
                    <Link v-if="!search" :href="route('citizen.complaints.create')" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">
                        <i class="fas fa-plus text-xs"></i>
                        Submit Complaint
                    </Link>
                </div>

                <div v-else class="divide-y divide-gray-50">
                    <div v-for="item in paginatedItems" :key="item.id" class="px-5 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <Link :href="route('citizen.complaints.show', item.id)" class="text-sm font-medium text-gray-900 hover:text-indigo-600 truncate block">
                                    {{ item.title }}
                                </Link>
                                <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ item.complaint_no }}</p>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset" :class="statusBadge(item.current_status)">
                                        {{ statusLabel(item.current_status) }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset" :class="priorityBadge(item.priority)">
                                        {{ priorityLabel(item.priority) }}
                                    </span>
                                    <span v-if="item.department?.name || item.category?.department?.name" class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700">
                                        <i class="fas fa-building text-[9px]"></i>
                                        {{ item.department?.name || item.category?.department?.name }}
                                    </span>
                                </div>
                            </div>
                            <span class="text-xs text-gray-400 flex-shrink-0">{{ formatDate(item.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="hasSearchResults" class="px-5 py-3.5 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <p class="text-sm text-gray-500">
                        Showing <span class="font-medium text-gray-700">{{ startRecord }}</span>
                        &ndash; <span class="font-medium text-gray-700">{{ endRecord }}</span>
                        of <span class="font-medium text-gray-700">{{ filteredItems.length }}</span>
                    </p>
                    <nav class="flex items-center gap-1">
                        <button :disabled="currentPage === 1" @click="currentPage = 1" class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 disabled:opacity-30" title="First">
                            <i class="fas fa-angle-double-left text-xs"></i>
                        </button>
                        <button :disabled="currentPage === 1" @click="currentPage--" class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 disabled:opacity-30" title="Previous">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </button>
                        <span class="text-xs text-gray-500 px-2">Page {{ currentPage }} of {{ totalPages }}</span>
                        <button :disabled="currentPage === totalPages" @click="currentPage++" class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 disabled:opacity-30" title="Next">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </button>
                        <button :disabled="currentPage === totalPages" @click="currentPage = totalPages" class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 disabled:opacity-30" title="Last">
                            <i class="fas fa-angle-double-right text-xs"></i>
                        </button>
                    </nav>
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
