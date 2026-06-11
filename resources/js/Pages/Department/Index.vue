<script setup>
import { ref, computed, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminDataTable from '@/Components/AdminDataTable.vue'
import DepartmentModal from './DepartmentModal.vue'
import { useLocalizedContent } from '@/Composables/useLocalizedContent'
import { useVoicePageHandlers } from '@/Composables/useVoiceContext'
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'
import BackButton from '@/Components/common/BackButton.vue'

const { t } = useI18n()
const { localizeDepartments, statusLabel, formatDate } = useLocalizedContent()

const page = usePage()
const items = ref([...(page.props.departments ?? [])])

watch(() => page.props.departments, (v) => {
    items.value = [...(v ?? [])]
})

const flashMessage = ref(null)
let flashTimer = null
watch(() => page.props.flash?.success, (val) => {
    if (flashTimer) clearTimeout(flashTimer)
    flashMessage.value = val || null
    if (flashMessage.value) {
        flashTimer = setTimeout(() => flashMessage.value = null, 4000)
    }
}, { immediate: true })

const showModal = ref(false)
const selectedDepartment = ref(null)

const tableRows = computed(() =>
    localizeDepartments(items.value).map(dept => ({
        ...dept,
        created_on: formatDate(dept.created_at),
        status: statusLabel(dept.status || 'active'),
    }))
)

const columns = computed(() => [
    { key: 'name', label: t('department.colName') },
    { key: 'description', label: t('department.colDescription') },
    { key: 'created_on', label: t('department.colCreatedOn'), sortable: true },
    { key: 'status', label: t('department.colStatus') },
    { key: 'actions', label: t('department.colActions'), sortable: false },
])

const totalDepartments = computed(() => items.value.length)

function openCreateModal() {
    selectedDepartment.value = null
    showModal.value = true
}

function openEditModal(dept) {
    selectedDepartment.value = items.value.find(d => d.id === dept.id) ?? dept
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    selectedDepartment.value = null
}

function deleteDepartment(dept) {
    const original = items.value.find(d => d.id === dept.id) ?? dept
    if (!confirm(t('department.deleteConfirm', { name: original.name }))) return

    items.value = items.value.filter(d => d.id !== dept.id)
    router.delete(route('departments.destroy', dept.id), {
        preserveScroll: true,
        onError: () => router.reload(),
    })
}

function handleSuccess() {
    closeModal()
    router.reload({ only: ['departments'] })
}

useVoicePageHandlers({
    onOpenCreate: (target) => {
        if (!target || target === 'department' || target === 'auto') {
            openCreateModal()
        }
    },
}, 'department')
</script>

<template>
    <Head :title="t('department.title')" />

    <AdminLayout>
        <Teleport to="body">
            <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="translate-x-full opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-full opacity-0">
                <div v-if="flashMessage" class="fixed top-5 right-5 z-50 flex items-center gap-3 rounded-xl bg-white border border-green-200 shadow-lg px-5 py-3.5 cursor-pointer" @click="flashMessage = null">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600 flex-shrink-0">
                        <i class="fas fa-check text-sm"></i>
                    </span>
                    <p class="text-sm font-medium text-gray-800">{{ flashMessage }}</p>
                </div>
            </Transition>
        </Teleport>

        <div class="mb-8">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <BackButton />
                    <div>
                        <h1 class="text-3xl font-semibold text-gray-900">{{ t('department.title') }}</h1>
                        <p class="text-sm text-gray-500 mt-1">{{ t('department.subtitle') }}</p>
                    </div>
                </div>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-red-500 to-red-600 px-6 py-3 text-sm font-medium text-white shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200"
                >
                    <PlusIcon class="h-5 w-5" />
                    {{ t('department.newDepartment') }}
                </button>
            </div>
        </div>

        <div class="mb-6 rounded-xl bg-white border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">{{ t('department.totalDepartments') }}</p>
                    <p class="text-3xl font-semibold text-gray-900 mt-1">{{ totalDepartments }}</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-indigo-50">
                    <i class="fas fa-building text-xl text-indigo-500"></i>
                </div>
            </div>
        </div>

        <AdminDataTable
            :columns="columns"
            :rows="tableRows"
            :search-fields="['name', 'description']"
            :page-size="10"
        >
            <template #cell-description="{ value }">
                <span class="line-clamp-2 max-w-md text-gray-500 text-sm">{{ value || '—' }}</span>
            </template>

            <template #cell-status="{ value }">
                <span class="inline-flex items-center gap-2 rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700 ring-1 ring-green-600/20">
                    <span class="h-2 w-2 rounded-full bg-green-500" />
                    {{ value }}
                </span>
            </template>

            <template #cell-actions="{ row }">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="openEditModal(row)"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-50 hover:text-blue-700 transition-all duration-200"
                        :title="t('department.edit')"
                    >
                        <PencilIcon class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        @click="deleteDepartment(row)"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700 transition-all duration-200"
                        :title="t('department.delete')"
                    >
                        <TrashIcon class="h-4 w-4" />
                    </button>
                </div>
            </template>
        </AdminDataTable>

        <DepartmentModal
            :show="showModal"
            :department="selectedDepartment"
            @close="closeModal"
            @success="handleSuccess"
        />
    </AdminLayout>
</template>
