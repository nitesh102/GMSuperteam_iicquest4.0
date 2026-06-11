<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ComplaintModal from '@/Pages/Complaint/ComplaintModal.vue'
import { useVoicePageHandlers } from '@/Composables/useVoiceContext'

const { t } = useI18n();
const { complaints, departments, categories, users } = usePage().props

const items = ref([...(complaints ?? [])])
const loading = ref(true)

watch(() => usePage().props.complaints, (v) => {
  items.value = [...(v ?? [])]
})

const flashMessage = ref(null)
let flashTimer = null
watch(() => usePage().props.flash?.success, (val) => {
  if (flashTimer) clearTimeout(flashTimer)
  flashMessage.value = val || null
  if (flashMessage.value) {
    flashTimer = setTimeout(() => flashMessage.value = null, 4000)
  }
}, { immediate: true })

const showModal = ref(false)
const editingComplaint = ref(null)
const showDeleteConfirm = ref(false)
const deletingComplaint = ref(null)

const search = ref('')
const filterDepartment = ref('')
const filterCategory = ref('')
const filterStatus = ref('')
const filterPriority = ref('')
const sortKey = ref('created_at')
const sortDir = ref('desc')
const currentPage = ref(1)
const pageSize = ref(10)

onMounted(() => {
  setTimeout(() => loading.value = false, 400)
})

const stats = computed(() => {
  const all = items.value
  return {
    total: all.length,
    pending: all.filter(c => c.current_status === 'submitted' || c.current_status === 'under_review').length,
    inProgress: all.filter(c => c.current_status === 'in_progress' || c.current_status === 'assigned').length,
    resolved: all.filter(c => c.current_status === 'resolved').length,
    spam: all.filter(c => c.is_spam).length,
  }
})

const filteredItems = computed(() => {
  let result = items.value
  const q = search.value.toLowerCase()
  if (q) {
    result = result.filter(c =>
      c.title.toLowerCase().includes(q) ||
      c.complaint_no.toLowerCase().includes(q) ||
      (c.citizen?.name && c.citizen.name.toLowerCase().includes(q)) ||
      (c.description && c.description.toLowerCase().includes(q))
    )
  }
  if (filterDepartment.value) {
    result = result.filter(c => c.category?.department_id === Number(filterDepartment.value))
  }
  if (filterCategory.value) {
    result = result.filter(c => c.category_id === Number(filterCategory.value))
  }
  if (filterStatus.value) {
    result = result.filter(c => c.current_status === filterStatus.value)
  }
  if (filterPriority.value) {
    result = result.filter(c => c.priority === filterPriority.value)
  }
  return result
})

const sortedItems = computed(() => {
  const sorted = [...filteredItems.value]
  sorted.sort((a, b) => {
    let aVal, bVal
    if (sortKey.value === 'priority') {
      const order = { emergency: 4, high: 3, medium: 2, low: 1 }
      aVal = order[a.priority] || 0
      bVal = order[b.priority] || 0
    } else if (sortKey.value === 'status') {
      aVal = a.current_status || ''
      bVal = b.current_status || ''
    } else {
      aVal = a[sortKey.value] ?? ''
      bVal = b[sortKey.value] ?? ''
    }
    if (sortKey.value === 'created_at') {
      const cmp = new Date(aVal).getTime() - new Date(bVal).getTime()
      return sortDir.value === 'asc' ? cmp : -cmp
    }
    const cmp = String(aVal).toLowerCase().localeCompare(String(bVal).toLowerCase())
    return sortDir.value === 'asc' ? cmp : -cmp
  })
  return sorted
})

const totalPages = computed(() =>
  Math.max(1, Math.ceil(sortedItems.value.length / pageSize.value))
)

const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return sortedItems.value.slice(start, start + pageSize.value)
})

const filteredCategories = computed(() => {
  if (!filterDepartment.value) return categories
  return categories.filter(c => c.department_id === Number(filterDepartment.value))
})

const startRecord = computed(() =>
  filteredItems.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1
)
const endRecord = computed(() =>
  Math.min(currentPage.value * pageSize.value, filteredItems.value.length)
)

const hasItems = computed(() => items.value.length > 0)
const hasSearchResults = computed(() => paginatedItems.value.length > 0)

function toggleSort(key) {
  if (sortKey.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortDir.value = 'asc'
  }
}

