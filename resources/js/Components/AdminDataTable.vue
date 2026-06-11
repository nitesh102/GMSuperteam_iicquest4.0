<script setup>
import { ref, computed } from 'vue'
import { ChevronUpDownIcon, ChevronDownIcon, ChevronUpIcon } from '@heroicons/vue/24/outline'
import AdminPagination from '@/Components/AdminPagination.vue'

const props = defineProps({
  columns: Array,
  rows: Array,
  searchFields: Array,
  pageSize: {
    type: Number,
    default: 10,
  },
})

const currentPage = ref(1)
const searchQuery = ref('')
const sortField = ref(null)
const sortOrder = ref('asc')

const filteredRows = computed(() => {
  let filtered = props.rows

  if (searchQuery.value && props.searchFields?.length > 0) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(row =>
      props.searchFields.some(field =>
        String(row[field]).toLowerCase().includes(query)
      )
    )
  }

  if (sortField.value) {
    filtered.sort((a, b) => {
      const aVal = a[sortField.value]
      const bVal = b[sortField.value]

      if (aVal === bVal) return 0
      const comparison = aVal < bVal ? -1 : 1
      return sortOrder.value === 'asc' ? comparison : -comparison
    })
  }

  return filtered
})

const paginatedRows = computed(() => {
  const start = (currentPage.value - 1) * props.pageSize
  const end = start + props.pageSize
  return filteredRows.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredRows.value.length / props.pageSize)
})

const toggleSort = (field) => {
  if (sortField.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortOrder.value = 'asc'
  }
  currentPage.value = 1
}

const getSortIcon = (field) => {
  if (sortField.value !== field) return ChevronUpDownIcon
  return sortOrder.value === 'asc' ? ChevronUpIcon : ChevronDownIcon
}
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:shadow-lg">
    <!-- Search -->
    <slot name="header">
      <div class="border-b border-gray-200 p-5">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search..."
          class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 px-4 text-sm placeholder-gray-400 transition-all duration-200 focus:border-red-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-500/10"
        />
      </div>
    </slot>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wide text-gray-500"
            >
              <button
                v-if="column.sortable !== false"
                @click="toggleSort(column.key)"
                class="flex items-center gap-2 transition hover:text-gray-800"
              >
                {{ column.label }}
                <component :is="getSortIcon(column.key)" class="h-4 w-4" />
              </button>
              <span v-else>{{ column.label }}</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, index) in paginatedRows"
            :key="index"
            class="border-b border-gray-100 transition hover:bg-gray-50"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4 text-gray-700"
            >
              <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                {{ row[column.key] }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="px-6 py-4 border-t border-gray-200">
      <AdminPagination
        :current-page="currentPage"
        :total-pages="totalPages"
        :total-items="filteredRows.length"
        :page-size="pageSize"
        @update:current-page="currentPage = $event"
      />
    </div>
  </div>
</template>
