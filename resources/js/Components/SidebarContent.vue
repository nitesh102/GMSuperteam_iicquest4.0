<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    collapsed: Boolean,
    isMobile: Boolean,
})

const emit = defineEmits(['toggle', 'close'])
const page  = usePage()

/* ── Active path helper ── */
const currentPath = computed(() =>
    (page.url || (typeof window !== 'undefined' ? window.location.pathname : '/')).split('?')[0]
)

const isActive    = (path) => currentPath.value === path || currentPath.value.startsWith(path + '/')

/* ── Close mobile drawer on link click ── */
const onLink = () => { if (props.isMobile) emit('close') }

/* ── Nav item classes ── */
const linkClass = (path) => [
    'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150 no-underline',
    isActive(path)
        ? 'bg-indigo-50 text-indigo-700 font-medium'
        : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900',
]
</script>

<template>
    <!-- Mobile: close button -->
    <div v-if="isMobile" class="flex items-center justify-between px-4 pt-3 pb-1 lg:hidden">
        <span class="text-xs font-semibold uppercase tracking-widest text-gray-400">{{ t('nav.menu') }}</span>
        <button
            @click="emit('close')"
            class="flex h-7 w-7 items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200"
        >
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-2 px-3">

        <!-- ── Static links ── -->
        <ul class="space-y-0.5">
            <!-- Section label -->
            <li v-if="!collapsed" class="px-3 pt-2 pb-1 text-[10px] font-semibold uppercase tracking-widest text-gray-400">
                {{ t('nav.main') }}
            </li>

            <!-- Dashboard -->
            <li>
                <a :href="route('dashboard')" :class="linkClass('/dashboard')" @click="onLink">
                    <i class="fas fa-home icon" :class="isActive('/dashboard') ? 'text-indigo-600' : 'text-gray-400'"></i>
                    <span v-if="!collapsed">{{ t('nav.dashboard') }}</span>
                </a>
            </li>

            <!-- Management -->
            <li v-if="!collapsed" class="px-3 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-widest text-gray-400">
                {{ t('nav.management') }}
            </li>
            <li v-else class="my-2 border-t border-gray-100 mx-1" />

            <!-- Departments -->
            <li>
                <a :href="route('departments.index')" :class="linkClass('/departments')" @click="onLink">
                    <i class="fas fa-building icon" :class="isActive('/departments') ? 'text-indigo-600' : 'text-gray-400'"></i>
                    <span v-if="!collapsed">{{ t('nav.departments') }}</span>
                </a>
            </li>

            <!-- Complaint Categories -->
            <li>
                <a :href="route('complaint-categories.index')" :class="linkClass('/complaint-categories')" @click="onLink">
                    <i class="fas fa-tags icon" :class="isActive('/complaint-categories') ? 'text-indigo-600' : 'text-gray-400'"></i>
                    <span v-if="!collapsed">{{ t('nav.categories') }}</span>
                </a>
            </li>

            <!-- Complaints -->
            <li>
                <a :href="route('complaints.index')" :class="linkClass('/complaints')" @click="onLink">
                    <i class="fas fa-flag icon" :class="isActive('/complaints') ? 'text-indigo-600' : 'text-gray-400'"></i>
                    <span v-if="!collapsed">{{ t('nav.complaints') }}</span>
                </a>
            </li>

            <!-- Profile -->
            <li v-if="!collapsed" class="px-3 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-widest text-gray-400">
                {{ t('nav.account') }}
            </li>
            <li v-else class="my-2 border-t border-gray-100 mx-1" />

            <li>
                <a :href="route('profile.edit')" :class="linkClass('/profile')" @click="onLink">
                    <i class="fas fa-user icon" :class="isActive('/profile') ? 'text-indigo-600' : 'text-gray-400'"></i>
                    <span v-if="!collapsed">{{ t('nav.profile') }}</span>
                </a>
            </li>
        </ul>
    </nav>
</template>

<style scoped>
.icon          { inline-size: 18px; font-size: 16px; text-align: center; flex-shrink: 0; }
.submenu-icon  { inline-size: 14px; font-size: 13px; text-align: center; flex-shrink: 0; }
.submenu       { margin-inline-start: 2rem; padding-inline-start: 0.75rem; border-inline-start: 2px solid #e5e7eb; }

.slide-enter-active,
.slide-leave-active { transition: all 0.2s ease; overflow: hidden; }
.slide-enter-from,
.slide-leave-to     { opacity: 0; max-block-size: 0; }
.slide-enter-to,
.slide-leave-from   { opacity: 1; max-block-size: 300px; }
</style>
