import { ref, computed, reactive, onUnmounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { speechLocales, speechLocaleFallbacks } from '@/i18n';

export function useSpeechRecognition(options = {}) {
    const transcript = ref('');
    const interimTranscript = ref('');
    const isListening = ref(false);
    const error = ref(null);

    const page = usePage();
    const locale = computed(() => {
        if (options.locale?.value !== undefined) {
            return options.locale.value;
        }
        return page.props.locale ?? 'en';
    });

    const SpeechRecognition = typeof window !== 'undefined'
        ? (window.SpeechRecognition || window.webkitSpeechRecognition)
        : null;

    const isSupported = computed(() => !!SpeechRecognition);

    let recognition = null;
    let langIndex = 0;

    const speechLang = computed(() => {
        const fallbacks = speechLocaleFallbacks[locale.value] || speechLocaleFallbacks.en;
        return fallbacks[langIndex] || fallbacks[0];
    });

    function getFullTranscript() {
        return (transcript.value || interimTranscript.value).trim();
    }

    function createRecognition() {
        if (!SpeechRecognition) return null;

        const instance = new SpeechRecognition();
        instance.continuous = options.continuous ?? false;
        instance.interimResults = options.interimResults ?? true;
        instance.lang = speechLang.value;
        instance.maxAlternatives = 1;

        instance.onresult = (event) => {
            let interim = '';
            let final = '';

            for (let i = event.resultIndex; i < event.results.length; i++) {
                const text = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    final += text;
                } else {
                    interim += text;
                }
            }

            if (final) {
                transcript.value = options.append
                    ? (transcript.value + ' ' + final).trim()
                    : final.trim();
                options.onResult?.(transcript.value, final);
            }

            interimTranscript.value = interim;
        };

        instance.onerror = (event) => {
            if (event.error === 'language-not-supported' && tryNextLanguage()) {
                return;
            }

            error.value = event.error;
            isListening.value = false;
            options.onError?.(event.error);
        };

        instance.onend = () => {
            isListening.value = false;

            const fullText = getFullTranscript();
            if (fullText && !transcript.value) {
                transcript.value = fullText;
            }

            options.onEnd?.(fullText);
        };

        return instance;
    }

    function tryNextLanguage() {
        const fallbacks = speechLocaleFallbacks[locale.value] || speechLocaleFallbacks.en;
        if (langIndex < fallbacks.length - 1) {
            langIndex++;
            start();
            return true;
        }
        return false;
    }

    function start() {
        if (!isSupported.value) {
            error.value = 'not-supported';
            return;
        }

        error.value = null;
        if (!options.append) {
            transcript.value = '';
        }
        interimTranscript.value = '';

        try {
            recognition?.abort();
        } catch {
            // ignore
        }

        recognition = createRecognition();
        if (!recognition) return;

        try {
            isListening.value = true;
            recognition.start();
        } catch (err) {
            if (err.name === 'InvalidStateError') {
                isListening.value = false;
                setTimeout(() => start(), 300);
            } else {
                error.value = err.message;
                isListening.value = false;
            }
        }
    }

    function stop() {
        try {
            recognition?.stop();
        } catch {
            // ignore
        }
        isListening.value = false;
    }

    function toggle() {
        if (isListening.value) {
            stop();
        } else {
            langIndex = 0;
            start();
        }
    }

    function reset() {
        transcript.value = '';
        interimTranscript.value = '';
        error.value = null;
        langIndex = 0;
    }

    // Watch for locale changes and restart speech recognition if listening
    watch(locale, (newLocale, oldLocale) => {
        if (newLocale !== oldLocale && isListening.value) {
            langIndex = 0;
            start();
        }
    });

    onUnmounted(() => {
        try {
            recognition?.abort();
        } catch {
            // ignore
        }
    });

    return reactive({
        transcript,
        interimTranscript,
        isListening,
        isSupported,
        error,
        speechLang,
        getFullTranscript,
        start,
        stop,
        toggle,
        reset,
    });
}
