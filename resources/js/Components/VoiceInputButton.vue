<script setup>
import { watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useSpeechRecognition } from '@/Composables/useSpeechRecognition';

const props = defineProps({
    modelValue: { type: String, default: '' },
    append: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue']);

const { t } = useI18n();

const speech = useSpeechRecognition({
    append: props.append,
    continuous: true,
    interimResults: true,
    onResult: (full) => {
        emit('update:modelValue', full);
    },
    onEnd: (fullText) => {
        if (fullText) {
            emit('update:modelValue', props.append ? fullText : fullText);
        }
    },
});

watch(() => props.modelValue, (val) => {
    if (!speech.isListening) {
        speech.transcript = val;
    }
});

function handleClick() {
    if (!speech.isSupported) {
        alert(t('voice.notSupported'));
        return;
    }

    if (speech.isListening) {
        speech.stop();
    } else {
        speech.transcript = props.append ? props.modelValue : '';
        speech.start();
    }
}
</script>

<template>
    <button
        type="button"
        @click="handleClick"
        :title="speech.isListening ? t('voice.stopListening') : t('voice.startListening')"
        :class="[
            'inline-flex items-center justify-center w-9 h-9 rounded-lg transition-all',
            speech.isListening
                ? 'bg-red-100 text-red-600 animate-pulse'
                : 'bg-gray-100 text-gray-600 hover:bg-indigo-100 hover:text-indigo-600',
        ]"
    >
        <i :class="speech.isListening ? 'fas fa-stop' : 'fas fa-microphone'"></i>
    </button>
</template>
