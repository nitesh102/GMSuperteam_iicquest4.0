```vue
<script setup>
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
    show: Boolean,
    category: Object,
    departments: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['close', 'success'])

const isEditMode = computed(() => !!props.category)

const form = useForm({
    department_id: '',
    name: '',
    description: '',
    icon: 'fa-tag',
    priority: 'Medium',
    status: 'Active',
})

watch(
    () => props.category,
    (category) => {
        if (category) {
            form.department_id = category.department_id || ''
            form.name = category.name || ''
            form.description = category.description || ''
            form.icon = category.icon || 'fa-tag'
            form.priority = category.priority || 'Medium'
            form.status = category.status || 'Active'
        } else {
            form.reset()
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
            route('complaint-categories.update', props.category.id),
            {
                onSuccess: () => {
                    emit('success')
                    closeModal()
                },
            }
        )
    } else {
        form.post(route('complaint-categories.store'), {
            onSuccess: () => {
                emit('success')
                closeModal()
            },
        })
    }
}
</script>

<template>
    <Modal :show="show" @close="closeModal">
        <div class="mx-auto max-w-3xl rounded-2xl bg-white p-8 shadow-xl">
            <div class="border-b border-gray-200 pb-5">
                <h3 class="text-2xl font-semibold text-gray-900">
                    {{ isEditMode ? 'Edit Category' : 'Create Category' }}
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Manage complaint categories used by citizens when filing complaints.
                </p>
            </div>

            <form @submit.prevent="submitForm" class="mt-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Department
                        </label>

                        <select
                            v-model="form.department_id"
                            class="h-12 w-full rounded-xl border border-gray-300 px-4"
                        >
                            <option value="">Select Department</option>

                            <option
                                v-for="department in departments"
                                :key="department.id"
                                :value="department.id"
                            >
                                {{ department.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Category Name
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Road Damage"
                            class="h-12 w-full rounded-xl border border-gray-300 px-4"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Icon
                        </label>

                        <select
                            v-model="form.icon"
                            class="h-12 w-full rounded-xl border border-gray-300 px-4"
                        >
                            <option value="fa-road">Road</option>
                            <option value="fa-water">Water</option>
                            <option value="fa-school">Education</option>
                            <option value="fa-tree">Environment</option>
                            <option value="fa-hospital">Health</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Priority
                        </label>

                        <select
                            v-model="form.priority"
                            class="h-12 w-full rounded-xl border border-gray-300 px-4"
                        >
                            <option>Low</option>
                            <option>Medium</option>
                            <option>High</option>
                            <option>Critical</option>
                        </select>
                    </div>

                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>

                    <textarea
                        v-model="form.description"
                        rows="4"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3"
                    />
                </div>

                <div class="mt-6 flex gap-3">
                    <button
                        type="button"
                        @click="form.status='Active'"
                        :class="form.status === 'Active'
                            ? 'bg-green-500 text-white'
                            : 'border border-gray-300'"
                        class="rounded-lg px-4 py-2"
                    >
                        Active
                    </button>

                    <button
                        type="button"
                        @click="form.status='Inactive'"
                        :class="form.status === 'Inactive'
                            ? 'bg-red-500 text-white'
                            : 'border border-gray-300'"
                        class="rounded-lg px-4 py-2"
                    >
                        Inactive
                    </button>
                </div>

                <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                    <button
                        type="button"
                        @click="closeModal"
                        class="rounded-xl border border-gray-300 px-5 py-3"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-red-500 px-6 py-3 text-white"
                    >
                        {{ isEditMode ? 'Update Category' : 'Create Category' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
