<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link } from '@inertiajs/vue3';

const { t } = useI18n();

const props = defineProps({
    groupedComplaints: {
        type: Array,
        default: () => [],
    },
});

const expandedGroups = ref({});

const toggleGroup = (location) => {
    expandedGroups.value[location] = !expandedGroups.value[location];
};

const priorityColor = (priority) => {
    const colors = {
        low: 'bg-gray-100 text-gray-700',
        medium: 'bg-yellow-100 text-yellow-700',
        high: 'bg-orange-100 text-orange-700',
        emergency: 'bg-red-100 text-red-700',
    };
    return colors[priority] || colors.medium;
};

const statusColor = (status) => {
    const colors = {
        submitted: 'bg-blue-100 text-blue-700',
        under_review: 'bg-yellow-100 text-yellow-700',
        assigned: 'bg-purple-100 text-purple-700',
        in_progress: 'bg-indigo-100 text-indigo-700',
        resolved: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700',
        closed: 'bg-gray-100 text-gray-700',
    };
    return colors[status] || colors.submitted;
};

const statusLabel = (status) => {
    const labels = {
        submitted: t('complaint.statusSubmitted'),
        under_review: t('complaint.statusUnderReview'),
        assigned: t('complaint.statusAssigned'),
        in_progress: t('complaint.statusInProgress'),
        resolved: t('complaint.statusResolved'),
        rejected: t('complaint.statusRejected'),
        closed: t('complaint.statusClosed'),
    };
    return labels[status] || status;
};

const priorityLabel = (priority) => {
    const labels = {
        low: t('complaint.priorityLow'),
        medium: t('complaint.priorityMedium'),
        high: t('complaint.priorityHigh'),
        emergency: t('complaint.priorityEmergency'),
    };
    return labels[priority] || priority;
};
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900">{{ t('dashboard.groupedByLocation') }}</h2>
            <span class="text-sm text-gray-500">{{ groupedComplaints.length }} {{ t('dashboard.areas') }}</span>
        </div>

        <div v-if="groupedComplaints.length === 0" class="text-center py-12">
            <i class="fas fa-map-marker-alt text-gray-300 text-4xl mb-3"></i>
            <p class="text-gray-500">{{ t('dashboard.noGroupedComplaints') }}</p>
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="group in groupedComplaints"
                :key="group.location"
                class="border border-gray-200 rounded-lg overflow-hidden"
            >
                <!-- Group Header -->
                <button
                    @click="toggleGroup(group.location)"
                    class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 transition-colors"
                >
                    <div class="flex items-center gap-3">
                        <i class="fas fa-map-marker-alt text-indigo-600"></i>
                        <span class="font-medium text-gray-900">{{ group.location }}</span>
                        <span class="bg-indigo-100 text-indigo-700 text-xs font-medium px-2 py-1 rounded-full">
                            {{ group.count }} {{ t('dashboard.complaints') }}
                        </span>
                    </div>
                    <i
                        :class="expandedGroups[group.location] ? 'fa-chevron-up' : 'fa-chevron-down'"
                        class="fas text-gray-400 transition-transform"
                    ></i>
                </button>

                <!-- Expanded Complaints -->
                <div v-if="expandedGroups[group.location]" class="p-4 border-t border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 border-b border-gray-200">
                                    <th class="pb-3 font-medium">{{ t('complaint.columnTitle') }}</th>
                                    <th class="pb-3 font-medium">{{ t('complaint.columnCategory') }}</th>
                                    <th class="pb-3 font-medium">{{ t('complaint.columnPriority') }}</th>
                                    <th class="pb-3 font-medium">{{ t('complaint.columnStatus') }}</th>
                                    <th class="pb-3 font-medium">{{ t('complaint.columnCitizen') }}</th>
                                    <th class="pb-3 font-medium">{{ t('complaint.columnCreated') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="complaint in group.complaints"
                                    :key="complaint.id"
                                    class="border-b border-gray-100 hover:bg-gray-50"
                                >
                                    <td class="py-3">
                                        <Link
                                            :href="route('complaints.show', complaint.id)"
                                            class="text-indigo-600 hover:text-indigo-800 font-medium"
                                        >
                                            {{ complaint.title }}
                                        </Link>
                                    </td>
                                    <td class="py-3 text-gray-600">{{ complaint.category || '—' }}</td>
                                    <td class="py-3">
                                        <span :class="priorityColor(complaint.priority)" class="text-xs font-medium px-2 py-1 rounded-full">
                                            {{ priorityLabel(complaint.priority) }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span :class="statusColor(complaint.current_status)" class="text-xs font-medium px-2 py-1 rounded-full">
                                            {{ statusLabel(complaint.current_status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-gray-600">{{ complaint.citizen || '—' }}</td>
                                    <td class="py-3 text-gray-600">{{ complaint.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
