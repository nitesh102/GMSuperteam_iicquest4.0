import { onMounted, onUnmounted, reactive, nextTick } from 'vue';

const VOICE_ACTION_KEY = 'voice_action';

export const voiceHandlers = reactive({
    onFillField: null,
    onSubmitForm: null,
    onSearch: null,
    onFilter: null,
    onClearFilters: null,
    onOpenCreate: null,
    pageContext: null,
});

export function setPendingVoiceAction(action) {
    sessionStorage.setItem(VOICE_ACTION_KEY, JSON.stringify(action));
}

export function consumePendingVoiceAction(context) {
    const raw = sessionStorage.getItem(VOICE_ACTION_KEY);
    if (!raw) return null;

    try {
        const data = JSON.parse(raw);
        if (data.target !== context) {
            return null;
        }
        sessionStorage.removeItem(VOICE_ACTION_KEY);
        return data;
    } catch {
        sessionStorage.removeItem(VOICE_ACTION_KEY);
        return null;
    }
}

export function useVoiceContext() {
    return voiceHandlers;
}

export function useVoicePageHandlers(handlers, context = null) {
    onMounted(() => {
        Object.entries(handlers).forEach(([key, fn]) => {
            if (key in voiceHandlers) {
                voiceHandlers[key] = fn;
            }
        });
        if (context) {
            voiceHandlers.pageContext = context;
        }

        if (context) {
            const pending = consumePendingVoiceAction(context);
            if (pending?.action === 'open_create' && handlers.onOpenCreate) {
                nextTick(() => {
                    handlers.onOpenCreate(pending.target || context);
                });
            }
        }
    });

    onUnmounted(() => {
        Object.keys(handlers).forEach((key) => {
            if (key in voiceHandlers && voiceHandlers[key] === handlers[key]) {
                voiceHandlers[key] = null;
            }
        });
        if (voiceHandlers.pageContext === context) {
            voiceHandlers.pageContext = null;
        }
    });
}
