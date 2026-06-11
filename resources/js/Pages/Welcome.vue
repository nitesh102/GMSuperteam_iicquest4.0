<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import {
    ChatBubbleLeftRightIcon,
    ClipboardDocumentCheckIcon,
    MapPinIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline'
import logo from '@/assets/logo.png'

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
    version: String,
})

const frameworkVersion = computed(() => props.version || props.laravelVersion)

const features = [
    {
        title: 'Report Issues',
        description: 'Submit civic concerns with clarity, location context, and the details departments need.',
        icon: MapPinIcon,
    },
    {
        title: 'Track Requests',
        description: 'Follow complaint progress from submission to resolution with transparent updates.',
        icon: ClipboardDocumentCheckIcon,
    },
    {
        title: 'Community Forum',
        description: 'Engage with neighbors and local administration around shared civic priorities.',
        icon: ChatBubbleLeftRightIcon,
    },
    {
        title: 'AI-Powered Insights',
        description: 'Surface patterns, priorities, and response needs for smarter civic operations.',
        icon: SparklesIcon,
    },
]
</script>

<template>
    <Head title="Welcome to CiviSense" />

    <main class="min-h-screen h-screen overflow-hidden bg-white font-['Poppins'] text-slate-950">
        <div class="flex h-screen overflow-hidden">
            <section class="relative flex h-full w-[55%] flex-col justify-center overflow-hidden bg-[#020B24] px-8 py-8 lg:px-12 xl:px-14">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(29,78,216,0.32),transparent_34%),radial-gradient(circle_at_82%_12%,rgba(239,68,68,0.18),transparent_28%),radial-gradient(circle_at_54%_82%,rgba(14,165,233,0.14),transparent_36%)]"></div>
                <div class="absolute inset-x-0 bottom-0 h-40 opacity-25">
                    <svg viewBox="0 0 900 260" class="h-full w-full" preserveAspectRatio="none" aria-hidden="true">
                        <path d="M0 230H900V260H0V230Z" fill="white" />
                        <path d="M26 230V145H86V230H26ZM112 230V103H184V230H112ZM215 230V135H286V230H215ZM320 230V80H402V230H320ZM438 230V128H508V230H438ZM545 230V96H625V230H545ZM662 230V150H723V230H662ZM760 230V112H842V230H760Z" fill="white" />
                        <path d="M46 165H64M46 188H64M132 125H164M132 151H164M132 177H164M235 157H265M235 184H265M346 105H376M346 134H376M346 163H376M346 192H376M462 151H486M462 178H486M569 119H601M569 147H601M569 175H601M682 171H704M782 135H820M782 165H820M782 195H820" stroke="#020B24" stroke-width="8" stroke-linecap="round" opacity="0.55" />
                    </svg>
                </div>

                <div class="relative z-10 flex h-full w-full flex-col justify-center gap-6">
                    <header class="flex items-center gap-3">
                        <img :src="logo" alt="CiviSense Logo" class="h-8 w-auto shrink-0 object-contain" />
                        <span class="text-xl font-bold leading-none">
                            <span class="text-[#1D4ED8]">Civi</span><span class="text-[#EF4444]">Sense</span>
                        </span>
                    </header>

                    <div class="max-w-3xl">
                        <p class="mb-4 inline-flex rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 backdrop-blur">
                            Government-grade civic engagement platform
                        </p>

                        <h1 class="max-w-3xl text-5xl font-bold leading-[1.04] tracking-tight text-white xl:text-6xl">
                            Building Better Communities
                            <span class="block text-red-500">with Technology &amp; Transparency</span>
                        </h1>

                        <p class="mt-6 max-w-xl text-base leading-7 text-slate-300">
                            Report issues, track progress, engage with your community and help create a smarter, cleaner and more accountable civic ecosystem.
                        </p>

                        <div class="mt-6 grid max-w-2xl grid-cols-2 gap-4">
                            <article
                                v-for="feature in features"
                                :key="feature.title"
                                class="h-32 max-h-32 overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-4 shadow-lg shadow-black/10 backdrop-blur transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-black/20"
                            >
                                <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-2xl bg-white/10 text-red-400">
                                    <component :is="feature.icon" class="h-5 w-5" />
                                </div>
                                <h2 class="text-base font-semibold text-white">{{ feature.title }}</h2>
                                <p class="mt-1 text-xs leading-5 text-slate-300">{{ feature.description }}</p>
                            </article>
                        </div>
                    </div>
                </div>
            </section>

            <section class="relative flex h-full w-[45%] flex-col justify-center overflow-hidden bg-white px-8 py-8 lg:px-12">
                <div class="flex h-full items-center justify-center">
                <div class="mx-auto flex w-full max-w-md flex-col justify-center">
                    <div class="text-center">
                        <div class="mb-4 flex justify-center">
                            <img :src="logo" alt="CiviSense Logo" class="h-16 w-auto object-contain" />
                        </div>

                        <h2 class="text-3xl font-bold tracking-tight">
                            <span class="text-[#1D4ED8]">Civi</span><span class="text-[#EF4444]">Sense</span>
                        </h2>

                        <div class="mt-4 flex flex-col items-center gap-2">
                            <div class="flex w-full items-center justify-center gap-3">
                                <span class="h-px w-10 bg-slate-200"></span>
                                <p class="text-sm font-medium text-slate-600">तपाईंको आवाज, हाम्रो प्राथमिकता</p>
                                <span class="h-px w-10 bg-slate-200"></span>
                            </div>
                            <p class="text-xs font-semibold tracking-[0.24em] text-slate-500">LISTEN &bull; ENGAGE &bull; IMPROVE</p>
                        </div>

                        <div class="mt-8">
                            <h3 class="text-4xl font-bold tracking-tight text-slate-950">Smart Civic Hub</h3>
                            <p class="mt-4 text-base leading-7 text-slate-600">
                                Connect with local administration, monitor complaints progress, and help build a stronger community.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col gap-4">
                        <template v-if="canLogin">
                            <Link
                                :href="route('login')"
                                class="flex h-12 items-center justify-center rounded-xl bg-[#020B24] px-6 text-base font-semibold text-white shadow-lg shadow-slate-950/15 transition hover:bg-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-300"
                            >
                                Sign In to Account
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="flex h-12 items-center justify-center rounded-xl border border-gray-300 bg-white px-6 text-base font-semibold text-slate-900 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-200"
                            >
                                Get Started / Register
                            </Link>
                        </template>

                        <Link
                            v-else
                            :href="route('dashboard')"
                            class="flex h-12 items-center justify-center rounded-xl bg-[#020B24] px-6 text-base font-semibold text-white shadow-lg shadow-slate-950/15 transition hover:bg-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-300"
                        >
                            Return to Dashboard
                        </Link>
                    </div>

                    <footer class="mt-6 text-center text-xs text-slate-400">
                        Laravel v{{ frameworkVersion }} &bull; PHP v{{ phpVersion }}
                    </footer>
                </div>
                </div>
            </section>
        </div>
    </main>
</template>
