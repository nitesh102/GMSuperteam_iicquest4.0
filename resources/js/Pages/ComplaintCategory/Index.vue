<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminDataTable from '@/Components/AdminDataTable.vue'
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

const categories = ref([
  {
    id: 1,
    name: 'Road Damage',
    department: 'Public Works',
    status: 'Active',
  },
  {
    id: 2,
    name: 'Water Supply',
    department: 'Public Works',
    status: 'Active',
  },
  {
    id: 3,
    name: 'Public Health',
    department: 'Health Department',
    status: 'Active',
  },
  {
    id: 4,
    name: 'School Issues',
    department: 'Education',
    status: 'Active',
  },
])

const columns = [
  { key: 'name', label: 'Category Name' },
  { key: 'department', label: 'Department' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions', sortable: false },
]

const totalCategories = categories.value.length
</script>

<template>
  <Head title="Complaint Categories" />

  <AdminLayout>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-semibold text-gray-900">Complaint Categories</h1>
          <p class="text-sm text-gray-500 mt-1">Manage complaint categories for departments.</p>
        </div>
        <Link
          href="categories.create"
          class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-6 py-3 text-sm font-medium text-white hover:bg-red-600 transition"
        >
          <PlusIcon class="h-5 w-5" />
          New Category
        </Link>
      </div>
    </div>

    <!-- Summary Card -->
    <div class="mb-6 rounded-xl bg-white border border-gray-200 p-6 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Total Categories</p>
          <p class="text-3xl font-semibold text-gray-900 mt-1">{{ totalCategories }}</p>
        </div>
      </div>
    </div>

    <!-- Data Table -->
    <AdminDataTable
      :columns="columns"
      :rows="categories"
      :search-fields="['name', 'department']"
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
