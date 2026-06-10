<template>
    <Modal :show="show" @close="closeModal">

        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-3xl mx-auto">

            <!-- HEADER -->
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center text-white">
                    <i class="fas fa-tags"></i>
                </div>

                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        {{ isEditMode ? 'Edit Category' : 'Create Category' }}
                    </h3>
                    <p class="text-sm text-gray-500">
                        Manage complaint categories under departments
                    </p>
                </div>
            </div>

            <!-- STATS -->
            <div class="grid grid-cols-3 gap-3 mb-6">
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-3">
                    <p class="text-xs text-gray-500">Complaints</p>
                    <h3 class="text-lg font-bold text-blue-600">1,245</h3>
                </div>

                <div class="bg-green-50 border border-green-100 rounded-xl p-3">
                    <p class="text-xs text-gray-500">Resolved</p>
                    <h3 class="text-lg font-bold text-green-600">1,010</h3>
                </div>

                <div class="bg-red-50 border border-red-100 rounded-xl p-3">
                    <p class="text-xs text-gray-500">Pending</p>
                    <h3 class="text-lg font-bold text-red-600">235</h3>
                </div>
            </div>

            <!-- FORM -->
            <form @submit.prevent="submitForm" class="space-y-5">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- Department -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">
                            Department *
                        </label>

                        <select
                            v-model="form.department_id"
                            class="w-full rounded-xl border px-4 py-3 focus:ring-4 focus:ring-blue-100 focus:border-blue-600">

                            <option value="">Select Department</option>

                            <option
                                v-for="dept in departments"
                                :key="dept.id"
                                :value="dept.id">

                                {{ dept.name }}

                            </option>

                        </select>
                    </div>

                    <!-- Category Name -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">
                            Category Name *
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="e.g. Water Supply Issue"
                            class="w-full rounded-xl border px-4 py-3 focus:ring-4 focus:ring-blue-100 focus:border-blue-600"
                        />
                    </div>

                    <!-- Icon -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">
                            Icon
                        </label>

                        <select
                            v-model="form.icon"
                            class="w-full rounded-xl border px-4 py-3">

                            <option value="fa-water">💧 Water</option>
                            <option value="fa-road">🛣 Road</option>
                            <option value="fa-trash">🗑 Waste</option>
                            <option value="fa-bolt">⚡ Electricity</option>
                            <option value="fa-tree">🌳 Environment</option>
                            <option value="fa-hospital">🏥 Health</option>

                        </select>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">
                            Priority
                        </label>

                        <select
                            v-model="form.priority"
                            class="w-full rounded-xl border px-4 py-3">

                            <option>Low</option>
                            <option>Medium</option>
                            <option>High</option>
                            <option>Critical</option>

                        </select>
                    </div>

                    <!-- Color -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">
                            Theme Color
                        </label>

                        <input
                            type="color"
                            v-model="form.color"
                            class="w-20 h-12 border rounded-lg"
                        />
                    </div>

                </div>

                <!-- DESCRIPTION -->
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-2 block">
                        Description
                    </label>

                    <textarea
                        v-model="form.description"
                        rows="4"
                        maxlength="1000"
                        class="w-full rounded-xl border px-4 py-3 focus:ring-4 focus:ring-blue-100"
                        placeholder="Describe category purpose...">
                    </textarea>

                    <!-- PROGRESS -->
                    <div class="mt-2">
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div
                                class="h-full bg-blue-600 transition-all"
                                :style="{ width: `${(form.description.length / 1000) * 100}%` }">
                            </div>
                        </div>

                        <p class="text-xs text-right text-gray-500 mt-1">
                            {{ form.description.length }}/1000
                        </p>
                    </div>
                </div>

                <!-- LIVE PREVIEW -->
                <div class="bg-gray-50 border rounded-2xl p-5">

                    <h4 class="font-semibold mb-3 text-gray-800">
                        Live Preview
                    </h4>

                    <div class="flex gap-4">

                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center text-white"
                            :style="{ backgroundColor: form.color }">

                            <i class="fas" :class="form.icon"></i>

                        </div>

                        <div class="flex-1">

                            <div class="flex justify-between">

                                <h3 class="font-semibold text-gray-900">
                                    {{ form.name || 'Category Name' }}
                                </h3>

                                <span
                                    class="px-2 py-1 rounded-full text-xs"
                                    :class="{
                                        'bg-green-100 text-green-700': form.priority === 'Low',
                                        'bg-yellow-100 text-yellow-700': form.priority === 'Medium',
                                        'bg-orange-100 text-orange-700': form.priority === 'High',
                                        'bg-red-100 text-red-700': form.priority === 'Critical'
                                    }">

                                    {{ form.priority }}

                                </span>

                            </div>

                            <p class="text-sm text-gray-500 mt-1">
                                {{ form.description || 'Category description preview...' }}
                            </p>

                            <p class="text-xs text-gray-400 mt-2">
                                Department:
                                {{
                                    departments.find(
                                        d => d.id == form.department_id
                                    )?.name || 'Not selected'
                                }}
                            </p>

                        </div>

                    </div>

                </div>

                <!-- FOOTER -->
                <div class="flex justify-end gap-3 pt-5 border-t">

                    <button
                        type="button"
                        @click="closeModal"
                        class="px-5 py-2.5 rounded-xl border hover:bg-gray-50">

                        Cancel

                    </button>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50">

                        <span v-if="form.processing">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Saving...
                        </span>

                        <span v-else>
                            <i class="fas fa-save mr-2"></i>
                            {{ isEditMode ? 'Update' : 'Create' }}
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
