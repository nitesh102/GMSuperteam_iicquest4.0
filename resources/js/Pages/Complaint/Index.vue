<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminEmptyState from '@/Components/AdminEmptyState.vue'
import { ClipboardDocumentListIcon } from '@heroicons/vue/24/outline'

const hasComplaints = ref(false)
</script>

<template>
  <Head title="Complaints" />

  <AdminLayout>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-semibold text-gray-900">Complaints</h1>
          <p class="text-sm text-gray-500 mt-1">View and manage all citizen complaints.</p>
        </div>
        <Link
          href="complaints.create"
          class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-6 py-3 text-sm font-medium text-white hover:bg-red-600 transition"
        >
          + New Complaint
        </Link>
      </div>
    </div>

    <!-- Empty State -->
    <AdminEmptyState
      v-if="!hasComplaints"
      :icon="ClipboardDocumentListIcon"
      title="No complaints yet"
      description="Citizen complaints will appear here once they are submitted. Submit a test complaint to get started."
      button-text="+ Submit Complaint"
      link-text="Learn how complaints work"
      button-href="complaints.create"
    />

    <!-- Complaints Table (when hasComplaints is true) -->
    <div v-else class="rounded-xl bg-white border border-gray-200 shadow-sm">
      <!-- Table will be displayed here when complaints exist -->
    </div>
  </AdminLayout>
</template>
