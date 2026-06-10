<template>
    <Modal :show="show" @close="closeModal">
        <div>
            <div class="flex items-center gap-3.5 mb-6">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-flag text-indigo-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ isEditMode ? 'Edit Complaint' : 'New Complaint' }}
                    </h3>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ isEditMode ? 'Update the complaint details below.' : 'Submit a new citizen complaint.' }}
                    </p>
                </div>
            </div>

            <form @submit.prevent="submitForm" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Department <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-building text-gray-400 text-sm"></i>
                        </div>
                        <select
                            v-model="form.department_id"
                            @change="form.category_id = ''"
                            :class="[
                                'w-full pl-9 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 appearance-none bg-white',
                                form.errors.department_id
                                    ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                    : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                            ]"
                            required
                        >
                            <option value="" disabled>Select a department</option>
                            <option
                                v-for="dept in departments"
                                :key="dept.id"
                                :value="dept.id"
                            >
                                {{ dept.name }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                    </div>
                    <p v-if="form.errors.department_id" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                        <i class="fas fa-exclamation-circle text-xs"></i>
                        <span>{{ form.errors.department_id }}</span>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Category <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-tag text-gray-400 text-sm"></i>
                        </div>
                        <select
                            v-model="form.category_id"
                            :class="[
                                'w-full pl-9 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 appearance-none bg-white',
                                form.errors.category_id
                                    ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                    : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                            ]"
                            :disabled="!form.department_id"
                            required
                        >
                            <option value="" disabled>
                                {{ form.department_id ? 'Select a category' : 'Select a department first' }}
                            </option>
                            <option
                                v-for="cat in filteredCategories"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ cat.name }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                    </div>
                    <p v-if="form.errors.category_id" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                        <i class="fas fa-exclamation-circle text-xs"></i>
                        <span>{{ form.errors.category_id }}</span>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Title <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-heading text-gray-400 text-sm"></i>
                        </div>
                        <input
                            type="text"
                            v-model="form.title"
                            :class="[
                                'w-full pl-9 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 placeholder:text-gray-400',
                                form.errors.title
                                    ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                    : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                            ]"
                            placeholder="e.g. Pothole on Main Street"
                            required
                        />
                    </div>
                    <p v-if="form.errors.title" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                        <i class="fas fa-exclamation-circle text-xs"></i>
                        <span>{{ form.errors.title }}</span>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Description <span class="text-red-400">*</span>
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="5"
                        :class="[
                            'w-full px-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 resize-none placeholder:text-gray-400',
                            form.errors.description
                                ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                        ]"
                        placeholder="Describe the issue in detail..."
                        maxlength="10000"
                    ></textarea>
                    <div class="flex items-center justify-between mt-1.5">
                        <p v-if="form.errors.description" class="text-sm text-red-600 flex items-center gap-1.5">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ form.errors.description }}</span>
                        </p>
                        <p v-else></p>
                        <p class="text-xs text-gray-400">{{ form.description.length }}/10000</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Location</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-map-marker-alt text-gray-400 text-sm"></i>
                        </div>
                        <input
                            type="text"
                            v-model="form.location"
                            :class="[
                                'w-full pl-9 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 placeholder:text-gray-400',
                                form.errors.location
                                    ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                    : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                            ]"
                            placeholder="e.g. 123 Main Street, Downtown"
                        />
                    </div>
                    <p v-if="form.errors.location" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                        <i class="fas fa-exclamation-circle text-xs"></i>
                        <span>{{ form.errors.location }}</span>
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Latitude</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-arrows-alt text-gray-400 text-sm"></i>
                            </div>
                            <input
                                type="number"
                                step="any"
                                v-model="form.latitude"
                                :class="[
                                    'w-full pl-9 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 placeholder:text-gray-400',
                                    form.errors.latitude
                                        ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                        : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                                ]"
                                placeholder="-90 to 90"
                            />
                        </div>
                        <p v-if="form.errors.latitude" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ form.errors.latitude }}</span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Longitude</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-arrows-alt-h text-gray-400 text-sm"></i>
                            </div>
                            <input
                                type="number"
                                step="any"
                                v-model="form.longitude"
                                :class="[
                                    'w-full pl-9 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 placeholder:text-gray-400',
                                    form.errors.longitude
                                        ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                        : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                                ]"
                                placeholder="-180 to 180"
                            />
                        </div>
                        <p v-if="form.errors.longitude" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ form.errors.longitude }}</span>
                        </p>
                    </div>
                </div>

                <template v-if="isEditMode">
                    <hr class="border-gray-100" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Status <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <select
                                    v-model="form.current_status"
                                    :class="[
                                        'w-full pl-3 pr-8 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 appearance-none bg-white',
                                        form.errors.current_status
                                            ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                            : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                                    ]"
                                    required
                                >
                                    <option value="submitted">Submitted</option>
                                    <option value="under_review">Under Review</option>
                                    <option value="assigned">Assigned</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="closed">Closed</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                </div>
                            </div>
                            <p v-if="form.errors.current_status" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                <span>{{ form.errors.current_status }}</span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Priority <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <select
                                    v-model="form.priority"
                                    :class="[
                                        'w-full pl-3 pr-8 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 appearance-none bg-white',
                                        form.errors.priority
                                            ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                            : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                                    ]"
                                    required
                                >
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="emergency">Emergency</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                </div>
                            </div>
                            <p v-if="form.errors.priority" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                <span>{{ form.errors.priority }}</span>
                            </p>
                        </div>
                    </div>
                </template>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-150"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        :class="[
                            'px-4 py-2.5 text-sm font-medium text-white rounded-lg transition-all duration-150 inline-flex items-center gap-2',
                            form.processing
                                ? 'bg-indigo-400 cursor-not-allowed'
                                : 'bg-indigo-600 hover:bg-indigo-700 hover:shadow-md'
                        ]"
                    >
                        <span v-if="form.processing" class="inline-flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i>
                            Saving...
                        </span>
                        <span v-else class="inline-flex items-center gap-2">
                            <i class="fas" :class="isEditMode ? 'fa-save' : 'fa-plus'"></i>
                            {{ isEditMode ? 'Update Complaint' : 'Submit Complaint' }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: Boolean,
    complaint: Object,
    departments: Array,
    categories: Array,
});

