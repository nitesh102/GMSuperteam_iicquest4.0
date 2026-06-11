<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLogo from '@/Components/common/AppLogo.vue'

const { t } = useI18n()

const props = defineProps({
    collapsed: Boolean,
    isMobile: Boolean,
})

const emit = defineEmits(['toggle', 'close'])
const page  = usePage()

const currentPath = computed(() =>
    (page.url || (typeof window !== 'undefined' ? window.location.pathname : '/')).split('?')[0]
)

const isActive = (path) => currentPath.value === path || currentPath.value.startsWith(path + '/')

const onLink = () => { if (props.isMobile) emit('close') }

const linkClass = (path) => [
    'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150 no-underline group',
    isActive(path)
        ? 'bg-indigo-50 text-indigo-700 font-medium'
        : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900',
]
</script>

<template>
    <div
        class="flex min-h-0 flex-1 flex-col"
        :class="{
            'sidebar-collapsed': collapsed,
        }"
    >
        <div
            class="flex shrink-0 items-center border-b border-gray-100 pt-16 pb-3"
            :class="collapsed ? 'justify-center px-2' : 'justify-center px-4'"
        >
            <AppLogo />
        </div>

        <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3 px-2">
            <ul class="space-y-1">
                <li v-if="!collapsed" class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-widest text-gray-400">
                    {{ t('nav.main') }}
                </li>

                <li>
                    <a :href="route('dashboard')" :class="linkClass('/dashboard')" @click="onLink">
                        <i class="fas fa-home w-5 text-center" :class="isActive('/dashboard') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-500'"></i>
                        <span v-if="!collapsed">{{ t('nav.dashboard') }}</span>
                    </a>
                </li>

                <li v-if="!collapsed" class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-widest text-gray-400">
                    {{ t('nav.management') }}
                </li>
                <li v-else class="my-2 border-t border-gray-100 mx-2" />

                <li>
                    <a :href="route('departments.index')" :class="linkClass('/departments')" @click="onLink">
                        <i class="fas fa-building w-5 text-center" :class="isActive('/departments') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-500'"></i>
                        <span v-if="!collapsed">{{ t('nav.departments') }}</span>
                    </a>
                </li>

                <li>
                    <a :href="route('complaint-categories.index')" :class="linkClass('/complaint-categories')" @click="onLink">
                        <i class="fas fa-tags w-5 text-center" :class="isActive('/complaint-categories') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-500'"></i>
                        <span v-if="!collapsed">{{ t('nav.categories') }}</span>
                    </a>
                </li>

                <li>
                    <a :href="route('complaints.index')" :class="linkClass('/complaints')" @click="onLink">
                        <i class="fas fa-flag w-5 text-center" :class="isActive('/complaints') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-500'"></i>
                        <span v-if="!collapsed">{{ t('nav.complaints') }}</span>
                    </a>
                </li>

                <li v-if="!collapsed" class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-widest text-gray-400">
                    {{ t('nav.account') }}
                </li>
                <li v-else class="my-2 border-t border-gray-100 mx-2" />

                <li>
                    <a :href="route('profile.edit')" :class="linkClass('/profile')" @click="onLink">
                        <i class="fas fa-user w-5 text-center" :class="isActive('/profile') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-500'"></i>
                        <span v-if="!collapsed">{{ t('nav.profile') }}</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</template>

<style scoped>
.sidebar-collapsed :deep(.brand-text) {
    display: none;
}

.icon          { inline-size: 18px; font-size: 16px; text-align: center; flex-shrink: 0; }
.submenu-icon  { inline-size: 14px; font-size: 13px; text-align: center; flex-shrink: 0; }
.submenu       { margin-inline-start: 2rem; padding-inline-start: 0.75rem; border-inline-start: 2px solid #e5e7eb; }
</style>
