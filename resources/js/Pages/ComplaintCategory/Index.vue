<script setup>
import { ref, computed, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminDataTable from '@/Components/AdminDataTable.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ComplaintCategoryModal from '@/Pages/ComplaintCategory/ComplaintCategoryModal.vue'
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

const page = usePage()

const items = ref([...(page.props.categories ?? [])])
const departments = computed(() => page.props.departments ?? [])

watch(() => page.props.categories, (v) => {
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
const selectedCategory = ref(null)

function openCreateModal() {
    selectedCategory.value = null
    showModal.value = true
}

function openEditModal(category) {
    selectedCategory.value = category
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    selectedCategory.value = null
}

function deleteCategory(category) {
    if (!confirm(`Delete category "${category.name}"? This cannot be undone.`)) return
    items.value = items.value.filter(c => c.id !== category.id)
    router.delete(route('complaint-categories.destroy', category.id), {
        preserveScroll: true,
        onError: () => router.reload(),
    })
}

const totalCategories = computed(() => items.value.length)

const tableRows = computed(() =>
    items.value.map(cat => ({
        ...cat,
        department_name: cat.department?.name ?? '—',
    }))
)

const columns = [
    { key: 'name', label: 'Category Name' },
    { key: 'department_name', label: 'Department' },
    { key: 'description', label: 'Description' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Actions', sortable: false },
]
</script>

<template>
    <Head title="Complaint Categories" />

    <AdminLayout>
        <!-- Flash -->
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

        <PageHeader
            title="Complaint Categories"
            description="Manage complaint categories for departments."
        >
            <template #actions>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-6 py-3 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:scale-[1.02] hover:bg-red-600 hover:shadow-md"
                >
                    <PlusIcon class="h-5 w-5" />
                    New Category
                </button>
            </template>
        </PageHeader>

        <!-- Summary Card -->
        <div class="mb-6 rounded-2xl bg-white border border-gray-200 p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Categories</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ totalCategories }}</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-red-50">
                    <i class="fas fa-tags text-xl text-red-500"></i>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <AdminDataTable
            :columns="columns"
            :rows="tableRows"
            :search-fields="['name', 'department_name', 'description']"
            :page-size="10"
        >
            <template #cell-description="{ value }">
                <span class="line-clamp-1 max-w-xs text-gray-500 text-sm">{{ value || '—' }}</span>
            </template>

            <template #cell-status="{ value }">
                <span
                    v-if="value === 'active' || value == null"
                    class="inline-flex items-center gap-2 rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700"
                >
                    <span class="h-2 w-2 rounded-full bg-green-500" />
                    Active
                </span>
                <span
                    v-else
                    class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500"
                >
                    <span class="h-2 w-2 rounded-full bg-gray-400" />
                    Inactive
                </span>
            </template>

            <template #cell-actions="{ row }">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="openEditModal(row)"
                        class="rounded-lg p-2 text-blue-500 transition-all duration-200 hover:scale-110 hover:bg-blue-50 hover:text-blue-600"
                        title="Edit"
                    >
                        <PencilIcon class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        @click="deleteCategory(row)"
                        class="rounded-lg p-2 text-red-500 transition-all duration-200 hover:scale-110 hover:bg-red-50 hover:text-red-600"
                        title="Delete"
                    >
                        <TrashIcon class="h-4 w-4" />
                    </button>
                </div>
            </template>
        </AdminDataTable>

        <!-- Modal -->
        <ComplaintCategoryModal
            :show="showModal"
            :category="selectedCategory"
            :departments="departments"
            @close="closeModal"
            @success="closeModal"
        />
    </AdminLayout>
</template>