function sortIcon(key) {
  if (sortKey.value !== key) return 'fas fa-sort text-gray-300'
  return sortDir.value === 'asc'
    ? 'fas fa-sort-up text-indigo-600'
    : 'fas fa-sort-down text-indigo-600'
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric', month: 'short', day: 'numeric',
  })
}

function statusBadge(status) {
  const map = {
    submitted: 'bg-blue-50 text-blue-700 ring-blue-600/20',
    under_review: 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
    assigned: 'bg-purple-50 text-purple-700 ring-purple-600/20',
    in_progress: 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
    resolved: 'bg-green-50 text-green-700 ring-green-600/20',
    rejected: 'bg-red-50 text-red-700 ring-red-600/20',
    closed: 'bg-gray-50 text-gray-700 ring-gray-600/20',
  }
  return map[status] || 'bg-gray-50 text-gray-700 ring-gray-600/20'
}

function statusLabel(status) {
  const map = {
    submitted: t('complaint.statusSubmitted'),
    under_review: t('complaint.statusUnderReview'),
    assigned: t('complaint.statusAssigned'),
    in_progress: t('complaint.statusInProgress'),
    resolved: t('complaint.statusResolved'),
    rejected: t('complaint.statusRejected'),
    closed: t('complaint.statusClosed'),
  }
  return map[status] || status
}

function priorityBadge(priority) {
  const map = {
    low: 'bg-gray-50 text-gray-600 ring-gray-500/20',
    medium: 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
    high: 'bg-orange-50 text-orange-700 ring-orange-600/20',
    emergency: 'bg-red-50 text-red-700 ring-red-600/20',
  }
  return map[priority] || 'bg-gray-50 text-gray-600 ring-gray-500/20'
}

function priorityLabel(priority) {
  const map = {
    low: t('complaint.priorityLow'),
    medium: t('complaint.priorityMedium'),
    high: t('complaint.priorityHigh'),
    emergency: t('complaint.priorityEmergency'),
  }
  return map[priority] || priority
}

function openEditModal(item) {
  editingComplaint.value = item
  showModal.value = true
}

function closeEditModal() {
  showModal.value = false
  editingComplaint.value = null
}

function confirmDelete(item) {
  deletingComplaint.value = item
  showDeleteConfirm.value = true
}

function executeDelete() {
  if (!deletingComplaint.value) return
  const id = deletingComplaint.value.id
  showDeleteConfirm.value = false
  deletingComplaint.value = null
  items.value = items.value.filter(c => c.id !== id)
  router.delete(route('complaints.destroy', id), {
    preserveScroll: true,
    onError: () => router.reload(),
  })
}

function handleOptimisticUpdate(data) {
  if (data.isEdit) {
    const idx = items.value.findIndex(c => c.id === data.id)
    if (idx !== -1) {
      items.value[idx] = {
        ...items.value[idx],
        title:            data.title,
        description:      data.description,
        category_id:      data.category_id,
        category:         categories.find(c => c.id === data.category_id) || items.value[idx].category,
        current_status:   data.current_status,
        priority:         data.priority,
        location:         data.location,
        latitude:         data.latitude,
        longitude:        data.longitude,
        assigned_to:      data.assigned_to ?? null,
        assignee:         data.assigned_to ? (users ?? []).find(u => u.id === data.assigned_to) ?? null : null,
        resolution_notes: data.resolution_notes ?? null,
      }
    }
  } else {
    items.value.unshift({
      id: -Date.now(),
      complaint_no: 'CMP-PENDING',
      title: data.title,
      description: data.description,
      category_id: data.category_id,
      category: categories.find(c => c.id === data.category_id) || null,
      citizen: usePage().props.auth.user,
      current_status: 'submitted',
      priority: 'medium',
      is_spam: false,
      location: data.location || null,
      latitude: data.latitude || null,
      longitude: data.longitude || null,
      ai_summary: null,
      created_at: new Date().toISOString(),
    })
  }
}

function clearFilters() {
  search.value = ''
  filterDepartment.value = ''
  filterCategory.value = ''
  filterStatus.value = ''
  filterPriority.value = ''
  currentPage.value = 1
}

const visiblePages = computed(() => {
  const total = totalPages.value
  const current = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  if (current <= 4) return [1, 2, 3, 4, 5, '...', total]
  if (current >= total - 3) return [1, '...', total - 4, total - 3, total - 2, total - 1, total]
  return [1, '...', current - 1, current, current + 1, '...', total]
})

