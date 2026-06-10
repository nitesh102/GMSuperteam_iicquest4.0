<template>
    <Modal :show="show" @close="closeModal">
        <div class="mx-auto max-w-4xl rounded-2xl bg-white p-8 shadow-xl">

            <!-- Header -->
            <div class="flex items-start gap-4 border-b border-gray-200 pb-6">
                <!-- Logo Branding -->
                <div class="flex items-center gap-3 flex-shrink-0">
                    <img
                        src="/logo.png"
                        alt="CiviSense Logo"
                        class="h-12 object-contain"
                    />
                    <span class="font-bold text-gray-900">CiviSense</span>
                </div>

                <div class="flex-1">
                    <h3 class="text-2xl font-semibold text-gray-900">
                        {{ isEditMode ? 'Edit Complaint Category' : 'Create Complaint Category' }}
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Manage complaint categories for organizing and tracking citizen submissions.
                    </p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="mt-8">

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <!-- Department Name -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Department Name
                        </label>

                        <input
                            type="text"
                            v-model="form.name"
                            placeholder="Road Maintenance Department"
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-100"
                        />

                        <p
                            v-if="form.errors.name"
                            class="mt-2 text-sm text-red-500"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Department Icon -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Department Icon
                        </label>

                        <select
                            v-model="form.icon"
                            class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-100"
                        >
                            <option value="fa-road">Road Department</option>
                            <option value="fa-hospital">Health</option>
                            <option value="fa-school">Education</option>
                            <option value="fa-tree">Environment</option>
                            <option value="fa-water">Water Supply</option>
                            <option value="fa-bolt">Electricity</option>
                            <option value="fa-trash">Waste Management</option>
                            <option value="fa-shield-alt">Public Safety</option>
                        </select>
                    </div>

                    <!-- Department Color -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Department Color
                        </label>

                        <div class="flex items-center gap-4">
                            <input
                                type="color"
                                v-model="form.color"
                                class="h-12 w-16 cursor-pointer rounded-lg border"
                            />

                            <span class="text-sm text-gray-500">
                                {{ form.color }}
                            </span>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Department Status
                        </label>

                        <div class="flex gap-3">
                            <button
                                type="button"
                                @click="form.status='active'"
                                :class="[
                                    'rounded-xl px-4 py-2 text-sm font-medium transition',
                                    form.status === 'active'
                                        ? 'bg-red-500 text-white'
                                        : 'border border-gray-300 text-gray-600'
                                ]"
                            >
                                Active
                            </button>

                            <button
                                type="button"
                                @click="form.status='inactive'"
                                :class="[
                                    'rounded-xl px-4 py-2 text-sm font-medium transition',
                                    form.status === 'inactive'
                                        ? 'bg-red-500 text-white'
                                        : 'border border-gray-300 text-gray-600'
                                ]"
                            >
                                Inactive
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Description -->
                <div class="mt-6">
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Description
                    </label>

                    <textarea
                        v-model="form.description"
                        rows="5"
                        maxlength="1000"
                        placeholder="Describe department responsibilities..."
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-red-500 focus:ring-2 focus:ring-red-100"
                    />

                    <div class="mt-2 flex justify-end">
                        <span class="text-xs text-gray-500">
                            {{ form.description.length }}/1000
                        </span>
                    </div>

                    <p
                        v-if="form.errors.description"
                        class="mt-2 text-sm text-red-500"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>

                <!-- Preview -->
                <div class="mt-8 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h4 class="mb-4 text-sm font-semibold text-gray-900">
                        Department Preview
                    </h4>

                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl text-white"
                            :style="{ backgroundColor: form.color }"
                        >
                            <span class="font-bold">D</span>
                        </div>

                        <div class="flex-1">
                            <h5 class="font-medium text-gray-900">
                                {{ form.name || 'Department Preview' }}
                            </h5>

                            <p class="text-sm text-gray-500">
                                {{ form.description || 'Department description will appear here.' }}
                            </p>
                        </div>

                        <span
                            :class="[
                                'rounded-full px-3 py-1 text-xs font-medium',
                                form.status === 'active'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-600'
                            ]"
                        >
                            {{ form.status }}
                        </span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-8 flex justify-end gap-3 border-t border-gray-200 pt-6">
                    <button
                        type="button"
                        @click="closeModal"
                        class="rounded-xl border border-gray-300 px-5 py-3 text-gray-700 transition hover:bg-gray-50"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-red-500 px-6 py-3 font-medium text-white transition hover:bg-red-600 disabled:opacity-50"
                    >
                        <span v-if="form.processing">
                            Saving...
                        </span>

                        <span v-else>
                            {{ isEditMode ? 'Update Department' : 'Create Department' }}
                        </span>
                    </button>
                </div>

            </form>
        </div>
    </Modal>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
    show: Boolean,
    category: Object,
    departments: Array,
})

const emit = defineEmits(['close', 'success', 'submitting'])

const isEditMode = computed(() => !!props.category)

const form = useForm({
    department_id: '',
    name: '',
    description: '',
    icon: 'fa-tag',
    color: '#2563EB',
    priority: 'Medium',
})

watch(() => props.category, (cat) => {
    if (cat) {
        form.department_id = cat.department_id || ''
        form.name = cat.name || ''
        form.description = cat.description || ''
        form.icon = cat.icon || 'fa-tag'
        form.color = cat.color || '#2563EB'
        form.priority = cat.priority || 'Medium'
    }
}, { immediate: true })

function closeModal() {
    form.reset()
    emit('close')
}

function submitForm() {
    const payload = { ...form }

    if (isEditMode.value) {
        form.put(route('complaint-categories.update', props.category.id), {
            onSuccess: () => {
                emit('success')
                closeModal()
            }
        })
    } else {
        form.post(route('complaint-categories.store'), {
            onSuccess: () => {
                emit('success')
                closeModal()
            }
        })
    }
}
</script>
