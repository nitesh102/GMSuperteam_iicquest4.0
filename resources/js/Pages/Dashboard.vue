<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminStatsCard from '@/Components/AdminStatsCard.vue'
import {
  CheckCircleIcon,
  ExclamationCircleIcon,
  ClockIcon,
  RectangleStackIcon,
} from '@heroicons/vue/24/outline'

const stats = [
  {
    title: 'Total Complaints',
    value: '128',
    icon: RectangleStackIcon,
  },
  {
    title: 'Open Complaints',
    value: '45',
    icon: ExclamationCircleIcon,
  },
  {
    title: 'Resolved Complaints',
    value: '78',
    icon: CheckCircleIcon,
  },
  {
    title: 'In Progress',
    value: '5',
    icon: ClockIcon,
  },
]

const complaintsTrend = [
  { month: 'Jan', value: 45 },
  { month: 'Feb', value: 52 },
  { month: 'Mar', value: 48 },
  { month: 'Apr', value: 61 },
  { month: 'May', value: 55 },
  { month: 'Jun', value: 67 },
]

const topDepartments = [
  { name: 'Public Works', complaints: 45 },
  { name: 'Health', complaints: 32 },
  { name: 'Education', complaints: 28 },
  { name: 'Safety', complaints: 23 },
]
</script>

<template>
  <Head title="Dashboard" />

  <AdminLayout>
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-semibold text-gray-900">Dashboard</h1>
      <p class="text-sm text-gray-500 mt-1">Welcome back! Here's your system overview.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <AdminStatsCard
        v-for="stat in stats"
        :key="stat.title"
        :title="stat.title"
        :value="stat.value"
        :icon="stat.icon"
      />
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Complaints Over Time Chart -->
      <div class="lg:col-span-2 rounded-xl bg-white border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Complaints Over Time</h2>
        <div class="h-64 flex items-end gap-2">
          <div
            v-for="item in complaintsTrend"
            :key="item.month"
            class="flex-1 flex flex-col items-center"
          >
            <div
              class="w-full rounded-t-lg bg-red-500 transition hover:bg-red-600"
              :style="{ height: `${(item.value / 70) * 100}%` }"
            />
            <span class="text-xs text-gray-500 mt-2">{{ item.month }}</span>
          </div>
        </div>
      </div>

      <!-- Top Departments Chart -->
      <div class="rounded-xl bg-white border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Top Departments</h2>
        <div class="space-y-4">
          <div
            v-for="dept in topDepartments"
            :key="dept.name"
            class="flex items-center gap-3"
          >
            <div
              class="h-3 rounded-full"
              :style="{
                width: `${(dept.complaints / 45) * 100}%`,
                backgroundColor: ['#ef4444', '#2563eb', '#22c55e', '#f59e0b'][topDepartments.indexOf(dept) % 4],
              }"
            />
            <span class="text-sm text-gray-600">{{ dept.name }}</span>
            <span class="ml-auto text-sm font-semibold text-gray-900">{{ dept.complaints }}</span>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
