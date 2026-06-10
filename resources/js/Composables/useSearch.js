// resources/js/Composables/useSearch.js
import { ref, computed, onMounted, onUnmounted } from 'vue';

export function useSearch(items = [], searchFields = ['name']) {
    const searchQuery = ref('');
    const searchResults = ref([]);
    const isSearching = ref(false);

    // Global search emitter (communicates between components)
    const emitGlobalSearch = (query) => {
        const event = new CustomEvent('global-search', {
            detail: { query }
        });
        window.dispatchEvent(event);
    };

    // Listen for global search events
    const setupGlobalSearchListener = () => {
        const handler = (event) => {
            searchQuery.value = event.detail.query;
        };
        window.addEventListener('global-search', handler);
        return () => window.removeEventListener('global-search', handler);
    };

    // Filter items based on search query
    const filteredItems = computed(() => {
        if (!searchQuery.value || !items || !items.length) {
            return items;
        }

        const query = searchQuery.value.toLowerCase().trim();

        return items.filter(item => {
            return searchFields.some(field => {
                const value = getNestedValue(item, field);
                if (value === null || value === undefined) return false;

                if (typeof value === 'object') {
                    return JSON.stringify(value).toLowerCase().includes(query);
                }

                return String(value).toLowerCase().includes(query);
            });
        });
    });

    // Helper to get nested object values
    const getNestedValue = (obj, path) => {
        return path.split('.').reduce((current, key) => {
            return current ? current[key] : undefined;
        }, obj);
    };

    // Search in table columns
    const searchInTable = (tableData, columns = []) => {
        if (!searchQuery.value) return tableData;

        const query = searchQuery.value.toLowerCase().trim();
        return tableData.filter(row => {
            return columns.some(col => {
                const value = row[col];
                return value && String(value).toLowerCase().includes(query);
            });
        });
    };

    // Reset search
    const resetSearch = () => {
        searchQuery.value = '';
        emitGlobalSearch('');
    };

    // Debounced search
    const debouncedSearch = (callback, delay = 300) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => callback(...args), delay);
        };
    };

    return {
        searchQuery,
        searchResults,
        filteredItems,
        isSearching,
        emitGlobalSearch,
        setupGlobalSearchListener,
        searchInTable,
        resetSearch,
        debouncedSearch
    };
}

// Sync a local search ref with the navbar/global search event and optionally trigger a callback.
export function useGlobalSearchListener(searchRef, onSearch, delay = 250) {
    let timeout;

    const handler = (event) => {
        const query = event?.detail?.query ?? '';
        searchRef.value = query;

        if (typeof onSearch === 'function') {
            clearTimeout(timeout);
            timeout = setTimeout(() => onSearch(query), delay);
        }
    };

    onMounted(() => window.addEventListener('global-search', handler));
    onUnmounted(() => window.removeEventListener('global-search', handler));
}

// Create a global search instance
let globalSearchInstance = null;

export function useGlobalSearch() {
    if (!globalSearchInstance) {
        globalSearchInstance = ref({
            query: '',
            source: '', // 'navbar', 'sidebar', 'office'
            results: []
        });
    }

    const updateGlobalSearch = (query, source = 'navbar') => {
        if (globalSearchInstance) {
            globalSearchInstance.value.query = query;
            globalSearchInstance.value.source = source;

            // Emit event for other components
            window.dispatchEvent(new CustomEvent('global-search-updated', {
                detail: { query, source }
            }));
        }
    };

    const getGlobalSearchQuery = () => {
        return globalSearchInstance ? globalSearchInstance.value.query : '';
    };

    return {
        globalSearch: globalSearchInstance,
        updateGlobalSearch,
        getGlobalSearchQuery
    };
}