const emit = defineEmits(['close', 'success', 'submitting']);

const isEditMode = computed(() => !!props.complaint);

const form = useForm({
    department_id: props.complaint?.category?.department_id || '',
    category_id: props.complaint?.category_id || '',
    title: props.complaint?.title || '',
    description: props.complaint?.description || '',
    location: props.complaint?.location || '',
    latitude: props.complaint?.latitude || '',
    longitude: props.complaint?.longitude || '',
    current_status: props.complaint?.current_status || 'submitted',
    priority: props.complaint?.priority || 'medium',
});

const filteredCategories = computed(() => {
    if (!form.department_id || !props.categories) return [];
    return props.categories.filter(c => c.department_id === Number(form.department_id));
});

watch(() => props.complaint, (newComplaint) => {
    if (newComplaint) {
        form.department_id = newComplaint.category?.department_id || '';
        form.category_id = newComplaint.category_id || '';
        form.title = newComplaint.title || '';
        form.description = newComplaint.description || '';
        form.location = newComplaint.location || '';
        form.latitude = newComplaint.latitude || '';
        form.longitude = newComplaint.longitude || '';
        form.current_status = newComplaint.current_status || 'submitted';
        form.priority = newComplaint.priority || 'medium';
    }
}, { immediate: true });

function statusLabel(status) {
    const map = {
        submitted: 'Submitted',
        under_review: 'Under Review',
        assigned: 'Assigned',
        in_progress: 'In Progress',
        resolved: 'Resolved',
        rejected: 'Rejected',
        closed: 'Closed',
    };
    return map[status] || status;
}

function statusBadgeClass(status) {
    const map = {
        submitted: 'bg-blue-50 text-blue-700 ring-blue-600/20',
        under_review: 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
        assigned: 'bg-purple-50 text-purple-700 ring-purple-600/20',
        in_progress: 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
        resolved: 'bg-green-50 text-green-700 ring-green-600/20',
        rejected: 'bg-red-50 text-red-700 ring-red-600/20',
        closed: 'bg-gray-50 text-gray-700 ring-gray-600/20',
    };
    return map[status] || 'bg-gray-50 text-gray-700 ring-gray-600/20';
}

function formatDateTime(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleString('en-US', {
        month: 'short', day: 'numeric',
        hour: 'numeric', minute: '2-digit'
    });
}

function closeModal() {
    form.reset();
    emit('close');
}

function submitForm() {
    const payload = {
        title: form.title,
        description: form.description,
        category_id: form.category_id,
        location: form.location || null,
        latitude: form.latitude || null,
        longitude: form.longitude || null,
    };
    if (isEditMode.value) {
        payload.current_status = form.current_status;
        payload.priority = form.priority;
        payload.id = props.complaint.id;
        payload.isEdit = true;
        form.put(route('complaints.update', props.complaint.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('submitting', payload);
                emit('success');
                closeModal();
            },
        });
    } else {
        payload.isEdit = false;
        form.post(route('complaints.store'), {
            preserveScroll: true,
            onSuccess: () => {
                emit('submitting', payload);
                emit('success');
                closeModal();
            },
        });
    }
}
</script>
