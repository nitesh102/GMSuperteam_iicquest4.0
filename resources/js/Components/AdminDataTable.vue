<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { ChevronUpDownIcon, ChevronDownIcon, ChevronUpIcon } from '@heroicons/vue/24/outline'
import AdminPagination from '@/Components/AdminPagination.vue'

const { t } = useI18n()

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
  return filteredRows.value.slice(start, start + props.pageSize)
})

const totalPages = computed(() =>
  Math.ceil(filteredRows.value.length / props.pageSize)
)

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
  <div class="card overflow-hidden">
    <slot name="header">
      <div class="p-5 border-b border-gray-100">
        <div class="relative">
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="t('table.search')"
            class="input-base pl-10"
          />
        </div>
      </div>
    </slot>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="sticky top-0 z-10 bg-gray-50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              class="px-5 py-3.5 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider"
            >
              <button
                v-if="column.sortable !== false"
                @click="toggleSort(column.key)"
                class="flex items-center gap-1.5 hover:text-gray-900 transition-colors duration-150"
              >
                {{ column.label }}
                <component :is="getSortIcon(column.key)" class="h-3.5 w-3.5 text-gray-300" />
              </button>
              <span v-else class="text-gray-500">{{ column.label }}</span>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-if="paginatedRows.length === 0">
            <td :colspan="columns.length">
              <div class="flex flex-col items-center justify-center py-16 px-6">
                <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mb-4">
                  <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                  </svg>
                </div>
                <p class="text-sm font-medium text-gray-900">{{ t('table.noData') }}</p>
                <p class="text-xs text-gray-500 mt-1">Try adjusting your search or filters.</p>
              </div>
            </td>
          </tr>
          <tr
            v-for="(row, index) in paginatedRows"
            :key="index"
            class="transition-all duration-150 hover:bg-gray-50"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-5 py-3.5"
            >
              <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                <span class="text-gray-700">{{ row[column.key] }}</span>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="totalPages > 1" class="px-5 py-3.5 border-t border-gray-100">
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
