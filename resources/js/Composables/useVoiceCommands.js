import { ref, reactive, computed, nextTick } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import { useSpeechRecognition } from './useSpeechRecognition';
import { interpretVoiceCommandLocally, ROUTES } from './voiceCommandInterpreter';
import { voiceHandlers, setPendingVoiceAction } from './useVoiceContext';

export function useVoiceCommands() {
    const { locale, t } = useI18n();
    const page = usePage();

    const isProcessing = ref(false);
    const lastMessage = ref('');
    const panelOpen = ref(false);
    const showHelp = ref(false);
    let processingLock = false;

    const isAuthenticated = computed(() => !!page.props.auth?.user);

    const speech = useSpeechRecognition({
        continuous: true,
        interimResults: true,
        locale,
        onEnd: (fullText) => {
            if (fullText && !processingLock) {
                processCommand(fullText);
            }
        },
    });

    const noiseWords = new Set(['the', 'a', 'an', 'is', 'it', 'to', 'of', 'in', 'on', 'for', 'and', 'or', 'but', 'with', 'at', 'by', 'from', 'as', 'was', 'are', 'be', 'this', 'that', 'we', 'i', 'you', 'he', 'she', 'they', 'my', 'your', 'its', 'what', 'which', 'who', 'how', 'when', 'where']);

    function isNoise(transcript) {
        const trimmed = transcript.trim();
        if (trimmed.length < 2) return true;
        const words = trimmed.toLowerCase().split(/\s+/).filter(w => w.length > 0);
        if (words.length === 0) return true;
        const meaningful = words.filter(w => !noiseWords.has(w));
        return meaningful.length === 0;
    }

    async function processCommand(transcript) {
        if (processingLock || !transcript?.trim()) return;
        if (isNoise(transcript)) return;

        processingLock = true;
        isProcessing.value = true;
        lastMessage.value = '';
        showHelp.value = false;

        try {
            let data = interpretVoiceCommandLocally(transcript.trim(), locale.value);

            if (!data || data.action === 'unknown') {
                if (isAuthenticated.value) {
                    try {
                        const response = await axios.post(route('voice.interpret'), {
                            transcript: transcript.trim(),
                            locale: locale.value,
                            current_page: page.url,
                            page_context: voiceHandlers.pageContext,
                        });
                        data = response.data;
                    } catch {
                        data = data || { action: 'unknown', message: t('voice.commandFailed') };
                    }
                } else {
                    data = data || { action: 'unknown', message: t('voice.commandFailed') };
                }
            }

            lastMessage.value = data.message || '';
            await executeAction(data);
        } catch (err) {
            console.error('Voice command error:', err);
            lastMessage.value = t('voice.commandFailed');
        } finally {
            isProcessing.value = false;
            processingLock = false;
            speech.reset();
        }
    }

    async function executeAction(data) {
        switch (data.action) {
            case 'navigate': {
                const target = ROUTES[data.route];
                if (!target) {
                    lastMessage.value = t('voice.commandFailed');
                    return;
                }
                if (target.startsWith('/')) {
                    router.visit(target);
                } else {
                    try {
                        router.visit(route(target));
                    } catch {
                        lastMessage.value = t('voice.commandFailed');
                        return;
                    }
                }
                panelOpen.value = false;
                break;
            }
            case 'fill_field':
                if (data.field && data.value && voiceHandlers.onFillField) {
                    voiceHandlers.onFillField(data.field, data.value);
                } else if (data.field && data.value) {
                    sessionStorage.setItem('voice_fill', JSON.stringify({
                        field: data.field,
                        value: data.value,
                    }));
                    router.visit(route('complaints.create'));
                } else {
                    lastMessage.value = t('voice.commandFailed');
                }
                break;
            case 'submit_form':
                if (voiceHandlers.onSubmitForm) {
                    voiceHandlers.onSubmitForm();
                } else {
                    lastMessage.value = t('voice.noFormToSubmit');
                }
                break;
            case 'search':
                if (voiceHandlers.onSearch && data.value) {
                    voiceHandlers.onSearch(data.value);
                } else {
                    lastMessage.value = t('voice.searchNotAvailable');
                }
                break;
            case 'filter':
                if (voiceHandlers.onFilter) {
                    voiceHandlers.onFilter(data.filter, data.value);
                } else {
                    lastMessage.value = t('voice.filterNotAvailable');
                }
                break;
            case 'clear_filters':
                if (voiceHandlers.onClearFilters) {
                    voiceHandlers.onClearFilters();
                } else {
                    lastMessage.value = t('voice.filterNotAvailable');
                }
                break;
            case 'open_create': {
                let target = data.target;
                if (target === 'auto') {
                    target = voiceHandlers.pageContext;
                }

                const createRoutes = {
                    department: 'departments.index',
                    category: 'complaint-categories.index',
                    complaint: 'complaints.create',
                };

                if (!target || !createRoutes[target]) {
                    lastMessage.value = t('voice.commandFailed');
                    break;
                }

                const onCorrectPage = (
                    (target === 'department' && page.url.startsWith('/departments')) ||
                    (target === 'category' && page.url.startsWith('/complaint-categories')) ||
                    (target === 'complaint' && page.url.includes('/complaints/create'))
                );

                if (onCorrectPage && voiceHandlers.onOpenCreate) {
                    await nextTick();
                    voiceHandlers.onOpenCreate(target);
                } else {
                    setPendingVoiceAction({ action: 'open_create', target });
                    router.visit(route(createRoutes[target]));
                }
                break;
            }
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
            case 'logout':
                if (isAuthenticated.value) {
                    router.post(route('logout'));
                    panelOpen.value = false;
                } else {
                    lastMessage.value = t('voice.loginRequired');
                }
                break;
            case 'go_back':
                window.history.back();
                panelOpen.value = false;
                break;
            case 'show_help':
                showHelp.value = true;
                break;
            default:
                lastMessage.value = data.message || t('voice.commandFailed');
                break;
        }
    }

    function openPanel() {
        panelOpen.value = true;
        lastMessage.value = '';
        showHelp.value = false;
        speech.reset();
    }

    function closePanel() {
        panelOpen.value = false;
        showHelp.value = false;
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

    function togglePanel() {
        if (panelOpen.value) {
            closePanel();
        } else {
            openPanel();
        }
    }

    return reactive({
        get transcript() { return speech.transcript; },
        get interimTranscript() { return speech.interimTranscript; },
        get isListening() { return speech.isListening; },
        get isSupported() { return speech.isSupported; },
        get error() { return speech.error; },
        get speechLang() { return speech.speechLang; },
        isProcessing,
        lastMessage,
        panelOpen,
        showHelp,
        isAuthenticated,
        openPanel,
        closePanel,
        togglePanel,
        startCommand,
        stopCommand,
        processCommand,
    });
}
