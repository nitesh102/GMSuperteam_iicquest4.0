<template>
    <nav class="flex h-20 shrink-0 items-center justify-between border-b border-gray-200 bg-white text-gray-900 px-8 fixed top-0 right-0 z-50 transition-all duration-300 ease-in-out"
        :style="{ left: sidebarWidth + 'px' }">
        <!-- Left side -->
        <div class="flex items-center gap-4">
            <!-- Mobile Menu Toggle -->
            <button
                @click="toggleMobileSidebar"
                class="lg:hidden flex size-10 items-center justify-center rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors"
            >
                <i :class="mobileSidebarOpen ? 'fas fa-times' : 'fas fa-bars'"></i>
            </button>

        </div>

        <!-- Search (Desktop only) -->
        <div class="hidden lg:flex flex-1 max-w-xl mx-6">
            <div class="relative w-full group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 group-focus-within:text-primary"></i>
                </div>

                <input
                    v-model="searchQuery"
                    @input="handleSearch"
                    type="search"
                    :placeholder="t('nav.search')"
                    class="block w-full pl-10 pr-10 py-2.5 border-none bg-gray-100 rounded-lg text-sm text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all"
                />

                <button
                    v-if="searchQuery"
                    @click="clearSearch"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                    title="Clear search"
                >
                    <i class="fas fa-times text-gray-400 hover:text-gray-600"></i>
                </button>
            </div>
        </div>

        <!-- Right side -->
        <div class="flex items-center gap-3">
            <LanguageSwitcher />

            <!-- Notifications / Help (Desktop only) -->
            <div class="hidden md:flex gap-2">
                <div class="relative">
                    <button
                        @click="toggleNotifications"
                        class="flex size-10 items-center justify-center rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors relative"
                    >
                        <i class="fas fa-bell"></i>
                        <span
                            v-if="unreadNotifications > 0"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
                        >
              {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
            </span>
                    </button>

                    <transition name="slide-down">
                        <div
                            v-if="notificationsOpen"
                            class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50 overflow-hidden"
                        >
                            <div class="p-4 border-b border-gray-200">
                                <div class="flex justify-between items-center">
                                    <h3 class="font-semibold text-gray-900">{{ t('nav.notifications') }}</h3>
                                    <button @click="markAllAsRead" class="text-sm text-primary font-medium">
                                        {{ t('nav.markAllRead') }}
                                    </button>
                                </div>
                            </div>

                            <div class="max-h-96 overflow-y-auto">
                                <div
                                    v-for="notification in notifications"
                                    :key="notification.id"
                                    class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
                                >
                                    <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ notification.message }}</p>
                                </div>

                                <div v-if="notifications.length === 0" class="p-8 text-center">
                                    <i class="fas fa-bell-slash text-3xl text-gray-300 mb-3"></i>
                                    <p class="text-gray-500">{{ t('nav.noNotifications') }}</p>
                                </div>
                            </div>

                            <div class="p-3 border-t border-gray-200 text-center">
                                <a href="/notifications" class="text-sm text-primary font-medium">
                                    {{ t('nav.viewAllNotifications') }}
                                </a>
                            </div>
                        </div>
                    </transition>
                </div>

                <button
                    class="flex size-10 items-center justify-center rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors"
                    title="Help"
                >
                    <i class="fas fa-question-circle"></i>
                </button>
            </div>

            <div class="h-8 w-px bg-gray-200 mx-2 hidden md:block"></div>

            <!-- User Menu -->
            <div class="relative">
                <button
                    @click="toggleUserMenu"
                    ref="userMenuButton"
                    class="flex items-center gap-3 focus:outline-none group"
                >
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold leading-none text-gray-900">{{ userName }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ userRole }}</p>
                    </div>

                    <div
                        class="size-10 rounded-full overflow-hidden border-2 border-gray-300 bg-center bg-cover"
                        :style="{ backgroundImage: `url(${userAvatar})` }"
                    ></div>

                    <i :class="userMenuOpen ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"
                       class="hidden md:block text-gray-500 text-sm"></i>
                </button>

                <transition name="slide-down">
                    <div
                        v-show="userMenuOpen"
                        ref="userDropdown"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 z-50 overflow-hidden"
                    >
                        <div class="py-2">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <div class="font-medium text-gray-900">{{ userName }}</div>
                                <div class="text-gray-500 text-xs truncate">{{ userEmail }}</div>
                                <div class="text-xs text-gray-400 mt-1">{{ userRole }}</div>
                            </div>

                            <a
                                :href="safeRoute('profile.edit')"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50"
                                @click="closeUserMenu"
                            >
                                <i class="fas fa-user text-gray-400 w-5"></i>
                                <span>{{ t('nav.profile') }}</span>
                            </a>

                            <a
                                href="/settings"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50"
                                @click="closeUserMenu"
                            >
                                <i class="fas fa-cog text-gray-400 w-5"></i>
                                <span>{{ t('nav.settings') }}</span>
                            </a>

                            <hr class="my-1 border-gray-200" />

                            <form @submit.prevent="logout">
                                <button
                                    type="submit"
                                    class="flex items-center gap-3 w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50"
                                >
                                    <i class="fas fa-sign-out-alt w-5"></i>
                                    <span>{{ t('nav.logout') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'

const { t } = useI18n()

const emit = defineEmits(['toggle-sidebar'])

const props = defineProps({
    sidebarWidth: { type: Number, default: 0 },
    sidebarCollapsed: Boolean,
    mobileSidebarOpen: Boolean
})

const page = usePage()

const searchQuery = ref('')
const userMenuOpen = ref(false)
const notificationsOpen = ref(false)
const userMenuButton = ref(null)
const userDropdown = ref(null)

// Safe Ziggy route wrapper (prevents blank component if route() missing)
const safeRoute = (name, params) => {
    try {
        if (typeof route === 'function') return route(name, params)
    } catch (e) {}
    return '#'
}

// User data
const user = computed(() => page.props?.auth?.user || {})
const userName = computed(() => user.value?.name || 'User')
const userEmail = computed(() => user.value?.email || 'user@example.com')
const userRole = computed(() => user.value?.role || 'User')
const userAvatar = computed(() => {
    if (user.value?.avatar) return user.value.avatar
    const name = userName.value || 'User'
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=5048e5&color=fff&size=128`
})

// Notifications (replace with real data later)
const notifications = ref([])
const unreadNotifications = computed(() => notifications.value.filter(n => !n.read).length)

const toggleMobileSidebar = () => emit('toggle-sidebar')

const toggleUserMenu = () => {
    userMenuOpen.value = !userMenuOpen.value
    if (notificationsOpen.value) notificationsOpen.value = false
}

const toggleNotifications = () => {
    notificationsOpen.value = !notificationsOpen.value
    if (userMenuOpen.value) userMenuOpen.value = false
}

const closeUserMenu = () => (userMenuOpen.value = false)

const handleSearch = () => {}
const clearSearch = () => (searchQuery.value = '')

const markAllAsRead = () => {
    notifications.value = notifications.value.map(n => ({ ...n, read: true }))
}

const logout = () => {
    router.post('/logout', {}, {
        onFinish: () => {
            window.location.href = '/login'
        }
    })
}
const handleClickOutside = (event) => {
    if (
        userMenuOpen.value &&
        userMenuButton.value &&
        !userMenuButton.value.contains(event.target) &&
        userDropdown.value &&
        !userDropdown.value.contains(event.target)
    ) {
        userMenuOpen.value = false
    }
}

const handleEscapeKey = (e) => {
    if (e.key === 'Escape') {
        userMenuOpen.value = false
        notificationsOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
    document.addEventListener('keydown', handleEscapeKey)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
    document.removeEventListener('keydown', handleEscapeKey)
})
</script>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active { transition: all 0.2s ease-out; }
.slide-down-enter-from,
.slide-down-leave-to { opacity: 0; transform: translateY(-10px); }
</style>
