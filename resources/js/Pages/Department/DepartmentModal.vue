<template>
    <Modal :show="show" @close="closeModal">
        <div class="bg-white rounded-2xl p-8 max-w-3xl mx-auto">

            <div class="flex items-center gap-4 mb-8">
                <div class="flex items-center gap-3 flex-shrink-0">
                    <AppLogo />
                </div>

                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900">
                        {{ isEditMode ? t('department.editTitle') : t('department.createTitle') }}
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        {{ t('department.modalSubtitle') }}
                    </p>
                </div>
            </div>

            <form @submit.prevent="submitForm">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ t('department.nameLabel') }}
                        </label>

                        <input
                            type="text"
                            v-model="form.name"
                            :placeholder="t('department.namePlaceholder')"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-4 focus:ring-blue-100 focus:border-blue-600 transition"
                        />

                        <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ t('department.iconLabel') }}
                        </label>

                        <select
                            v-model="form.icon"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-4 focus:ring-blue-100 focus:border-blue-600"
                        >
                            <option value="fa-road">🛣 {{ t('department.icons.road') }}</option>
                            <option value="fa-hospital">🏥 {{ t('department.icons.health') }}</option>
                            <option value="fa-school">🏫 {{ t('department.icons.education') }}</option>
                            <option value="fa-tree">🌳 {{ t('department.icons.environment') }}</option>
                            <option value="fa-water">💧 {{ t('department.icons.water') }}</option>
                            <option value="fa-bolt">⚡ {{ t('department.icons.electricity') }}</option>
                            <option value="fa-trash">🗑 {{ t('department.icons.waste') }}</option>
                            <option value="fa-shield-alt">🚓 {{ t('department.icons.safety') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ t('department.colorLabel') }}
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
                            {{ t('department.statusLabel') }}
                        </label>

                        <select v-model="form.status" class="w-full rounded-xl border border-slate-300 px-4 py-3">
                            <option value="active">{{ t('status.active') }}</option>
                            <option value="inactive">{{ t('status.inactive') }}</option>
                        </select>
                    </div>

                </div>

                <div class="mt-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ t('department.descriptionLabel') }}
                    </label>

                    <textarea
                        v-model="form.description"
                        rows="5"
                        maxlength="1000"
                        :placeholder="t('department.descriptionPlaceholder')"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 resize-none focus:ring-4 focus:ring-blue-100 focus:border-blue-600"
                    ></textarea>

                    <div class="mt-3">
                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div
                                class="h-full bg-blue-600 transition-all"
                                :style="{ width: `${(form.description.length / 1000) * 100}%` }"
                            ></div>
                        </div>

                        <div class="flex justify-between mt-2">
                            <span v-if="form.errors.description" class="text-red-600 text-sm">
                                {{ form.errors.description }}
                            </span>
                            <span class="ml-auto text-xs text-gray-500">
                                {{ form.description.length }}/1000
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 p-5 rounded-2xl border border-slate-200 bg-slate-50">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center text-white"
                            :style="{ backgroundColor: form.color }"
                        >
                            <i class="fas" :class="form.icon"></i>
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-900">
                                {{ form.name || t('department.previewTitle') }}
                            </h4>
                            <p class="text-sm text-gray-500">
                                {{ form.description || t('department.previewDesc') }}
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
                        {{ t('department.cancel') }}
                    </button>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium shadow-md transition flex items-center gap-2 disabled:opacity-50"
                    >
                        <template v-if="form.processing">
                            <i class="fas fa-spinner fa-spin"></i>
                            {{ t('department.saving') }}
                        </template>

                        <template v-else>
                            <i class="fas" :class="isEditMode ? 'fa-save' : 'fa-plus'"></i>
                            {{ isEditMode ? t('department.update') : t('department.create') }}
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
    department: Object,
})

const emit = defineEmits(['close', 'success', 'submitting'])

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
        form.put(route('departments.update', props.department.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('success')
                closeModal()
            },
        })
    } else {
        form.post(route('departments.store'), {
            preserveScroll: true,
            onSuccess: () => {
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
    voiceHandlers.pageContext = 'department';
}

function unregisterVoiceHandlers() {
    if (voiceHandlers.onFillField === handleVoiceFillField) {
        voiceHandlers.onFillField = null;
    }
    if (voiceHandlers.onSubmitForm === submitForm) {
        voiceHandlers.onSubmitForm = null;
    }
    if (voiceHandlers.pageContext === 'department') {
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

<style scoped>
.modal-logo :deep(.logo-image) {
    height: 40px;
}

.modal-logo :deep(.brand-text) {
    font-size: 20px;
}
</style>
