<script setup>
import { onMounted, onUnmounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useVoiceCommands } from '@/Composables/useVoiceCommands';

const { t, locale } = useI18n();
const voice = useVoiceCommands();

const currentLanguageLabel = computed(() => {
    return locale.value === 'ne' ? 'नेपाली' : 'English';
});

const speechLanguageLabel = computed(() => {
    const lang = voice.speechLang;
    if (lang?.startsWith('ne')) return 'नेपाली';
    if (lang?.startsWith('hi')) return 'Hindi (fallback)';
    return 'English';
});

const commandGroups = computed(() => [
    {
        title: t('voice.groups.navigation'),
        items: [
            t('voice.commands.goDashboard'),
            t('voice.commands.goComplaints'),
            t('voice.commands.newComplaint'),
            t('voice.commands.goDepartments'),
            t('voice.commands.goCategories'),
            t('voice.commands.goProfile'),
        ],
    },
    {
        title: t('voice.groups.admin'),
        items: [
            t('voice.commands.newDepartment'),
            t('voice.commands.newCategory'),
            t('voice.commands.filterPending'),
            t('voice.commands.clearFilters'),
        ],
    },
    {
        title: t('voice.groups.general'),
        items: [
            t('voice.commands.switchNepali'),
            t('voice.commands.switchEnglish'),
            t('voice.commands.logout'),
            t('voice.commands.goBack'),
            t('voice.commands.searchExample'),
        ],
    },
]);

function handleKeydown(e) {
    if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'v') {
        e.preventDefault();
        voice.togglePanel();
    }
    if (e.key === 'Escape' && voice.panelOpen) {
        voice.closePanel();
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div aria-live="polite">
        <!-- Floating trigger -->
        <button
            type="button"
            @click="voice.togglePanel()"
            :title="t('voice.openAssistant')"
            :class="[
                'fixed bottom-6 right-6 z-[100] flex h-14 w-14 items-center justify-center rounded-full text-white shadow-lg transition-all hover:scale-105',
                voice.isListening ? 'bg-red-600 hover:bg-red-700 animate-pulse' : 'bg-indigo-600 hover:bg-indigo-700',
            ]"
        >
            <i :class="voice.isListening ? 'fas fa-stop' : 'fas fa-microphone-alt'" class="text-xl"></i>
        </button>

        <!-- Panel -->
        <Transition name="slide-up">
            <div
                v-if="voice.panelOpen"
                class="fixed bottom-24 right-6 z-[100] w-[22rem] sm:w-96 max-h-[70vh] rounded-2xl border border-gray-200 bg-white shadow-2xl overflow-hidden flex flex-col"
            >
                <div class="bg-indigo-600 px-4 py-3 text-white flex-shrink-0">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold">{{ t('voice.commandAssistant') }}</h3>
                            <p class="text-xs text-indigo-100 mt-0.5">{{ t('voice.systemWideHint') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs bg-indigo-500 px-2 py-1 rounded-full" :title="`Speech: ${voice.speechLang}`">
                                {{ speechLanguageLabel }}
                            </span>
                            <button type="button" @click="voice.closePanel()" class="text-white/80 hover:text-white p-1">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-4 space-y-3 overflow-y-auto flex-1">
                    <div
                        :class="[
                            'rounded-xl border-2 border-dashed p-4 text-center transition-colors',
                            voice.isListening ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50',
                        ]"
                    >
                        <i
                            :class="voice.isListening ? 'fas fa-circle text-red-500 animate-pulse' : 'fas fa-microphone text-gray-400'"
                            class="text-2xl mb-2"
                        ></i>
                        <p class="text-sm text-gray-700">
                            {{ voice.isListening ? t('voice.speakNow') : t('voice.commandHint') }}
                        </p>
                        <p v-if="voice.isListening" class="text-xs text-gray-500 mt-1">
                            {{ t('voice.continuousHint') }}
                        </p>
                        <p v-if="voice.interimTranscript || voice.transcript" class="text-xs text-gray-500 mt-2 italic break-words">
                            {{ voice.transcript || voice.interimTranscript }}
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="voice.isListening ? voice.stopCommand() : voice.startCommand()"
                        :disabled="voice.isProcessing || !voice.isSupported"
                        class="w-full py-2.5 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
                    >
                        <span v-if="voice.isProcessing">{{ t('voice.processing') }}</span>
                        <span v-else-if="voice.isListening">{{ t('voice.stopListening') }}</span>
                        <span v-else>{{ t('voice.startListening') }}</span>
                    </button>

                    <p v-if="voice.lastMessage" class="text-xs text-center text-indigo-600 font-medium">
                        {{ voice.lastMessage }}
                    </p>

                    <p v-if="voice.error === 'not-allowed'" class="text-xs text-center text-red-600">
                        {{ t('voice.micPermission') }}
                    </p>

                    <div v-if="!voice.isSupported" class="text-xs text-amber-600 text-center">
                        {{ t('voice.notSupported') }}
                    </div>

                    <div v-if="voice.showHelp || !voice.isAuthenticated" class="border-t border-gray-100 pt-3 space-y-3">
                        <div v-for="group in commandGroups" :key="group.title">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">
                                {{ group.title }}
                            </p>
                            <ul class="text-xs text-gray-500 space-y-1">
                                <li v-for="item in group.items" :key="item">• {{ item }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active { transition: all 0.25s ease; }
.slide-up-enter-from,
.slide-up-leave-to { opacity: 0; transform: translateY(12px); }
</style>
