<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const { t } = useI18n();
</script>

<template>
    <Head title="Welcome to CiviSense" />

    <div class="w-screen h-screen m-0 p-0 overflow-hidden font-['Poppins'] bg-slate-50 flex select-none">
        <div class="flex w-full h-full">

            <div class="relative hidden lg:flex flex-col justify-between w-[58%] bg-[#0A111E] p-16 overflow-hidden z-10">

                <header class="w-full z-10">
                    <div class="flex items-center justify-between gap-3">
                        <img src="logo.png" alt="CiviSense Logo" class="h-12 w-auto object-contain" />
                        <LanguageSwitcher />
                    </div>
                </header>

                <div class="my-auto max-w-xl z-10">
                    <h1 class="m-0 text-white leading-tight">
                        <span class="block text-4xl font-normal text-slate-200 opacity-90">{{ t('welcome.title') }}</span>
                        <span class="block text-5xl font-bold tracking-tight">Civi<span class="text-[#E63946]">Sense</span></span>
                    </h1>
                    <p class="mt-4 text-[15.5px] text-slate-400 font-light leading-relaxed">
                        {{ t('welcome.subtitle') }}
                    </p>

                    <div class="mt-12 grid grid-cols-2 gap-5 max-w-xl">
                        <div class="flex items-center gap-4 p-5 bg-white/[0.02] border border-white/[0.06] rounded-xl box-border">
                            <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 bg-[#13233C] border border-blue-500/30 text-blue-500">
                                <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                            </div>
                            <div>
                                <h4 class="m-0 text-white text-sm font-semibold">{{ t('welcome.reportIssues') }}</h4>
                                <p class="m-0 mt-1 text-slate-400 text-xs font-normal">{{ t('welcome.reportIssuesDesc') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-5 bg-white/[0.02] border border-white/[0.06] rounded-xl box-border">
                            <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 bg-[#13233C] border border-red-500/30 text-[#E63946]">
                                <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1s-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                            </div>
                            <div>
                                <h4 class="m-0 text-white text-sm font-semibold">{{ t('welcome.trackRequests') }}</h4>
                                <p class="m-0 mt-1 text-slate-400 text-xs font-normal">{{ t('welcome.trackRequestsDesc') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-5 bg-white/[0.02] border border-white/[0.06] rounded-xl box-border">
                            <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 bg-[#13233C] border border-blue-500/30 text-blue-500">
                                <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z"/></svg>
                            </div>
                            <div>
                                <h4 class="m-0 text-white text-sm font-semibold">{{ t('welcome.communityForum') }}</h4>
                                <p class="m-0 mt-1 text-slate-400 text-xs font-normal">{{ t('welcome.communityForumDesc') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-5 bg-white/[0.02] border border-white/[0.06] rounded-xl box-border">
                            <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 bg-[#13233C] border border-red-500/30 text-[#E63946]">
                                <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                            </div>
                            <div>
                                <h4 class="m-0 text-white text-sm font-semibold">{{ t('welcome.aiAnalytics') }}</h4>
                                <p class="m-0 mt-1 text-slate-400 text-xs font-normal">{{ t('welcome.aiAnalyticsDesc') }}</p>
                            </div>
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

                <div class="absolute top-0 right-0 h-full w-40 z-20 pointer-events-none">
                    <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="w-full h-full">
                        <path d="M100,0 C60,15 65,85 100,100 Z" fill="#FFFFFF" />
                        <path d="M100,0 C60,15 65,85 100,100" stroke="#E63946" stroke-width="1.5" fill="none" opacity="0.85" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col justify-between items-center flex-1 bg-white p-10 z-10">

                <div class="hidden lg:flex h-12 w-full max-w-[420px] justify-end">
                    <LanguageSwitcher />
                </div>

                <div class="w-full max-w-[420px] flex flex-col my-auto">

                    <div class="text-center mb-10">
                        <div class="flex flex-col items-center gap-2 mb-6">
                            <img src="logo.png" alt="CiviSense Logo Workspace Banner" class="w-44 h-auto object-contain mb-2" />

                            <div class="w-full flex flex-col items-center mt-1">
                                <div class="flex items-center w-full justify-center gap-2">
                                    <span class="h-[1px] w-8 bg-slate-200"></span>
                                    <span class="text-[13px] font-medium text-slate-700 tracking-wide">{{ t('welcome.nepaliTagline') }}</span>
                                    <span class="h-[1px] w-8 bg-slate-200"></span>
                                </div>
                                <span class="text-[10px] font-semibold text-slate-400 tracking-[0.2em] uppercase mt-1">{{ t('welcome.listenEngage') }}</span>
                            </div>
                            <p class="text-xs font-semibold tracking-[0.24em] text-slate-500">LISTEN &bull; ENGAGE &bull; IMPROVE</p>
                        </div>

                        <h3 class="m-0 text-[28px] font-bold text-[#0F172A] tracking-tight">{{ t('welcome.hubTitle') }}</h3>
                        <p class="m-0 mt-2 text-sm text-slate-500 leading-relaxed px-4">
                            {{ t('welcome.hubDesc') }}
                        </p>
                    </div>

                    <div class="mt-6 flex flex-col gap-4">
                        <template v-if="canLogin">
                            <Link
                                :href="route('login')"
                                class="flex h-12 items-center justify-center rounded-xl bg-[#020B24] px-6 text-base font-semibold text-white shadow-lg shadow-slate-950/15 transition hover:bg-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-300"
                            >
                                {{ t('welcome.signIn') }}
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="flex h-12 items-center justify-center rounded-xl border border-gray-300 bg-white px-6 text-base font-semibold text-slate-900 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-200"
                            >
                                {{ t('welcome.register') }}
                            </Link>
                        </template>

                        <Link
                            v-else
                            :href="route('dashboard')"
                            class="flex h-12 items-center justify-center rounded-xl bg-[#020B24] px-6 text-base font-semibold text-white shadow-lg shadow-slate-950/15 transition hover:bg-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-300"
                        >
                            {{ t('welcome.returnDashboard') }}
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
