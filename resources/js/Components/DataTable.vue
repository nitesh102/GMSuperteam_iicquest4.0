<template>
    <div class="card dt-card">
        <div class="card-body p-0">

            <!-- Toolbar -->
            <div class="dt-toolbar d-flex flex-wrap align-items-center justify-content-between">
                <div class="dt-search input-group">
                    <div class="input-group-prepend">
            <span class="input-group-text bg-white border-right-0">
              <i class="mdi mdi-magnify text-muted"></i>
            </span>
                    </div>
                    <input
                        v-model="localSearch"
                        type="search"
                        class="form-control border-left-0"
                        :placeholder="searchPlaceholder"
                    />
                </div>

                <div class="dt-controls d-flex align-items-center">
                    <label class="mb-0 mr-2 small text-muted">Rows</label>
                    <select v-model.number="pageSize" class="form-control dt-page-size">
                        <option v-for="n in pageSizeOptions" :key="n" :value="n">{{ n }}</option>
                    </select>
                </div>
            </div>

            <!-- Table wrapper -->
            <div class="table-responsive dt-table-wrap">
                <table class="table table-hover table-striped mb-0 dt-table">
                    <thead class="thead-light sticky-top bg-white">
                    <tr>
                        <th
                            v-for="(col, i) in computedColumns"
                            :key="col.key || i"
                            :style="{ width: col.width || 'auto', cursor: col.sortable ? 'pointer' : 'default' }"
                            @click="col.sortable ? sortBy(col) : null"
                        >
                            <div class="th-inner d-flex align-items-center">
                                <span class="mr-1">{{ col.label }}</span>
                                <span v-if="col.sortable" class="text-muted small">
                    <i
                        class="mdi sort-icon"
                        :class="{
                        'mdi-arrow-up': sort.key === col.key && sort.dir === 'asc',
                        'mdi-arrow-down': sort.key === col.key && sort.dir === 'desc',
                        'mdi-swap-vertical': sort.key !== col.key
                      }"
                    ></i>
                  </span>
                            </div>
                        </th>
                    </tr>
                    </thead>

                    <tbody>
                    <tr
                        v-for="(row, idx) in pagedRows"
                        :key="rowKey(row, idx)"
                        class="align-middle"
                    >
                        <td
                            v-for="(col, i) in computedColumns"
                            :key="col.key || i"
                            class="dt-cell"
                            :data-title="col.label"
                            :class="col.align ? `text-${col.align}` : ''"
                        >
                            <!-- Slot for custom cell -->
                            <slot
                                v-if="$slots[`cell:${col.key}`]"
                                :name="`cell:${col.key}`"
                                :row="row"
                                :index="(currentPage - 1) * pageSize + idx"
                            />
                            <template v-else>
                  <span class="cell-text" :title="stringify(resolveValue(row, col.key))">
                    {{ resolveValue(row, col.key) }}
                  </span>
                            </template>
                        </td>
                    </tr>

                    <!-- Empty state -->
                    <tr v-if="pagedRows.length === 0">
                        <td :colspan="computedColumns.length" class="text-center text-muted py-4">
                            {{ emptyText }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer: pagination -->
            <div
                class="dt-footer d-flex flex-wrap align-items-center justify-content-between"
                v-if="filteredRows.length > 0"
            >
                <div class="small text-muted">
                    Showing
                    <strong>{{ startRecord }}</strong>–<strong>{{ endRecord }}</strong>
                    of <strong>{{ filteredRows.length }}</strong> records
                </div>

                <nav aria-label="Table pagination">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item" :class="{ disabled: currentPage === 1 }">
                            <button class="page-link" @click="goTo(1)" aria-label="First">
                                <span aria-hidden="true">&laquo;</span>
                            </button>
                        </li>
                        <li class="page-item" :class="{ disabled: currentPage === 1 }">
                            <button class="page-link" @click="prev" aria-label="Previous">&lsaquo;</button>
                        </li>

                        <li
                            v-for="p in pagesToShow"
                            :key="p"
                            class="page-item"
                            :class="{ active: currentPage === p }"
                        >
                            <button class="page-link" @click="goTo(p)">{{ p }}</button>
                        </li>

                        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                            <button class="page-link" @click="next" aria-label="Next">&rsaquo;</button>
                        </li>
                        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                            <button class="page-link" @click="goTo(totalPages)" aria-label="Last">
                                <span aria-hidden="true">&raquo;</span>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>

        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    rows: { type: Array, default: () => [] },
    /** Columns: [{ key, label, sortable, width, align }] */
    columns: { type: Array, default: () => [] },
    searchableKeys: { type: Array, default: () => [] },
    defaultSort: { type: Object, default: () => ({ key: null, dir: 'asc' }) },
    pageSizeOptions: { type: Array, default: () => [10, 25, 50, 100] },
    searchPlaceholder: { type: String, default: 'Search…' },
    emptyText: { type: String, default: 'No records found.' },
    rowKeyField: { type: String, default: 'id' },
});

