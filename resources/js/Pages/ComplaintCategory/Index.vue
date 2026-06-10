<script setup>
import { ref, computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminDataTable from '@/Components/AdminDataTable.vue'
import ComplaintCategoryModal from '@/Complaint/ComplaintCategoryModal.vue'
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

const page = usePage()

const categories = computed(() => page.props.categories || [])
const departments = computed(() => page.props.departments || [])

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

const columns = [
    { key: 'name', label: 'Category Name' },
    { key: 'department_name', label: 'Department' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Actions', sortable: false },
]

const tableRows = computed(() =>
    categories.value.map(category => ({
        ...category,
        department_name: category.department?.name ?? '-',
    }))
)
</script>

<template>
  <Head title="Complaint Categories" />

  <AdminLayout>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-semibold text-gray-900">
            Complaint Categories
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            Manage complaint categories for departments.
          </p>
        </div>

<button
    @click="openCreateModal"
    class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-6 py-3 text-sm font-medium text-white transition hover:bg-red-600"
>
    <PlusIcon class="h-5 w-5" />
    New Category
</button>

      </div>
    </div>

    <!-- Summary Card -->
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Total Categories</p>
          <p class="mt-1 text-3xl font-semibold text-gray-900">
            {{ totalCategories }}
          </p>
        </div>
      </div>
    </div>

    <!-- Data Table -->

<AdminDataTable
    :columns="columns"
    :rows="tableRows"
    :page-size="10"
>
    <template #cell-status="{ value }">
        <span
            class="inline-flex items-center gap-2 rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700"
        >
            <span class="h-2 w-2 rounded-full bg-green-500" />
            {{ value }}
        </span>
    </template>

    <template #cell-actions="{ row }">
        <div class="flex items-center gap-3">
            <button
                type="button"
                @click="openEditModal(row)"
                class="text-blue-500 transition hover:text-blue-600"
            >
                <PencilIcon class="h-4 w-4" />
            </button>

            <button
                type="button"
                class="text-red-500 transition hover:text-red-600"
            >
                <TrashIcon class="h-4 w-4" />
            </button>
        </div>
    </template>
</AdminDataTable>

<ComplaintCategoryModal
    :show="showModal"
    :category="selectedCategory"
    :departments="departments"
    @close="closeModal"
    @success="closeModal"
/>


  </AdminLayout>
</template>

