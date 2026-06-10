<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    data: { type: Object, required: true },
})
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-3 border-t border-gray-200 bg-gray-50/50">
        <p class="text-sm text-gray-600">
            Showing
            <span class="font-medium">{{ data.from ?? 0 }}</span>
            –
            <span class="font-medium">{{ data.to ?? 0 }}</span>
            of
            <span class="font-medium">{{ data.total }}</span>
            results
        </p>

        <div v-if="data.last_page > 1" class="flex items-center gap-1 flex-wrap">
            <template v-for="link in data.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-state
                    preserve-scroll
                    class="px-3 py-1.5 text-xs rounded-lg border transition-colors"
                    :class="link.active
                        ? 'bg-indigo-600 text-white border-indigo-600 font-medium'
                        : 'border-gray-300 text-gray-700 hover:bg-gray-100'"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
