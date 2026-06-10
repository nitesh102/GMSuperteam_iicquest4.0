<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminDataTable from '@/Components/AdminDataTable.vue'
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

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
</script>

<template>
  <Head title="Departments" />

  <AdminLayout>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-semibold text-gray-900">Departments</h1>
          <p class="text-sm text-gray-500 mt-1">Manage all departments in your system.</p>
        </div>
        <Link
          href="departments.create"
          class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-6 py-3 text-sm font-medium text-white hover:bg-red-600 transition"
        >
          <PlusIcon class="h-5 w-5" />
          New Department
        </Link>
      </div>
    </div>

    <!-- Summary Card -->
    <div class="mb-6 rounded-xl bg-white border border-gray-200 p-6 shadow-sm">
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

      <template #cell-actions>
        <div class="flex items-center gap-3">
          <button class="text-blue-500 hover:text-blue-600 font-medium text-sm transition">
            <PencilIcon class="h-4 w-4" />
          </button>
          <button class="text-red-500 hover:text-red-600 font-medium text-sm transition">
            <TrashIcon class="h-4 w-4" />
          </button>
        </div>
      </template>
    </AdminDataTable>
  </AdminLayout>
</template>