const emit = defineEmits(['update:search']);

const localSearch = ref('');
const pageSize = ref(props.pageSizeOptions?.[0] || 10);
const currentPage = ref(1);
const sort = ref({ ...props.defaultSort });

watch(localSearch, () => {
    currentPage.value = 1;
    emit('update:search', localSearch.value);
});

const computedColumns = computed(() => {
    return props.columns.map(col => ({
        sortable: false,
        width: null,
        align: null,
        ...col,
    }));
});

function resolveValue(obj, path) {
    if (!path) return '';
    return path.split('.').reduce((acc, key) => (acc ? acc[key] : undefined), obj);
}
function stringify(v) {
    if (v == null) return '';
    if (typeof v === 'object') return JSON.stringify(v);
    return String(v);
}
function rowKey(row, idx) {
    if (props.rowKeyField && row[props.rowKeyField] != null) return row[props.rowKeyField];
    return `${idx}-${JSON.stringify(row).length}`;
}

const filteredRows = computed(() => {
    const s = localSearch.value?.toString().toLowerCase().trim();
    if (!s) return props.rows;

    const keys = props.searchableKeys.length
        ? props.searchableKeys
        : props.columns.map(c => c.key).filter(Boolean);

    return props.rows.filter(r =>
        keys.some(k => {
            const v = resolveValue(r, k);
            return v != null && String(v).toLowerCase().includes(s);
        })
    );
});

const sortedRows = computed(() => {
    const { key, dir } = sort.value;
    if (!key) return filteredRows.value;

    const copy = [...filteredRows.value];
    copy.sort((a, b) => {
        const av = resolveValue(a, key);
        const bv = resolveValue(b, key);

        if (av == null && bv == null) return 0;
        if (av == null) return dir === 'asc' ? -1 : 1;
        if (bv == null) return dir === 'asc' ? 1 : -1;

        if (typeof av === 'number' && typeof bv === 'number') {
            return dir === 'asc' ? av - bv : bv - av;
        }
        return dir === 'asc'
            ? String(av).localeCompare(String(bv))
            : String(bv).localeCompare(String(av));
    });
    return copy;
});

const totalPages = computed(() => Math.max(1, Math.ceil(sortedRows.value.length / pageSize.value)));

const pagedRows = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return sortedRows.value.slice(start, start + pageSize.value);
});

function sortBy(col) {
    if (!col.key) return;
    if (sort.value.key === col.key) {
        sort.value.dir = sort.value.dir === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value.key = col.key;
        sort.value.dir = 'asc';
    }
}

function goTo(p) {
    if (p < 1 || p > totalPages.value) return;
    currentPage.value = p;
}
function prev() {
    if (currentPage.value > 1) currentPage.value -= 1;
}
function next() {
    if (currentPage.value < totalPages.value) currentPage.value += 1;
}