watch(search, () => { currentPage.value = 1 })
watch(filterDepartment, () => { filterCategory.value = ''; currentPage.value = 1 })
watch([filterCategory, filterStatus, filterPriority], () => { currentPage.value = 1 })

useVoicePageHandlers({
    onSearch: (query) => {
        search.value = query
        currentPage.value = 1
    },
    onFilter: (key, value) => {
        if (key === 'status') {
            filterStatus.value = value === 'pending' ? 'submitted' : value
            currentPage.value = 1
        }
    },
    onClearFilters: clearFilters,
    onOpenCreate: (target) => {
        if (!target || target === 'complaint' || target === 'auto') {
            router.visit(route('complaints.create'))
        }
    },
}, 'complaint')
</script>

<template>
  <Head :title="t('complaint.listTitle')" />

  <teleport to="body">
    <div
      v-if="flashMessage"
      class="fixed top-5 right-5 z-[100] animate-slide-in"
      @click="flashMessage = null"
    >
      <div class="bg-white border border-green-200 rounded-xl shadow-lg px-5 py-3.5 flex items-center gap-3 cursor-pointer hover:shadow-xl transition-shadow">
        <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
          <i class="fas fa-check-circle text-green-500 text-sm"></i>
        </div>
        <p class="text-sm font-medium text-gray-800">{{ flashMessage }}</p>
        <i class="fas fa-times text-gray-300 hover:text-gray-500 text-xs ml-2 transition-colors"></i>
      </div>
    </div>
  </teleport>

  <AuthenticatedLayout>
    <div class="py-6 space-y-6">
      <!-- HEADER -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ t('complaint.listTitle') }}</h1>
          <p class="mt-1 text-sm text-gray-500">
            {{ t('complaint.listSubtitle') }}
          </p>
        </div>
        <Link
          :href="route('complaints.create')"
          class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 hover:shadow-md transition-all duration-150 flex-shrink-0"
        >
          <i class="fas fa-plus text-xs"></i>
          {{ t('complaint.newComplaint') }}
        </Link>
      </div>

      <!-- SKELETON LOADING -->
      <div v-if="loading" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
          <div v-for="n in 5" :key="n" class="bg-white rounded-xl border border-gray-200 p-5 animate-pulse">
            <div class="h-3 w-20 bg-gray-200 rounded mb-3"></div>
            <div class="h-6 w-12 bg-gray-200 rounded"></div>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-6 animate-pulse space-y-4">
          <div class="h-4 w-48 bg-gray-200 rounded"></div>
          <div class="h-10 bg-gray-200 rounded-lg"></div>
          <div class="space-y-3">
            <div v-for="n in 5" :key="'r'+n" class="h-12 bg-gray-100 rounded-lg"></div>
          </div>
        </div>
      </div>

      <!-- STATS CARDS -->
      <div v-if="!loading && hasItems" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 transition-all duration-200 hover:shadow-md hover:border-gray-300">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
              <i class="fas fa-flag text-indigo-600 text-sm"></i>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-500">Total</p>
              <p class="text-xl font-bold text-gray-900 mt-0.5">{{ stats.total }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 transition-all duration-200 hover:shadow-md hover:border-gray-300">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-yellow-50 flex items-center justify-center flex-shrink-0">
              <i class="fas fa-clock text-yellow-600 text-sm"></i>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-500">{{ t('dashboard.openPending') }}</p>
              <p class="text-xl font-bold text-gray-900 mt-0.5">{{ stats.pending }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 transition-all duration-200 hover:shadow-md hover:border-gray-300">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
              <i class="fas fa-spinner text-blue-600 text-sm"></i>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-500">In Progress</p>
              <p class="text-xl font-bold text-gray-900 mt-0.5">{{ stats.inProgress }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 transition-all duration-200 hover:shadow-md hover:border-gray-300">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
              <i class="fas fa-check-circle text-green-600 text-sm"></i>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-500">{{ t('dashboard.resolved') }}</p>
              <p class="text-xl font-bold text-gray-900 mt-0.5">{{ stats.resolved }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 transition-all duration-200 hover:shadow-md hover:border-gray-300">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
              <i class="fas fa-shield-alt text-red-600 text-sm"></i>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-500">Spam</p>
              <p class="text-xl font-bold text-gray-900 mt-0.5">{{ stats.spam }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- EMPTY STATE -->
      <div v-if="!loading && !hasItems" class="text-center py-16">
        <div class="w-20 h-20 rounded-2xl bg-indigo-50 flex items-center justify-center mx-auto mb-5">
          <i class="fas fa-flag text-indigo-400 text-3xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ t('complaint.noComplaints') }}</h3>
        <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6">
          {{ t('complaint.noComplaintsDesc') }}
        </p>
        <Link
          :href="route('complaints.create')"
          class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 hover:shadow-md transition-all duration-150"
        >
          <i class="fas fa-plus text-xs"></i>
          {{ t('complaint.submitComplaint') }}
        </Link>
      </div>

      <!-- TABLE CARD -->
      <div v-if="!loading && hasItems" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <!-- Toolbar + Filters -->
        <div class="p-4 border-b border-gray-100 space-y-3">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="relative flex-1 max-w-xs">
              <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
              <input
                v-model="search"
                type="text"
                placeholder="{{ t('complaint.searchPlaceholder') }}"
                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-150 placeholder:text-gray-400"
              />
              <button
                v-if="search"
                @click="search = ''"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
              >
                <i class="fas fa-times text-xs"></i>
              </button>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-500">
              <span class="hidden sm:inline">Rows</span>
              <select
                v-model.number="pageSize"
                class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm bg-gray-50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all min-w-[5rem]"
              >
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
              </select>
            </div>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <select
              v-model="filterDepartment"
              class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm bg-gray-50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
            >
              <option value="">{{ t('complaint.allDepartments') }}</option>
              <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
            <select
              v-model="filterCategory"
              class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm bg-gray-50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
            >
              <option value="">{{ t('complaint.allCategories') }}</option>
              <option v-for="c in filteredCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <select
              v-model="filterStatus"
              class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm bg-gray-50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
            >
              <option value="">{{ t('complaint.filterAll') }}</option>
              <option value="submitted">{{ t('complaint.statusSubmitted') }}</option>
              <option value="under_review">{{ t('complaint.statusUnderReview') }}</option>
              <option value="assigned">{{ t('complaint.statusAssigned') }}</option>
              <option value="in_progress">{{ t('complaint.statusInProgress') }}</option>
              <option value="resolved">{{ t('complaint.statusResolved') }}</option>
              <option value="rejected">{{ t('complaint.statusRejected') }}</option>
              <option value="closed">{{ t('complaint.statusClosed') }}</option>
            </select>
            <select
              v-model="filterPriority"
              class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm bg-gray-50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
            >
              <option value="">{{ t('dashboard.allTime') }}</option>
              <option value="low">{{ t('complaint.priorityLow') }}</option>
              <option value="medium">{{ t('complaint.priorityMedium') }}</option>
              <option value="high">{{ t('complaint.priorityHigh') }}</option>
              <option value="emergency">{{ t('complaint.priorityEmergency') }}</option>
            </select>
            <button
              v-if="filterDepartment || filterCategory || filterStatus || filterPriority || search"
              @click="clearFilters"
              class="text-xs text-indigo-600 hover:text-indigo-700 font-medium px-2 py-1.5 transition-colors"
            >
              {{ t('complaint.clearAll') }}
            </button>
          </div>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-100 bg-gray-50/50">
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                <th
                  @click="toggleSort('title')"
                  class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700 transition-colors"
                >
                  <span class="inline-flex items-center gap-1.5">
                    {{ t('complaint.columnTitle') }}
                    <i :class="sortIcon('title') + ' text-xs'"></i>
                  </span>
                </th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Citizen</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Category</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Dept</th>
                <th
                  @click="toggleSort('priority')"
                  class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700 transition-colors"
                >
                  <span class="inline-flex items-center gap-1.5">
                    {{ t('complaint.columnPriority') }}
                    <i :class="sortIcon('priority') + ' text-xs'"></i>
                  </span>
                </th>
                <th
                  @click="toggleSort('status')"
                  class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700 transition-colors"
                >
                  <span class="inline-flex items-center gap-1.5">
                    {{ t('complaint.columnStatus') }}
                    <i :class="sortIcon('status') + ' text-xs'"></i>
                  </span>
                </th>
                <th
                  @click="toggleSort('created_at')"
                  class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700 transition-colors"
                >
                  <span class="inline-flex items-center gap-1.5">
                    {{ t('complaint.columnCreated') }}
                    <i :class="sortIcon('created_at') + ' text-xs'"></i>
                  </span>
                </th>
                <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">{{ t('complaint.columnActions') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr
                v-for="item in paginatedItems"
                :key="item.id"
                class="group transition-colors duration-150 hover:bg-indigo-50/40"
              >
                <td class="px-5 py-4">
                  <span class="text-xs font-mono text-gray-400">{{ item.complaint_no }}</span>
                </td>
                <td class="px-5 py-4 max-w-xs">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 group-hover:bg-indigo-100 flex items-center justify-center flex-shrink-0 transition-colors duration-150">
                      <i class="fas fa-flag text-indigo-500 text-xs"></i>
                    </div>
                    <div class="min-w-0">
                      <p class="text-sm font-medium text-gray-900 truncate">{{ item.title }}</p>
                      <p v-if="item.is_spam" class="text-xs text-red-500 mt-0.5 flex items-center gap-1">
                        <i class="fas fa-shield-alt"></i> Spam detected
                      </p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-4">
                  <span class="text-sm text-gray-600">{{ item.citizen?.name || '—' }}</span>
                </td>
                <td class="px-5 py-4 hidden lg:table-cell">
                  <span class="text-sm text-gray-500">{{ item.category?.name || '—' }}</span>
                </td>
                <td class="px-5 py-4 hidden lg:table-cell">
                  <span class="text-sm text-gray-500">{{ item.category?.department?.name || '—' }}</span>
                </td>
                <td class="px-5 py-4">
                  <span
                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                    :class="priorityBadge(item.priority)"
                  >
                    <i v-if="item.priority === 'emergency'" class="fas fa-exclamation-circle"></i>
                    <i v-else-if="item.priority === 'high'" class="fas fa-arrow-up"></i>
                    <i v-else-if="item.priority === 'medium'" class="fas fa-minus"></i>
                    <i v-else class="fas fa-arrow-down"></i>
                    {{ priorityLabel(item.priority) }}
                  </span>
                </td>
                <td class="px-5 py-4">
                  <span
                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                    :class="statusBadge(item.current_status)"
                  >
                    {{ statusLabel(item.current_status) }}
                  </span>
                </td>
                <td class="px-5 py-4">
                  <span class="text-sm text-gray-500">{{ formatDate(item.created_at) }}</span>
                </td>
                <td class="px-5 py-4 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <Link
                      :href="route('complaints.show', item.id)"
                      class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-150"
                      title="View complaint"
                    >
                      <i class="fas fa-eye text-sm"></i>
                    </Link>
                    <button
                      @click="openEditModal(item)"
                      class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-150"
                      title="Edit complaint"
                    >
                      <i class="fas fa-edit text-sm"></i>
                    </button>
                    <button
                      @click="confirmDelete(item)"
                      class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-150"
                      title="Delete complaint"
                    >
                      <i class="fas fa-trash text-sm"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-gray-50">
          <div
            v-for="item in paginatedItems"
            :key="item.id"
            class="p-4 hover:bg-gray-50 transition-colors duration-150"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                  <i class="fas fa-flag text-indigo-500 text-xs"></i>
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ item.title }}</p>
                  <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ item.complaint_no }}</p>
                </div>
              </div>
              <div class="flex items-center gap-1 flex-shrink-0">
                <Link :href="route('complaints.show', item.id)" class="p-2 text-gray-400 hover:text-indigo-600 rounded-lg transition-colors" title="View">
                  <i class="fas fa-eye text-sm"></i>
                </Link>
                <button @click="openEditModal(item)" class="p-2 text-gray-400 hover:text-indigo-600 rounded-lg transition-colors" title="Edit">
                  <i class="fas fa-edit text-sm"></i>
                </button>
                <button @click="confirmDelete(item)" class="p-2 text-gray-400 hover:text-red-600 rounded-lg transition-colors" title="Delete">
                  <i class="fas fa-trash text-sm"></i>
                </button>
              </div>
            </div>
            <div class="mt-2 ml-11 flex flex-wrap items-center gap-2">
              <span
                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                :class="statusBadge(item.current_status)"
              >
                {{ statusLabel(item.current_status) }}
              </span>
              <span
                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                :class="priorityBadge(item.priority)"
              >
                {{ priorityLabel(item.priority) }}
              </span>
              <span class="text-xs text-gray-400">{{ item.citizen?.name || '—' }}</span>
            </div>
            <p class="text-xs text-gray-400 mt-1.5 ml-11 truncate">{{ item.category?.name || 'No category' }}</p>
          </div>
        </div>

        <!-- No search results -->
        <div
          v-if="!hasSearchResults && search"
          class="px-5 py-12 text-center"
        >
          <div class="w-14 h-14 rounded-xl bg-gray-50 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-search text-gray-300 text-xl"></i>
          </div>
          <p class="text-sm font-medium text-gray-900">No results found</p>
          <p class="text-sm text-gray-500 mt-1">No complaints match your search or filters.</p>
          <button @click="clearFilters" class="mt-3 text-sm text-indigo-600 hover:text-indigo-700 font-medium transition-colors">
            Clear all filters
          </button>
        </div>

        <!-- Pagination -->
        <div
          v-if="hasSearchResults"
          class="px-5 py-3.5 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
        >
          <p class="text-sm text-gray-500">
            Showing <span class="font-medium text-gray-700">{{ startRecord }}</span>
            – <span class="font-medium text-gray-700">{{ endRecord }}</span>
            of <span class="font-medium text-gray-700">{{ filteredItems.length }}</span>
          </p>
          <nav class="flex items-center gap-1">
            <button
              :disabled="currentPage === 1"
              @click="currentPage = 1"
              class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
              title="First"
            >
              <i class="fas fa-angle-double-left text-xs"></i>
            </button>
            <button
              :disabled="currentPage === 1"
              @click="currentPage--"
              class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
              title="Previous"
            >
              <i class="fas fa-chevron-left text-xs"></i>
            </button>
            <button
              v-for="p in visiblePages"
              :key="p"
              @click="currentPage = p"
              :class="[
                'min-w-[32px] h-8 text-sm font-medium rounded-lg transition-all duration-150',
                currentPage === p
                  ? 'bg-indigo-600 text-white shadow-sm'
                  : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              {{ p }}
            </button>
            <button
              :disabled="currentPage === totalPages"
              @click="currentPage++"
              class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
              title="Next"
            >
              <i class="fas fa-chevron-left text-xs"></i>
            </button>
            <button
              :disabled="currentPage === totalPages"
              @click="currentPage = totalPages"
              class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
              title="Last"
            >
              <i class="fas fa-angle-double-right text-xs"></i>
            </button>
          </nav>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>

  <!-- EDIT MODAL -->
  <ComplaintModal
    :show="showModal"
    :complaint="editingComplaint"
    :departments="departments"
    :categories="categories"
    :users="users"
    @close="closeEditModal"
    @success="closeEditModal"
    @submitting="handleOptimisticUpdate"
  />

  <!-- DELETE CONFIRMATION -->
  <teleport to="body">
    <div
      v-if="showDeleteConfirm"
      class="fixed inset-0 z-50 overflow-y-auto"
    >
      <div
        class="fixed inset-0 bg-gray-500/60 backdrop-blur-sm transition-opacity"
        @click="showDeleteConfirm = false"
      ></div>
      <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
          <div class="px-6 pt-6 pb-4">
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-500 text-sm"></i>
              </div>
              <div>
                <h3 class="text-base font-semibold text-gray-900">Delete Complaint</h3>
                <p class="mt-1.5 text-sm text-gray-500 leading-relaxed">
                  Are you sure you want to delete
                  <span class="font-medium text-gray-700">"{{ deletingComplaint?.title }}"</span>?
                  This action cannot be undone.
                </p>
              </div>
            </div>
          </div>
          <div class="px-6 py-4 bg-gray-50 flex items-center justify-end gap-3 rounded-b-xl">
            <button
              @click="showDeleteConfirm = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-150"
            >
              Cancel
            </button>
            <button
              @click="executeDelete"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 hover:shadow-sm transition-all duration-150"
            >
              Delete Complaint
            </button>
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>

<style scoped>
@keyframes slide-in {
  from { transform: translateX(100%); opacity: 0; }
  to   { transform: translateX(0);    opacity: 1; }
}
.animate-slide-in { animation: slide-in 0.3s ease-out; }
</style>