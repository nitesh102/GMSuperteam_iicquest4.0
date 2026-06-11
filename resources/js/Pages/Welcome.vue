<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import AppLogo from '@/Components/common/AppLogo.vue';

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

            <div class="relative hidden lg:flex flex-col justify-between w-[58%] bg-[#0A111E] px-16 py-12 overflow-hidden z-10">

                <header class="w-full z-10">
                    <div class="flex items-center justify-between gap-3">
                        <AppLogo />
                        <LanguageSwitcher />
                    </div>
                </header>

                <div class="my-auto max-w-xl z-10">
                    <h1 class="leading-tight">
                        <span class="block text-5xl font-light text-slate-200">{{ t('welcome.title') }}</span>
                        <span class="block mt-3 text-6xl font-bold text-[#E63946]">CiviSense</span>
                    </h1>
                    <p class="mt-4 text-[15.5px] text-slate-400 font-light leading-relaxed">
                        {{ t('welcome.subtitle') }}
                    </p>

                    <div class="mt-8 grid grid-cols-2 gap-5 max-w-xl">
                        <div class="flex items-center gap-4 p-5 bg-white/[0.02] border border-white/[0.06] rounded-xl box-border hover:-translate-y-1 hover:bg-white/[0.05] hover:border-white/[0.12] transition-all duration-300">
                            <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 bg-[#13233C] border border-blue-500/30 text-blue-500">
                                <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                            </div>
                            <div>
                                <h4 class="m-0 text-white text-sm font-semibold">{{ t('welcome.reportIssues') }}</h4>
                                <p class="m-0 mt-1 text-slate-400 text-xs font-normal">{{ t('welcome.reportIssuesDesc') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-5 bg-white/[0.02] border border-white/[0.06] rounded-xl box-border hover:-translate-y-1 hover:bg-white/[0.05] hover:border-white/[0.12] transition-all duration-300">
                            <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 bg-[#13233C] border border-red-500/30 text-[#E63946]">
                                <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1s-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                            </div>
                            <div>
                                <h4 class="m-0 text-white text-sm font-semibold">{{ t('welcome.trackRequests') }}</h4>
                                <p class="m-0 mt-1 text-slate-400 text-xs font-normal">{{ t('welcome.trackRequestsDesc') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-5 bg-white/[0.02] border border-white/[0.06] rounded-xl box-border hover:-translate-y-1 hover:bg-white/[0.05] hover:border-white/[0.12] transition-all duration-300">
                            <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 bg-[#13233C] border border-blue-500/30 text-blue-500">
                                <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z"/></svg>
                            </div>
                            <div>
                                <h4 class="m-0 text-white text-sm font-semibold">{{ t('welcome.communityForum') }}</h4>
                                <p class="m-0 mt-1 text-slate-400 text-xs font-normal">{{ t('welcome.communityForumDesc') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-5 bg-white/[0.02] border border-white/[0.06] rounded-xl box-border hover:-translate-y-1 hover:bg-white/[0.05] hover:border-white/[0.12] transition-all duration-300">
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
            </div>

            <div class="relative flex h-full w-[42%] flex-col justify-center overflow-hidden bg-white px-8 lg:px-12">
                <div class="flex items-center justify-center">
                    <div class="mx-auto flex w-full max-w-md flex-col items-center">

                        <div class="mb-4 flex justify-center">
                            <AppLogo />
                        </div>

                        <div class="flex items-center gap-2 mb-1">
                            <span class="h-px w-8 bg-slate-200"></span>
                            <span class="text-[13px] font-medium text-slate-700 tracking-wide">{{ t('welcome.nepaliTagline') }}</span>
                            <span class="h-px w-8 bg-slate-200"></span>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-400 tracking-[0.2em] uppercase">LISTEN &bull; ENGAGE &bull; IMPROVE</span>

                        <div class="absolute top-0 right-0 h-full w-40 z-20 pointer-events-none">
                            <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="w-full h-full">
                                <path d="M100,0 C60,15 65,85 100,100 Z" fill="#FFFFFF" />
                                <path d="M100,0 C60,15 65,85 100,100" stroke="#E63946" stroke-width="1.5" fill="none" opacity="0.85" />
                            </svg>
                        </div>

                        <div class="text-center w-full mt-6">
                            <h3 class="text-[28px] font-bold text-[#0F172A] tracking-tight">{{ t('welcome.hubTitle') }}</h3>
                            <p class="mt-2 text-sm text-slate-500 leading-relaxed px-4">
                                {{ t('welcome.hubDesc') }}
                            </p>
                        </div>

                        <div class="mt-6 flex flex-col gap-4 w-full max-w-sm">
                            <template v-if="canLogin">
                                <Link
                                    :href="route('login')"
                                    class="flex h-14 items-center justify-center rounded-2xl bg-[#020B24] px-6 text-base font-semibold text-white shadow-lg shadow-slate-950/15 hover:scale-[1.02] transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-slate-300"
                                >
                                    {{ t('welcome.signIn') }}
                                </Link>

                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="flex h-14 items-center justify-center rounded-2xl border border-gray-300 bg-white px-6 text-base font-semibold text-slate-900 shadow-sm hover:scale-[1.02] hover:border-slate-400 hover:bg-slate-50 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-slate-200"
                                >
                                    {{ t('welcome.register') }}
                                </Link>
                            </template>

                            <Link
                                v-else
                                :href="route('dashboard')"
                                class="flex h-14 items-center justify-center rounded-2xl bg-[#020B24] px-6 text-base font-semibold text-white shadow-lg shadow-slate-950/15 hover:scale-[1.02] transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-slate-300"
                            >
                                {{ t('welcome.returnDashboard') }}
                            </Link>
                        </div>

                        <footer class="mt-6 text-center text-xs text-slate-400">
                            Laravel v{{ laravelVersion }} &bull; PHP v{{ phpVersion }}
                        </footer>

                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
