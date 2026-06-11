<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminStatsCard from '@/Components/AdminStatsCard.vue'
import { Line, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js'
import {
  CheckCircleIcon,
  ExclamationCircleIcon,
  ClockIcon,
  RectangleStackIcon,
} from '@heroicons/vue/24/outline'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const stats = [
  {
    title: 'Total Complaints',
    value: '128',
    change: '+12% from last week',
    icon: RectangleStackIcon,
  },
  {
    title: 'Open Complaints',
    value: '45',
    change: '+8% from last week',
    icon: ExclamationCircleIcon,
  },
  {
    title: 'Resolved Complaints',
    value: '78',
    change: '+15% from last week',
    icon: CheckCircleIcon,
  },
  {
    title: 'In Progress',
    value: '5',
    change: '-5% from last week',
    icon: ClockIcon,
  },
]

const lineChartData = {
  labels: ['Jun 4', 'Jun 5', 'Jun 6', 'Jun 7', 'Jun 8', 'Jun 9', 'Jun 10'],
  datasets: [
    {
      label: 'Total',
      data: [45, 52, 48, 61, 55, 67, 72],
      borderColor: '#2563eb',
      backgroundColor: 'rgba(37, 99, 235, 0.1)',
      borderWidth: 3,
      fill: true,
      tension: 0.4,
      pointRadius: 5,
      pointBackgroundColor: '#2563eb',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
    },
    {
      label: 'Resolved',
      data: [32, 38, 35, 44, 40, 50, 58],
      borderColor: '#ef4444',
      backgroundColor: 'rgba(239, 68, 68, 0.1)',
      borderWidth: 3,
      fill: true,
      tension: 0.4,
      pointRadius: 5,
      pointBackgroundColor: '#ef4444',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
    },
  ],
}

const lineChartOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: {
      display: true,
      position: 'top',
      labels: {
        font: {
          size: 12,
        },
        padding: 15,
        usePointStyle: true,
      },
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      max: 80,
      ticks: {
        font: {
          size: 12,
        },
      },
      grid: {
        color: 'rgba(0, 0, 0, 0.05)',
      },
    },
    x: {
      ticks: {
        font: {
          size: 12,
        },
      },
      grid: {
        display: false,
      },
    },
  },
}

const doughnutChartData = {
  labels: ['Health Department', 'Public Works', 'Education', 'Safety & Security', 'Others'],
  datasets: [
    {
      data: [45, 28, 22, 18, 15],
      backgroundColor: ['#3b82f6', '#ef4444', '#22c55e', '#f59e0b', '#8b5cf6'],
      borderColor: '#fff',
      borderWidth: 2,
    },
  ],
}

const doughnutChartOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: {
      display: true,
      position: 'bottom',
      labels: {
        font: {
          size: 12,
        },
        padding: 15,
      },
    },
    tooltip: {
      callbacks: {
        label: function (context) {
          const total = context.dataset.data.reduce((a, b) => a + b, 0)
          const percentage = ((context.parsed / total) * 100).toFixed(1)
          return `${context.label}: ${context.parsed} (${percentage}%)`
        },
      },
    },
  },
}
</script>

<template>
  <Head title="Dashboard" />

  <AdminLayout>
   

    <!-- Page Header -->
    <div class="mb-8 flex items-start justify-between">
      <div>
        <h1 class="text-3xl font-semibold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Welcome back! Here's what's happening with citizen complaints.</p>
      </div>
      <button class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
        📅 Jun 4 – Jun 10, 2026
      </button>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <AdminStatsCard
        v-for="stat in stats"
        :key="stat.title"
        :title="stat.title"
        :value="stat.value"
        :change="stat.change"
        :icon="stat.icon"
      />
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Complaints Over Time Chart -->
      <div class="lg:col-span-2 rounded-xl bg-white border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Complaints Over Time</h2>
        <div class="h-80">
          <Line :data="lineChartData" :options="lineChartOptions" />
        </div>
      </div>

      <!-- Top Departments Chart -->
      <div class="rounded-xl bg-white border border-gray-200 p-6 shadow-sm flex flex-col">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Top Departments</h2>
        <div class="relative flex-1 flex items-center justify-center">
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center">
              <p class="text-3xl font-bold text-gray-900">128</p>
              <p class="text-xs text-gray-500 mt-1">Total</p>
            </div>
          </div>
          <div class="w-full">
            <Doughnut :data="doughnutChartData" :options="doughnutChartOptions" />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
