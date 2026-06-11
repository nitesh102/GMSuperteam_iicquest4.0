<template>
    <Modal :show="show" @close="closeModal">
        <div class="bg-white rounded-2xl p-8 max-w-3xl mx-auto">

            <div class="flex items-center gap-4 mb-8">
                <div class="flex items-center gap-3 flex-shrink-0">
                    <svg class="h-10 w-10" viewBox="0 0 100 100">
                        <path d="M 50 10 A 40 40 0 0 0 50 90" fill="none" stroke="#1d2a42" stroke-width="5" />
                        <path d="M 50 10 A 40 40 0 0 1 50 90" fill="none" stroke="#dc2626" stroke-width="5" />
                        <path d="M50,10 L50,-5 L58,-1 L50,3 L58,7 L50,11" fill="#dc2626" />
                        <path d="M35,30 L65,30 L50,18 Z" fill="#1d2a42" />
                        <rect x="38" y="32" width="4" height="14" fill="#1d2a42" />
                        <rect x="48" y="32" width="4" height="14" fill="#1d2a42" />
                        <rect x="58" y="32" width="4" height="14" fill="#1d2a42" />
                        <rect x="35" y="46" width="30" height="3" fill="#1d2a42" />
                        <circle cx="40" cy="55" r="4" fill="#dc2626" />
                        <path d="M35,66 C35,60 45,60 45,66 Z" fill="#dc2626" />
                        <circle cx="50" cy="53" r="5" fill="#1d2a42" />
                        <path d="M43,66 C43,58 57,58 57,66 Z" fill="#1d2a42" />
                        <circle cx="60" cy="56" r="4" fill="#1d2a42" />
                        <path d="M55,66 C55,61 65,61 65,66 Z" fill="#1d2a42" />
                        <path d="M36,65 Q50,80 64,65 Q50,84 36,65" fill="none" stroke="#1d2a42" stroke-width="3" />
                    </svg>
                    <AppLogo />
                </div>
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900">
                        {{ isEditMode ? t('category.editTitle') : t('category.createTitle') }}
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ t('category.modalSubtitle') }}
                    </p>
                </div>
            </div>

            <form @submit.prevent="submitForm">

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ t('category.departmentLabel') }} <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.department_id"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-4 focus:ring-blue-100 focus:border-blue-600 transition"
                        required
                    >
                        <option value="" disabled>{{ t('category.selectDepartment') }}</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.department_id" class="mt-2 text-sm text-red-600">
                        {{ form.errors.department_id }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ t('category.nameLabel') }}
                        </label>
                        <input
                            type="text"
                            v-model="form.name"
                            :placeholder="t('category.namePlaceholder')"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-4 focus:ring-blue-100 focus:border-blue-600 transition"
                        />
                        <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ t('category.iconLabel') }}
                        </label>
                        <select
                            v-model="form.icon"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-4 focus:ring-blue-100 focus:border-blue-600"
                        >
                            <option value="fa-tag">🏷 {{ t('category.icons.general') }}</option>
                            <option value="fa-road">🛣 {{ t('category.icons.road') }}</option>
                            <option value="fa-water">💧 {{ t('category.icons.water') }}</option>
                            <option value="fa-bolt">⚡ {{ t('category.icons.electricity') }}</option>
                            <option value="fa-tree">🌳 {{ t('category.icons.environment') }}</option>
                            <option value="fa-hospital">🏥 {{ t('category.icons.health') }}</option>
                            <option value="fa-school">🏫 {{ t('category.icons.education') }}</option>
                            <option value="fa-trash">🗑 {{ t('category.icons.waste') }}</option>
                            <option value="fa-shield-alt">🚓 {{ t('category.icons.safety') }}</option>
                            <option value="fa-home">🏠 {{ t('category.icons.housing') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ t('category.colorLabel') }}
                        </label>
                        <div class="flex items-center gap-4">
                            <input
                                type="color"
                                v-model="form.color"
                                class="w-16 h-12 border rounded-lg cursor-pointer"
                            />
                            <span class="text-sm font-medium text-gray-500">{{ form.color }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ t('category.statusLabel') }}
                        </label>
                        <select v-model="form.status" class="w-full rounded-xl border border-slate-300 px-4 py-3">
                            <option value="active">{{ t('status.active') }}</option>
                            <option value="inactive">{{ t('status.inactive') }}</option>
                        </select>
                    </div>

                </div>

                <div class="mt-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ t('category.descriptionLabel') }}
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="5"
                        maxlength="1000"
                        :placeholder="t('category.descriptionPlaceholder')"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 resize-none focus:ring-4 focus:ring-blue-100 focus:border-blue-600"
                    ></textarea>
                    <div class="mt-3">
                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div
                                class="h-full bg-blue-600 transition-all"
                                :style="{ 'inline-size': `${(form.description.length / 1000) * 100}%` }"
                            ></div>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span v-if="form.errors.description" class="text-red-600 text-sm">
                                {{ form.errors.description }}
                            </span>
                            <span class="ml-auto text-xs text-gray-500">{{ form.description.length }}/1000</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 p-5 rounded-2xl border border-slate-200 bg-slate-50">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center text-white flex-shrink-0"
                            :style="{ backgroundColor: form.color }"
                        >
                            <i class="fas" :class="form.icon"></i>
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-semibold text-gray-900 truncate">
                                {{ form.name || t('category.previewTitle') }}
                            </h4>
                            <p class="text-sm text-gray-500 truncate">
                                {{ selectedDepartmentName
                                    ? t('category.previewDept', { name: selectedDepartmentName })
                                    : t('category.selectDeptHint') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-200">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-5 py-3 rounded-xl border border-slate-300 text-gray-700 font-medium hover:bg-slate-50 transition"
                    >
                        {{ t('category.cancel') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium shadow-md transition flex items-center gap-2 disabled:opacity-50"
                    >
                        <template v-if="form.processing">
                            <i class="fas fa-spinner fa-spin"></i>
                            {{ t('category.saving') }}
                        </template>
                        <template v-else>
                            <i class="fas" :class="isEditMode ? 'fa-save' : 'fa-plus'"></i>
                            {{ isEditMode ? t('category.update') : t('category.create') }}
                        </template>
                    </button>
                </div>

            </form>
        </div>
    </Modal>
</template>

<script setup>
import { computed, watch, onMounted, onUnmounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Modal from '@/Components/Modal.vue'
import AppLogo from '@/Components/common/AppLogo.vue'
import { useVoicePageHandlers } from '@/Composables/useVoiceContext'
import { voiceHandlers } from '@/Composables/useVoiceContext'

const { t } = useI18n()

const props = defineProps({
    show: Boolean,
    category: Object,
    departments: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['close', 'success', 'submitting'])

const isEditMode = computed(() => !!props.category)

const form = useForm({
    department_id: '',
    name: '',
    description: '',
    icon: 'fa-tag',
    color: '#2563EB',
    status: 'active',
})

const selectedDepartmentName = computed(() => {
    if (!form.department_id) return ''
    return props.departments.find(d => d.id == form.department_id)?.name ?? ''
})

watch(
    () => props.category,
    (cat) => {
        if (cat) {
            form.department_id = cat.department_id || ''
            form.name = cat.name || ''
            form.description = cat.description || ''
            form.icon = cat.icon || 'fa-tag'
            form.color = cat.color || '#2563EB'
            form.status = cat.status || 'active'
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
        form.put(route('complaint-categories.update', props.category.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('submitting', { ...form.data(), id: props.category.id })
                emit('success')
                closeModal()
            },
        })
    } else {
        form.post(route('complaint-categories.store'), {
            preserveScroll: true,
            onSuccess: () => {
                emit('submitting', { ...form.data() })
                emit('success')
                closeModal()
            },
        })
    }
}

function handleVoiceFillField(field, value) {
    if (field in form) {
        form[field] = value;
    }
}

function registerVoiceHandlers() {
    voiceHandlers.onFillField = handleVoiceFillField;
    voiceHandlers.onSubmitForm = submitForm;
    voiceHandlers.pageContext = 'category';
}

function unregisterVoiceHandlers() {
    if (voiceHandlers.onFillField === handleVoiceFillField) {
        voiceHandlers.onFillField = null;
    }
    if (voiceHandlers.onSubmitForm === submitForm) {
        voiceHandlers.onSubmitForm = null;
    }
    if (voiceHandlers.pageContext === 'category') {
        voiceHandlers.pageContext = null;
    }
}

watch(() => props.show, (isOpen) => {
    if (isOpen) {
        registerVoiceHandlers();
    } else {
        unregisterVoiceHandlers();
    }
}, { immediate: true });
</script>
