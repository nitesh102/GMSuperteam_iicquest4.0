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
  <div class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
    <!-- Search -->
    <slot name="header">
      <div class="p-6 border-b border-gray-200">
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="t('table.search')"
          class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 px-4 text-sm placeholder-gray-400 focus:border-red-500 focus:bg-white focus:outline-none"
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
              class="px-6 py-4 text-left font-semibold text-gray-900"
            >
              <button
                v-if="column.sortable !== false"
                @click="toggleSort(column.key)"
                class="flex items-center gap-2 hover:text-gray-600 transition"
              >
                {{ column.label }}
                <component :is="getSortIcon(column.key)" class="h-4 w-4" />
              </button>
              <span v-else>{{ column.label }}</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="paginatedRows.length === 0">
            <td :colspan="columns.length" class="px-6 py-10 text-center text-sm text-gray-500">
              {{ t('table.noData') }}
            </td>
          </tr>
          <tr
            v-for="(row, index) in paginatedRows"
            :key="index"
            class="border-b border-gray-200 hover:bg-gray-50 transition"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4"
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
