<template>
    <Modal :show="show" @close="closeModal">
        <div>
            <div class="flex items-center gap-3.5 mb-6">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-building text-indigo-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ isEditMode ? 'Edit Department' : 'New Department' }}
                    </h3>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ isEditMode ? 'Update the department details below.' : 'Add a new department to organize complaints.' }}
                    </p>
                </div>
            </div>

            <form @submit.prevent="submitForm" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Department Name <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-tag text-gray-400 text-sm"></i>
                        </div>
                        <input
                            type="text"
                            v-model="form.name"
                            :class="[
                                'w-full pl-9 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 placeholder:text-gray-400',
                                form.errors.name
                                    ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                    : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                            ]"
                            placeholder="e.g. Health Department"
                            required
                        />
                    </div>
                    <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                        <i class="fas fa-exclamation-circle text-xs"></i>
                        <span>{{ form.errors.name }}</span>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <div class="relative">
                        <textarea
                            v-model="form.description"
                            rows="4"
                            :class="[
                                'w-full px-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 resize-none placeholder:text-gray-400',
                                form.errors.description
                                    ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                    : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                            ]"
                            placeholder="Describe the department's purpose and responsibilities..."
                            maxlength="1000"
                        ></textarea>
                    </div>
                    <div class="flex items-center justify-between mt-1.5">
                        <p v-if="form.errors.description" class="text-sm text-red-600 flex items-center gap-1.5">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ form.errors.description }}</span>
                        </p>
                        <p v-else></p>
                        <p class="text-xs text-gray-400">{{ form.description.length }}/1000</p>
                    </div>
                </div>

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
                            {{ isEditMode ? 'Update Department' : 'Create Department' }}
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
    department: Object,
});

const emit = defineEmits(['close', 'success', 'submitting']);

const isEditMode = computed(() => !!props.department);

const form = useForm({
    name: props.department?.name || '',
    description: props.department?.description || '',
});

watch(() => props.department, (newDept) => {
    if (newDept) {
        form.name = newDept.name || '';
        form.description = newDept.description || '';
    }
}, { immediate: true });

function closeModal() {
    form.reset();
    emit('close');
}

function submitForm() {
    const payload = { name: form.name, description: form.description };
    if (isEditMode.value) {
        form.put(route('departments.update', props.department.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('submitting', { ...payload, id: props.department.id, isEdit: true });
                emit('success');
                closeModal();
            },
        });
    } else {
        form.post(route('departments.store'), {
            preserveScroll: true,
            onSuccess: () => {
                emit('submitting', { ...payload, isEdit: false });
                emit('success');
                closeModal();
            },
        });
    }
}
</script>