const startRecord = computed(() =>
    filteredRows.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1
);
const endRecord = computed(() =>
    Math.min(currentPage.value * pageSize.value, filteredRows.value.length)
);

const pagesToShow = computed(() => {
    const max = 5;
    const total = totalPages.value;
    const cur = currentPage.value;

    let start = Math.max(1, cur - Math.floor(max / 2));
    let end = Math.min(total, start + max - 1);
    if (end - start + 1 < max) start = Math.max(1, end - max + 1);

    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});
</script>

<style scoped>
/* ===== Layout polish ===== */
.dt-card {
    border: 1px solid #edf1f5;
    box-shadow: 0 1px 2px rgba(18, 38, 63, 0.05);
    border-radius: 8px;
}

.dt-toolbar {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f0f2f6;
    gap: .75rem;
    background: #fff;
}

.dt-controls .dt-page-size {
    max-width: 90px;
}

/* Input merge: no double borders */
.dt-search .form-control:focus {
    box-shadow: none;
}
.dt-search .form-control,
.dt-search .input-group-text {
    height: 38px;
}

/* ===== Table polish ===== */
.dt-table-wrap {
    max-height: 65vh;
    overflow: auto;
    background: #fff;
}

.dt-table thead th {
    font-weight: 600;
    color: #3c4858;
    font-size: 0.925rem;
    border-bottom: 1px solid #eef2f6 !important;
    padding: 0.75rem 0.85rem;
}

.dt-table tbody td {
    padding: 0.65rem 0.85rem;
    vertical-align: middle;
    border-top-color: #f6f8fb;
}

/* Keep header visible over content on scroll */
thead.sticky-top th {
    position: sticky;
    top: 0;
    z-index: 2;
    background-color: #fff;
}

/* Hover emphasis */
.dt-table tbody tr:hover {
    background-color: #f9fbff;
}

/* Cell text ellipsis */
.cell-text {
    display: inline-block;
    max-width: 360px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Sort icon subtle */
.sort-icon {
    opacity: 0.6;
}

/* Footer */
.dt-footer {
    padding: 0.5rem 1rem;
    border-top: 1px solid #f0f2f6;
    background: #fafbfc;
}

/* Pagination compact */
.pagination .page-link {
    border-radius: 6px !important;
}

/* Zebra already from .table-striped but add a slight tint */
.table-striped tbody tr:nth-of-type(odd) {
    background-color: #fcfdff;
}

/* ===== Mobile stacked layout ===== */
@media (max-width: 576px) {
    .dt-toolbar {
        padding: 0.65rem 0.75rem;
    }
    .dt-table thead {
        display: none;
    }
    .dt-table tbody tr {
        display: block;
        margin: 0.5rem;
        border: 1px solid #eef2f6;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(18, 38, 63, 0.04);
    }
    .dt-table tbody td.dt-cell {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.6rem 0.75rem;
        border-top: 1px dashed #f0f2f6 !important;
    }
    .dt-table tbody td.dt-cell:first-child {
        border-top: none !important;
    }
    .dt-table tbody td.dt-cell::before {
        content: attr(data-title);
        font-weight: 600;
        color: #6b7a90;
        min-width: 40%;
    }
    .cell-text {
        max-width: 55vw;
    }
}

/* Optional: light dark-mode tweak if parent sets .dark */
:global(.dark) .dt-card {
    background: #111827;
    border-color: #1f2937;
}
:global(.dark) .dt-table thead th {
    background: #0b1220;
    color: #d1d5db;
    border-bottom-color: #1f2937 !important;
}
:global(.dark) .dt-table tbody td {
    border-top-color: #1f2937 !important;
    color: #e5e7eb;
}
:global(.dark) .dt-table tbody tr:hover {
    background: #0b1220;
}
:global(.dark) .dt-footer {
    background: #0b1220;
    border-top-color: #1f2937;
}
</style>
