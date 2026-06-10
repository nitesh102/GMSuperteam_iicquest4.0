<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DepartmentModal from '@/Pages/Department/DepartmentModal.vue';

const items = ref([...(usePage().props.departments ?? [])]);
watch(() => usePage().props.departments, (v) => {
    items.value = [...(v ?? [])];
});

const flashMessage = ref(null);
let flashTimer = null;
watch(() => usePage().props.flash?.success, (val) => {
    if (flashTimer) clearTimeout(flashTimer);
    flashMessage.value = val || null;
    if (flashMessage.value) {
        flashTimer = setTimeout(() => flashMessage.value = null, 4000);
    }
}, { immediate: true });

const showModal = ref(false);
const editingDepartment = ref(null);
const showDeleteConfirm = ref(false);
const deletingDepartment = ref(null);

const search = ref('');
const sortKey = ref('name');
const sortDir = ref('asc');
const currentPage = ref(1);
const pageSize = ref(10);

const totalDepartments = computed(() => items.value.length);

const filteredDepartments = computed(() => {
    if (!search.value) return items.value;
    const q = search.value.toLowerCase();
    return items.value.filter(d =>
        d.name.toLowerCase().includes(q) ||
        (d.description && d.description.toLowerCase().includes(q))
    );
});

const sortedDepartments = computed(() => {
    const sorted = [...filteredDepartments.value];
    sorted.sort((a, b) => {
        const aVal = a[sortKey.value] ?? '';
        const bVal = b[sortKey.value] ?? '';
        let cmp;
        if (sortKey.value === 'created_at') {
            cmp = new Date(aVal).getTime() - new Date(bVal).getTime();
        } else if (typeof aVal === 'number') {
            cmp = aVal - bVal;
        } else {
            cmp = String(aVal).toLowerCase().localeCompare(String(bVal).toLowerCase());
        }
        return sortDir.value === 'asc' ? cmp : -cmp;
    });
    return sorted;
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(sortedDepartments.value.length / pageSize.value))
);

const paginatedDepartments = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return sortedDepartments.value.slice(start, start + pageSize.value);
});

const startRecord = computed(() =>
    filteredDepartments.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1
);
const endRecord = computed(() =>
    Math.min(currentPage.value * pageSize.value, filteredDepartments.value.length)
);

