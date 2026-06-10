<script setup>
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { locale, t } = useI18n();

const locales = [
    { code: 'en', label: 'English' },
    { code: 'ne', label: 'नेपाली' },
];

function switchLocale(code) {
    if (code === locale.value) return;

    router.post(route('locale.update'), { locale: code }, {
        preserveScroll: true,
        onSuccess: () => {
            locale.value = code;
            document.documentElement.lang = code === 'ne' ? 'ne' : 'en';
        },
    });
}
</script>

<template>
    <div class="flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 p-0.5">
        <button
            v-for="item in locales"
            :key="item.code"
            type="button"
            @click="switchLocale(item.code)"
            :class="[
                'px-2.5 py-1.5 text-xs font-medium rounded-md transition-colors',
                locale === item.code
                    ? 'bg-white text-indigo-700 shadow-sm'
                    : 'text-gray-600 hover:text-gray-900',
            ]"
            :title="t('language.label')"
        >
            {{ item.label }}
        </button>
    </div>
</template>
