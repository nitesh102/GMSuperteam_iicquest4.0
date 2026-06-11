<script setup>
import { useI18n } from 'vue-i18n';
import { useVoiceCommands } from '@/Composables/useVoiceCommands';

const props = defineProps({
    onFillField: { type: Function, default: null },
    onSubmitForm: { type: Function, default: null },
});

const { t } = useI18n();

const voice = useVoiceCommands({
    onFillField: (field, value) => props.onFillField?.(field, value),
    onSubmitForm: () => props.onSubmitForm?.(),
});

defineExpose({ voice });
</script>

<template>
    <div>
        <!-- Floating trigger -->
        <button
            type="button"
            @click="voice.panelOpen ? voice.closePanel() : voice.openPanel()"
            :title="t('voice.openAssistant')"
            class="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg hover:bg-indigo-700 transition-all hover:scale-105"
        >
            <i :class="voice.isListening ? 'fas fa-stop' : 'fas fa-microphone-alt'" class="text-xl"></i>
        </button>

        <!-- Panel -->
        <Transition name="slide-up">
            <div
                v-if="voice.panelOpen"
                class="fixed bottom-24 right-6 z-50 w-80 rounded-2xl border border-gray-200 bg-white shadow-2xl overflow-hidden"
            >
                <div class="bg-indigo-600 px-4 py-3 text-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold">{{ t('voice.commandAssistant') }}</h3>
                        <button type="button" @click="voice.closePanel()" class="text-white/80 hover:text-white">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <p class="text-xs text-indigo-100 mt-1">{{ t('voice.commandHint') }}</p>
                </div>

                <div class="p-4 space-y-3">
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
                        <p v-if="voice.interimTranscript || voice.transcript" class="text-xs text-gray-500 mt-2 italic">
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

                    <div class="border-t border-gray-100 pt-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">
                            {{ t('voice.commandAssistant') }}
                        </p>
                        <ul class="text-xs text-gray-500 space-y-1">
                            <li>• {{ t('voice.commands.goDashboard') }}</li>
                            <li>• {{ t('voice.commands.newComplaint') }}</li>
                            <li>• {{ t('voice.commands.switchNepali') }}</li>
                        </ul>
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