const visiblePages = computed(() => {
    const total = totalPages.value;
    const cur = currentPage.value;
    const max = 5;
    let start = Math.max(1, cur - Math.floor(max / 2));
    let end = Math.min(total, start + max - 1);
    if (end - start + 1 < max) {
        start = Math.max(1, end - max + 1);
    }
    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const hasDepartments = computed(() => totalDepartments.value > 0);
const hasSearchResults = computed(() => paginatedDepartments.value.length > 0);

function toggleSort(key) {
    if (sortKey.value === key) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortDir.value = 'asc';
    }
}

function sortIcon(key) {
    if (sortKey.value !== key) return 'fas fa-sort text-gray-300';
    return sortDir.value === 'asc'
        ? 'fas fa-sort-up text-indigo-600'
        : 'fas fa-sort-down text-indigo-600';
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function openCreateModal() {
    editingDepartment.value = null;
    showModal.value = true;
}

function openEditModal(dept) {
    editingDepartment.value = dept;
    showModal.value = true;
}

function confirmDelete(dept) {
    deletingDepartment.value = dept;
    showDeleteConfirm.value = true;
}

function executeDelete() {
    if (!deletingDepartment.value) return;
    const id = deletingDepartment.value.id;
    showDeleteConfirm.value = false;
    deletingDepartment.value = null;
    items.value = items.value.filter(d => d.id !== id);
    router.delete(route('departments.destroy', id), {
        preserveScroll: true,
        onError: () => router.reload(),
    });
}

function closeModal() {
    showModal.value = false;
    editingDepartment.value = null;
}

function handleOptimisticUpdate(data) {
    if (data.isEdit) {
        const idx = items.value.findIndex(d => d.id === data.id);
        if (idx !== -1) {
            items.value[idx] = { ...items.value[idx], name: data.name, description: data.description };
        }
    } else {
        items.value.unshift({
            id: -Date.now(),
            name: data.name,
            description: data.description,
            created_at: new Date().toISOString(),
        });
    }
}

watch(search, () => {
    currentPage.value = 1;
});
</script>

<template>
    <Head title="Departments" />

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
            <!-- ══════════════════════════════════════
                 PAGE HEADER
            ══════════════════════════════════════ -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Departments</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage departments for complaint routing and organization.
                    </p>
                </div>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 hover:shadow-md transition-all duration-150 flex-shrink-0"
                >
                    <i class="fas fa-plus text-xs"></i>
                    New Department
                </button>
            </div>

            <!-- ══════════════════════════════════════
                 STATS CARDS
            ══════════════════════════════════════ -->
            <div v-if="hasDepartments" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-5 transition-all duration-200 hover:shadow-md hover:border-gray-300">
                    <div class="flex items-center gap-4">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-building text-indigo-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Departments</p>
                            <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ totalDepartments }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════
                 EMPTY STATE (no departments at all)
            ══════════════════════════════════════ -->
            <div v-if="!hasDepartments" class="text-center py-16">
                <div class="w-20 h-20 rounded-2xl bg-indigo-50 flex items-center justify-center mx-auto mb-5">
                    <i class="fas fa-building text-indigo-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">No departments yet</h3>
                <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6">
                    Create your first department to start organizing complaints and routing them to the right teams.
                </p>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 hover:shadow-md transition-all duration-150"
                >
                    <i class="fas fa-plus text-xs"></i>
                    Create Department
                </button>
            </div>

            <!-- ══════════════════════════════════════
                 TABLE CARD (has departments)
            ══════════════════════════════════════ -->
            <div v-if="hasDepartments" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <!-- Toolbar -->
                <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="relative flex-1 max-w-xs">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search departments..."
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

                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th
                                    @click="toggleSort('name')"
                                    class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700 transition-colors"
                                >
                                    <span class="inline-flex items-center gap-1.5">
                                        Department Name
                                        <i :class="sortIcon('name') + ' text-xs'"></i>
                                    </span>
                                </th>
                                <th
                                    @click="toggleSort('description')"
                                    class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700 transition-colors"
                                >
                                    <span class="inline-flex items-center gap-1.5">
                                        Description
                                        <i :class="sortIcon('description') + ' text-xs'"></i>
                                    </span>
                                </th>
                                <th
                                    @click="toggleSort('created_at')"
                                    class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700 transition-colors"
                                >
                                    <span class="inline-flex items-center gap-1.5">
                                        Created
                                        <i :class="sortIcon('created_at') + ' text-xs'"></i>
                                    </span>
                                </th>
                                <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="dept in paginatedDepartments"
                                :key="dept.id"
                                class="group transition-colors duration-150 hover:bg-indigo-50/40"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 group-hover:bg-indigo-100 flex items-center justify-center flex-shrink-0 transition-colors duration-150">
                                            <i class="fas fa-building text-indigo-500 text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ dept.name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm text-gray-500 max-w-md truncate" :title="dept.description">
                                        {{ dept.description || '—' }}
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="text-sm text-gray-500">{{ formatDate(dept.created_at) }}</span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button
                                            @click="openEditModal(dept)"
                                            class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-150"
                                            title="Edit department"
                                        >
                                            <i class="fas fa-edit text-sm"></i>
                                        </button>
                                        <button
                                            @click="confirmDelete(dept)"
                                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-150"
                                            title="Delete department"
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
                        v-for="dept in paginatedDepartments"
                        :key="dept.id"
                        class="p-4 hover:bg-gray-50 transition-colors duration-150"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-building text-indigo-500 text-xs"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ dept.name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ formatDate(dept.created_at) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button @click="openEditModal(dept)" class="p-2 text-gray-400 hover:text-indigo-600 rounded-lg transition-colors" title="Edit">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <button @click="confirmDelete(dept)" class="p-2 text-gray-400 hover:text-red-600 rounded-lg transition-colors" title="Delete">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2 ml-11 truncate">{{ dept.description || 'No description' }}</p>
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
                    <p class="text-sm text-gray-500 mt-1">No departments match "{{ search }}"</p>
                    <button @click="search = ''" class="mt-3 text-sm text-indigo-600 hover:text-indigo-700 font-medium transition-colors">
                        Clear search
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
                        of <span class="font-medium text-gray-700">{{ filteredDepartments.length }}</span>
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
                            <i class="fas fa-chevron-right text-xs"></i>
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

    <!-- ══════════════════════════════════════
         CREATE / EDIT MODAL
    ══════════════════════════════════════ -->
    <DepartmentModal
        :show="showModal"
        :department="editingDepartment"
        @close="closeModal"
        @success="closeModal"
        @submitting="handleOptimisticUpdate"
    />

    <!-- ══════════════════════════════════════
         DELETE CONFIRMATION MODAL
    ══════════════════════════════════════ -->
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
                <div
                    class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md"
                >
                    <div class="px-6 pt-6 pb-4">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-red-500 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Delete Department</h3>
                                <p class="mt-1.5 text-sm text-gray-500 leading-relaxed">
                                    Are you sure you want to delete
                                    <span class="font-medium text-gray-700">"{{ deletingDepartment?.name }}"</span>?
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
                            Delete Department
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
