<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'

const stats = [
  { label: 'Total Complaints', value: '12,480', type: 'primary' },
  { label: 'Pending Issues', value: '1,240', type: 'danger' },
  { label: 'Resolved Cases', value: '10,200', type: 'secondary' },
  { label: 'Active Users', value: '8,540', type: 'dark' },
]

const recentComplaints = [
  { title: 'Broken Street Light', ward: 'Ward 5', status: 'Pending', type: 'danger' },
  { title: 'Water Leakage', ward: 'Ward 3', status: 'In Progress', type: 'primary' },
  { title: 'Garbage Overflow', ward: 'Ward 7', status: 'Resolved', type: 'secondary' },
  { title: 'Road Damage', ward: 'Ward 2', status: 'Pending', type: 'danger' },
]

// ✅ SAFE COLOR FUNCTIONS (BEST PRACTICE)
const getStatColor = (type) => {
  switch (type) {
    case 'primary':
      return 'text-[#064789]'
    case 'secondary':
      return 'text-[#427aa1]'
    case 'danger':
      return 'text-red-600'
    default:
      return 'text-slate-800'
  }
}

const getBadgeColor = (type) => {
  switch (type) {
    case 'primary':
      return 'bg-[#064789]/10 text-[#064789]'
    case 'secondary':
      return 'bg-[#427aa1]/10 text-[#427aa1]'
    case 'danger':
      return 'bg-red-50 text-red-600'
    default:
      return 'bg-slate-100 text-slate-800'
  }
}
</script>

<template>
<Head title="Dashboard" />

<AuthenticatedLayout>

  <template #header>
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-bold text-[#064789]">
        Civic Dashboard
      </h2>

      <span class="text-xs bg-white border border-[#427aa1] text-[#064789] px-3 py-1 rounded-full">
        Live System
      </span>
    </div>
  </template>

  <div class="min-h-screen bg-[#ebf2fa] py-10">

    <div class="mx-auto max-w-7xl px-6 lg:px-8 space-y-8">

      <!-- STATS -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        <div
          v-for="stat in stats"
          :key="stat.label"
          class="bg-white border border-[#427aa1]/20 rounded-2xl p-6 shadow-sm hover:shadow-md transition"
        >
          <p class="text-sm text-[#427aa1]">
            {{ stat.label }}
          </p>

          <p class="text-3xl font-extrabold mt-2"
             :class="getStatColor(stat.type)">
            {{ stat.value }}
          </p>

          <div class="mt-3 h-1 w-full bg-[#ebf2fa] rounded-full overflow-hidden">
            <div class="h-full bg-[#064789] w-2/3 rounded-full"></div>
          </div>
        </div>

      </div>

      <!-- TABLE -->
      <div class="bg-white border border-[#427aa1]/20 rounded-2xl shadow-sm overflow-hidden">

        <table class="w-full text-sm">

          <thead class="bg-[#064789] text-white">
            <tr>
              <th class="px-6 py-4 text-left">Issue</th>
              <th class="px-6 py-4 text-left">Ward</th>
              <th class="px-6 py-4 text-left">Status</th>
            </tr>
          </thead>

          <tbody>

            <tr
              v-for="(item, index) in recentComplaints"
              :key="index"
              class="border-b border-[#ebf2fa] hover:bg-[#ebf2fa] transition"
            >

              <td class="px-6 py-4 font-medium text-[#064789]">
                {{ item.title }}
              </td>

              <td class="px-6 py-4 text-[#427aa1]">
                {{ item.ward }}
              </td>

              <td class="px-6 py-4">
                <span
                  class="px-3 py-1 rounded-full text-xs font-semibold"
                  :class="getBadgeColor(item.type)"
                >
                  {{ item.status }}
                </span>
              </td>

            </tr>

          </tbody>

        </table>

      </div>

      <!-- QUICK ACTIONS -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="bg-[#064789] text-white rounded-2xl p-6 shadow">
          <h4 class="font-bold text-lg">View Complaints</h4>
          <p class="text-sm mt-1 text-[#ebf2fa]">
            Manage all citizen reports
          </p>
        </div>

        <div class="bg-[#427aa1] text-white rounded-2xl p-6 shadow">
          <h4 class="font-bold text-lg">Urgent Issues</h4>
          <p class="text-sm mt-1 text-[#ebf2fa]">
            High priority civic problems
          </p>
        </div>

        <div class="bg-white border border-[#427aa1]/30 text-[#064789] rounded-2xl p-6 shadow">
          <h4 class="font-bold text-lg">Analytics</h4>
          <p class="text-sm mt-1 text-[#427aa1]">
            Performance & reports
          </p>
        </div>

      </div>

    </div>

  </div>

</AuthenticatedLayout>
</template>
