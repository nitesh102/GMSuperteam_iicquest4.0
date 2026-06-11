<script setup>
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
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

const { t } = useI18n()
const { stats: rawStats } = usePage().props

const stats = computed(() => [
  {
    title: t('dashboard.totalComplaints'),
    value: String(rawStats?.total ?? 0),
    change: t('dashboard.allTime'),
    icon: RectangleStackIcon,
  },
  {
    title: t('dashboard.openPending'),
    value: String(rawStats?.pending ?? 0),
    change: t('dashboard.submittedReview'),
    icon: ExclamationCircleIcon,
  },
  {
    title: t('dashboard.resolved'),
    value: String(rawStats?.resolved ?? 0),
    change: t('dashboard.successfullyClosed'),
    icon: CheckCircleIcon,
  },
  {
    title: t('dashboard.inProgress'),
    value: String(rawStats?.inProgress ?? 0),
    change: t('dashboard.assignedProgress'),
    icon: ClockIcon,
  },
])

const lineChartData = computed(() => ({
  labels: ['Jun 4', 'Jun 5', 'Jun 6', 'Jun 7', 'Jun 8', 'Jun 9', 'Jun 10'],
  datasets: [
    {
      label: t('dashboard.chartTotal'),
      data: [45, 52, 48, 61, 55, 67, 72],
      borderColor: '#1D4ED8',
      backgroundColor: 'rgba(29, 78, 216, 0.08)',
      borderWidth: 3,
      fill: true,
      tension: 0.4,
      pointRadius: 4,
      pointHoverRadius: 6,
      pointBackgroundColor: '#1D4ED8',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
    },
    {
      label: t('dashboard.chartResolved'),
      data: [32, 38, 35, 44, 40, 50, 58],
      borderColor: '#EF4444',
      backgroundColor: 'rgba(239, 68, 68, 0.08)',
      borderWidth: 3,
      fill: true,
      tension: 0.4,
      pointRadius: 4,
      pointHoverRadius: 6,
      pointBackgroundColor: '#EF4444',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
    },
  ],
}))

const lineChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top',
      align: 'end',
      labels: {
        font: { size: 12, family: 'Inter, system-ui, sans-serif' },
        padding: 16,
        usePointStyle: true,
        boxWidth: 8,
      },
    },
    tooltip: {
      backgroundColor: '#0F172A',
      titleFont: { size: 12 },
      bodyFont: { size: 12 },
      padding: 12,
      cornerRadius: 8,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      max: 80,
      ticks: { font: { size: 11 }, color: '#94a3b8' },
      grid: { color: 'rgba(0, 0, 0, 0.04)' },
      border: { display: false },
    },
    x: {
      ticks: { font: { size: 11 }, color: '#94a3b8' },
      grid: { display: false },
      border: { display: false },
    },
  },
}

const doughnutChartData = computed(() => ({
  labels: [
    t('dashboard.deptHealth'),
    t('dashboard.deptPublicWorks'),
    t('dashboard.deptEducation'),
    t('dashboard.deptSafety'),
    t('dashboard.deptOthers'),
  ],
  datasets: [
    {
      data: [45, 28, 22, 18, 15],
      backgroundColor: ['#1D4ED8', '#EF4444', '#22C55E', '#F59E0B', '#8B5CF6'],
      borderColor: '#fff',
      borderWidth: 3,
      hoverOffset: 8,
    },
  ],
}))

const doughnutChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '72%',
  plugins: {
    legend: {
      display: true,
      position: 'bottom',
      labels: {
        font: { size: 11, family: 'Inter, system-ui, sans-serif' },
        padding: 12,
        usePointStyle: true,
        boxWidth: 8,
      },
    },
    tooltip: {
      backgroundColor: '#0F172A',
      padding: 12,
      cornerRadius: 8,
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
  <Head :title="t('dashboard.title')" />

  <AdminLayout>
    <div class="mb-10">
      <div class="flex items-start justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ t('dashboard.title') }}</h1>
          <p class="text-sm text-gray-500 mt-1.5">{{ t('dashboard.subtitle') }}</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
      <AdminStatsCard
        v-for="stat in stats"
        :key="stat.title"
        :title="stat.title"
        :value="stat.value"
        :change="stat.change"
        :icon="stat.icon"
      />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 card p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-6">{{ t('dashboard.complaintsOverTime') }}</h2>
        <div class="h-[300px]">
          <Line :data="lineChartData" :options="lineChartOptions" />
        </div>
      </div>

      <div class="card p-6 flex flex-col">
        <h2 class="text-base font-semibold text-gray-900 mb-6">{{ t('dashboard.topDepartments') }}</h2>
        <div class="relative flex-1 flex items-center justify-center">
          <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="text-center">
              <p class="text-3xl font-bold text-gray-900">128</p>
              <p class="text-xs text-gray-400 mt-1">{{ t('dashboard.total') }}</p>
            </div>
          </div>
          <div class="w-full h-[260px]">
            <Doughnut :data="doughnutChartData" :options="doughnutChartOptions" />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
