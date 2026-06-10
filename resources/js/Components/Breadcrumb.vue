<template>
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a :href="route('dashboard')"
                   class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600">
                    <i class="fas fa-home mr-2"></i>
                    Home
                </a>
            </li>
            <li v-if="$page.props.breadcrumb?.model">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">{{ $page.props.breadcrumb.model }}</span>
                </div>
            </li>
            <li v-if="$page.props.breadcrumb?.action" aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-700">{{ $page.props.breadcrumb.action }}</span>
                </div>
            </li>
        </ol>
    </nav>
</template>

<script setup>
// This component uses $page and route which are injected by Inertia and our plugin
</script>
