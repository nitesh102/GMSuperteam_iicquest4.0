<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Welcome to CiviSense. Here's an overview of the system.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center mb-3">
                        <i class="fas fa-flag text-indigo-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Total Complaints</p>
                    <p class="text-2xl font-bold text-gray-900 mt-0.5">—</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                    <div class="w-10 h-10 rounded-lg bg-yellow-50 flex items-center justify-center mb-3">
                        <i class="fas fa-clock text-yellow-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-gray-900 mt-0.5">—</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                    <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center mb-3">
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Resolved</p>
                    <p class="text-2xl font-bold text-gray-900 mt-0.5">—</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                    <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center mb-3">
                        <i class="fas fa-shield-alt text-red-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Spam</p>
                    <p class="text-2xl font-bold text-gray-900 mt-0.5">—</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center shadow-sm">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chart-bar text-indigo-400 text-2xl"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900 mb-1">Welcome to CiviSense</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto">
                    Start by exploring the Complaints section to view and manage citizen reports,
                    or visit Departments to configure the organizational structure.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
