<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="Welcome" />

    <div class="flex min-h-screen flex-col bg-gray-50">
        <header class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                    <i class="fas fa-shield-alt text-indigo-500 text-xs"></i>
                </div>
                <span class="text-sm font-semibold text-gray-900">CiviSense</span>
            </div>
            <div class="flex items-center gap-3">
                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors"
                >
                    Sign In
                </Link>
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all duration-150"
                >
                    Get Started
                </Link>
                <Link
                    v-if="!canLogin"
                    :href="route('dashboard')"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all duration-150"
                >
                    Dashboard
                </Link>
            </div>
        </header>

        <main class="flex-1 flex flex-col items-center justify-center px-6 py-16">
            <div class="w-20 h-20 rounded-2xl bg-indigo-50 flex items-center justify-center mb-6">
                <i class="fas fa-shield-alt text-indigo-500 text-3xl"></i>
            </div>

            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 tracking-tight text-center">
                CiviSense
            </h1>
            <p class="mt-3 text-lg text-gray-500 text-center max-w-md">
                Smart Civic. Stronger Communities.
            </p>
            <p class="mt-2 text-sm text-gray-400 text-center max-w-lg">
                A community-driven platform for reporting and tracking civic issues.
                Submit complaints, monitor progress, and help build a better city.
            </p>

            <div class="mt-12 grid grid-cols-1 sm:grid-cols-3 gap-5 max-w-2xl w-full">
                <div class="rounded-xl border border-gray-100 bg-white p-5 text-left shadow-sm">
                    <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center mb-3">
                        <i class="fas fa-flag text-indigo-500 text-sm"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Report Issues</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                        Submit complaints about civic issues in your area quickly and easily.
                    </p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-5 text-left shadow-sm">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center mb-3">
                        <i class="fas fa-search text-emerald-500 text-sm"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Track Progress</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                        Monitor the status of complaints from submission to resolution.
                    </p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-5 text-left shadow-sm">
                    <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center mb-3">
                        <i class="fas fa-robot text-amber-500 text-sm"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">AI Powered</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                        Intelligent analysis for priority, categorization, and spam detection.
                    </p>
                </div>
            </div>

            <p class="mt-12 text-xs text-gray-400">
                Laravel {{ laravelVersion }} / PHP {{ phpVersion }}
            </p>
        </main>
    </div>
</template>
