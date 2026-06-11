<template>
    <Modal :show="show" @close="closeModal">
        <div class="bg-white rounded-2xl p-8 max-w-3xl mx-auto">

            <!-- Header -->
            <div class="flex items-center gap-4 mb-8">
                <!-- Logo Branding -->
                <AppLogo class="modal-logo flex-shrink-0" />

                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900">
                        {{ isEditMode ? 'Edit Department' : 'Create Department' }}
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Manage departments responsible for handling civic complaints.
                    </p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Department Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Department Name
                        </label>

                        <input
                            type="text"
                            v-model="form.name"
                            placeholder="Road Maintenance Department"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-4 focus:ring-blue-100 focus:border-blue-600 transition"
                        />

                        <p
                            v-if="form.errors.name"
                            class="mt-2 text-sm text-red-600">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Department Icon -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Department Icon
                        </label>

                        <select
                            v-model="form.icon"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-4 focus:ring-blue-100 focus:border-blue-600">

                            <option value="fa-road">🛣 Road Department</option>
                            <option value="fa-hospital">🏥 Health</option>
                            <option value="fa-school">🏫 Education</option>
                            <option value="fa-tree">🌳 Environment</option>
                            <option value="fa-water">💧 Water Supply</option>
                            <option value="fa-bolt">⚡ Electricity</option>
                            <option value="fa-trash">🗑 Waste Management</option>
                            <option value="fa-shield-alt">🚓 Public Safety</option>

                        </select>
                    </div>

                    <!-- Department Color -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Department Color
                        </label>

                        <div class="flex items-center gap-4">
                            <input
                                type="color"
                                v-model="form.color"
                                class="w-16 h-12 border rounded-lg cursor-pointer"
                            />

                            <span
                                class="text-sm font-medium text-gray-500">
                                {{ form.color }}
                            </span>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Department Status
                        </label>

                        <select
                            v-model="form.status"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3">

                            <option value="active">
                                Active
                            </option>

                            <option value="inactive">
                                Inactive
                            </option>

                        </select>
                    </div>

                </div>

                <!-- Description -->
                <div class="mt-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Description
                    </label>

                    <textarea
                        v-model="form.description"
                        rows="5"
                        maxlength="1000"
                        placeholder="Describe department responsibilities..."
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 resize-none focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                    </textarea>

                    <!-- Progress -->
                    <div class="mt-3">
                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div
                                class="h-full bg-blue-600 transition-all"
                                :style="{ width: `${(form.description.length / 1000) * 100}%` }">
                            </div>
                        </div>

                        <div class="flex justify-between mt-2">
                            <span
                                v-if="form.errors.description"
                                class="text-red-600 text-sm">
                                {{ form.errors.description }}
                            </span>

                            <span
                                class="ml-auto text-xs text-gray-500">
                                {{ form.description.length }}/1000
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Preview Card -->
                <div
                    class="mt-8 p-5 rounded-2xl border border-slate-200 bg-slate-50">

                    <div class="flex items-center gap-4">

                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center text-white"
                            :style="{ backgroundColor: form.color }">

                            <i
                                class="fas"
                                :class="form.icon">
                            </i>

                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-900">
                                {{ form.name || 'Department Preview' }}
                            </h4>

                            <p class="text-sm text-gray-500">
                                {{ form.description || 'Department description will appear here.' }}
                            </p>
                        </div>

                    </div>

                </div>

                <!-- Footer -->
                <div
                    class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-200">

                    <button
                        type="button"
                        @click="closeModal"
                        class="px-5 py-3 rounded-xl border border-slate-300 text-gray-700 font-medium hover:bg-slate-50 transition">

                        Cancel

                    </button>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium shadow-md transition flex items-center gap-2 disabled:opacity-50">

                        <template v-if="form.processing">
                            <i class="fas fa-spinner fa-spin"></i>
                            Saving...
                        </template>

                        <template v-else>
                            <i
                                class="fas"
                                :class="isEditMode ? 'fa-save' : 'fa-plus'">
                            </i>

                            {{ isEditMode ? 'Update Department' : 'Create Department' }}
                        </template>

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
import AppLogo from '@/Components/common/AppLogo.vue'

const props = defineProps({
    show: Boolean,
    department: Object,
})

const emit = defineEmits([
    'close',
    'success',
    'submitting'
])

const isEditMode = computed(() => !!props.department)

const form = useForm({
    name: '',
    description: '',
    icon: 'fa-building',
    color: '#2563EB',
    status: 'active',
})

watch(
    () => props.department,
    (dept) => {
        if (dept) {
            form.name = dept.name || ''
            form.description = dept.description || ''
            form.icon = dept.icon || 'fa-building'
            form.color = dept.color || '#2563EB'
            form.status = dept.status || 'active'
        }
    },
    { immediate: true }
)

function closeModal() {
    form.reset()

    emit('close')
}

function submitForm() {
    if (isEditMode.value) {
        form.put(
            route(
                'departments.update',
                props.department.id
            ),
            {
                preserveScroll: true,

                onSuccess: () => {
                    emit('success')
                    closeModal()
                },
            }
        )
    } else {
        form.post(
            route('departments.store'),
            {
                preserveScroll: true,

                onSuccess: () => {
                    emit('success')
                    closeModal()
                },
            }
        )
    }
}
</script>

<style scoped>
.modal-logo :deep(.logo-image) {
    height: 40px;
}

.modal-logo :deep(.brand-text) {
    font-size: 20px;
}
</style>
