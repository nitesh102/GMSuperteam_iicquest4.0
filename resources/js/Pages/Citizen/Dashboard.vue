<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { usePage } from '@inertiajs/vue3'

const { t } = useI18n()
const { stats, recentComplaints } = usePage().props

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
        submitted: 'Submitted',
        under_review: 'Under Review',
        assigned: 'Assigned',
        in_progress: 'In Progress',
        resolved: 'Resolved',
        rejected: 'Rejected',
        closed: 'Closed',
    }
    return map[status] || status
}

function formatDate(d) {
    if (!d) return ''
    return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<template>
    <Head title="Citizen Dashboard" />

    <AdminLayout>
        <div class="py-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Citizen Dashboard</h1>
                    <p class="mt-1 text-sm text-gray-500">Track and manage your complaints</p>
                </div>
                <Link
                    :href="route('citizen.complaints.create')"
                    class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-5 py-3 text-sm font-medium text-white shadow-sm hover:bg-red-600"
                >
                    <i class="fas fa-plus text-xs"></i>
                    New Complaint
                </Link>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                            <i class="fas fa-flag text-indigo-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Total</p>
                            <p class="text-xl font-bold text-gray-900 mt-0.5">{{ stats.total }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-yellow-50 flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Pending</p>
                            <p class="text-xl font-bold text-gray-900 mt-0.5">{{ stats.pending }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                            <i class="fas fa-spinner text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">In Progress</p>
                            <p class="text-xl font-bold text-gray-900 mt-0.5">{{ stats.inProgress }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Resolved</p>
                            <p class="text-xl font-bold text-gray-900 mt-0.5">{{ stats.resolved }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-700">Recent Complaints</h2>
                </div>
                <div v-if="recentComplaints.length === 0" class="px-5 py-12 text-center">
                    <div class="w-14 h-14 rounded-xl bg-gray-50 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-flag text-gray-300 text-xl"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-900">No complaints yet</p>
                    <p class="text-sm text-gray-500 mt-1">Submit your first complaint to get started.</p>
                    <Link :href="route('citizen.complaints.create')" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">
                        <i class="fas fa-plus text-xs"></i>
                        Submit Complaint
                    </Link>
                </div>
                <div v-else class="divide-y divide-gray-50">
                    <div v-for="c in recentComplaints" :key="c.id" class="px-5 py-3.5 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <Link :href="route('citizen.complaints.show', c.id)" class="text-sm font-medium text-gray-900 hover:text-indigo-600 truncate block">
                                    {{ c.title }}
                                </Link>
                                <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ c.complaint_no }}</p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset" :class="statusBadge(c.current_status)">
                                    {{ statusLabel(c.current_status) }}
                                </span>
                                <span class="text-xs text-gray-400">{{ formatDate(c.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="recentComplaints.length > 0" class="px-5 py-3 border-t border-gray-100 text-center">
                    <Link :href="route('citizen.complaints.index')" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                        View all complaints &rarr;
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
