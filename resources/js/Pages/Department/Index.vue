<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminDataTable from '@/Components/AdminDataTable.vue'
import PageHeader from '@/Components/PageHeader.vue'
import DepartmentModal from './DepartmentModal.vue'
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

const showModal = ref(false)
const selectedDepartment = ref(null)

const departments = ref([
  {
    id: 1,
    name: 'Health Department',
    description: 'Handles public health and sanitation issues.',
    created_on: 'May 20, 2024',
    status: 'Active',
  },
  {
    id: 2,
    name: 'Public Works',
    description: 'Handles roads, water supply, and maintenance.',
    created_on: 'May 18, 2024',
    status: 'Active',
  },
  {
    id: 3,
    name: 'Education',
    description: 'Handles schools, colleges, and education services.',
    created_on: 'May 15, 2024',
    status: 'Active',
  },
  {
    id: 4,
    name: 'Safety & Security',
    description: 'Handles public safety and law enforcement.',
    created_on: 'May 10, 2024',
    status: 'Active',
  },
])

const columns = [
  { key: 'name', label: 'Department Name' },
  { key: 'description', label: 'Description' },
  { key: 'created_on', label: 'Created On', sortable: true },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions', sortable: false },
]

const totalDepartments = departments.value.length

const openCreateModal = () => {
  selectedDepartment.value = null
  showModal.value = true
}

const openEditModal = (dept) => {
  selectedDepartment.value = dept
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedDepartment.value = null
}

const handleSuccess = () => {
  closeModal()
  // Refresh departments list if needed
}
</script>

<template>
  <Head title="Departments" />

  <AdminLayout>
    <PageHeader
      title="Departments"
      description="Manage departments for complaint routing and organization."
    >
      <template #actions>
        <button
          @click="openCreateModal"
          class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-6 py-3 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:scale-[1.02] hover:bg-red-600 hover:shadow-md"
        >
          <PlusIcon class="h-5 w-5" />
          New Department
        </button>
      </template>
    </PageHeader>

    <!-- Summary Card -->
    <div class="mb-6 rounded-2xl bg-white border border-gray-200 p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Total Departments</p>
          <p class="text-3xl font-semibold text-gray-900 mt-1">{{ totalDepartments }}</p>
        </div>
      </div>
    </div>

    <!-- Data Table -->
    <AdminDataTable
      :columns="columns"
      :rows="departments"
      :search-fields="['name', 'description']"
      page-size="10"
    >
      <template #cell-status="{ value }">
        <span class="inline-flex items-center gap-2 rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
          <span class="h-2 w-2 rounded-full bg-green-500" />
          {{ value }}
        </span>
      </template>

      <template #cell-actions="{ row }">
        <div class="flex items-center gap-3">
          <button
            @click="openEditModal(row)"
            class="rounded-lg p-2 text-blue-500 transition-all duration-200 hover:scale-110 hover:bg-blue-50 hover:text-blue-600"
          >
            <PencilIcon class="h-4 w-4" />
          </button>
          <button class="rounded-lg p-2 text-red-500 transition-all duration-200 hover:scale-110 hover:bg-red-50 hover:text-red-600">
            <TrashIcon class="h-4 w-4" />
          </button>
        </div>
      </template>
    </AdminDataTable>

    <!-- Department Modal -->
    <DepartmentModal
      :show="showModal"
      :department="selectedDepartment"
      @close="closeModal"
      @success="handleSuccess"
    />
  </AdminLayout>
</template>
