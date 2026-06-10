import { ref, reactive } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import { useSpeechRecognition } from './useSpeechRecognition';

const routeMap = {
    dashboard: 'dashboard',
    'complaints.index': 'complaints.index',
    'complaints.create': 'complaints.create',
    'departments.index': 'departments.index',
    'complaint-categories.index': 'complaint-categories.index',
    'profile.edit': 'profile.edit',
    home: '/',
};

export function useVoiceCommands(handlers = {}) {
    const { locale, t } = useI18n();
    const page = usePage();

    const isProcessing = ref(false);
    const lastMessage = ref('');
    const panelOpen = ref(false);
    let processingLock = false;

    const speech = useSpeechRecognition({
        continuous: true,
        interimResults: true,
        onEnd: (fullText) => {
            if (fullText && !processingLock) {
                processCommand(fullText);
            }
        },
    });

    async function processCommand(transcript) {
        if (processingLock || !transcript?.trim()) return;

        processingLock = true;
        isProcessing.value = true;
        lastMessage.value = '';

        try {
            const { data } = await axios.post(route('voice.interpret'), {
                transcript: transcript.trim(),
                locale: locale.value,
                current_page: page.url,
            });

            lastMessage.value = data.message || '';
            await executeAction(data);
        } catch (err) {
            console.error('Voice command error:', err);
            lastMessage.value = locale.value === 'ne'
                ? 'आदेश प्रशोधन गर्न सकिएन।'
                : 'Failed to process command.';
        } finally {
            isProcessing.value = false;
            processingLock = false;
            speech.reset();
        }
    }

    async function executeAction(data) {
        switch (data.action) {
            case 'navigate': {
                const target = routeMap[data.route];
                if (target) {
                    if (target.startsWith('/')) {
                        router.visit(target);
                    } else {
                        router.visit(route(target));
                    }
                    panelOpen.value = false;
                } else {
                    lastMessage.value = t('voice.commandFailed');
                }
                break;
            }
            case 'fill_field':
                if (data.field && data.value) {
                    handlers.onFillField?.(data.field, data.value);
                } else {
                    lastMessage.value = t('voice.commandFailed');
                }
                break;
            case 'submit_form':
                if (handlers.onSubmitForm) {
                    handlers.onSubmitForm();
                } else {
                    lastMessage.value = t('voice.commandFailed');
                }
                break;
            case 'change_language':
                if (data.locale && data.locale !== locale.value) {
                    router.post(route('locale.update'), { locale: data.locale }, {
                        preserveScroll: true,
                        onSuccess: () => {
                            locale.value = data.locale;
                            document.documentElement.lang = data.locale === 'ne' ? 'ne' : 'en';
                        },
                    });
                }
                break;
            default:
                lastMessage.value = data.message || t('voice.commandFailed');
                break;
        }
    }

    function openPanel() {
        panelOpen.value = true;
        lastMessage.value = '';
        speech.reset();
    }

    function closePanel() {
        panelOpen.value = false;
        if (speech.isListening) {
            speech.stop();
        }
        speech.reset();
    }

    function startCommand() {
        speech.reset();
        speech.start();
    }

    function stopCommand() {
        speech.stop();
    }

    return reactive({
        get transcript() { return speech.transcript; },
        get interimTranscript() { return speech.interimTranscript; },
        get isListening() { return speech.isListening; },
        get isSupported() { return speech.isSupported; },
        get error() { return speech.error; },
        isProcessing,
        lastMessage,
        panelOpen,
        openPanel,
        closePanel,
        startCommand,
        stopCommand,
        processCommand,
    });
}
