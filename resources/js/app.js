import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createAppI18n } from './i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const locale = props.initialPage?.props?.locale || 'en';
        const i18n = createAppI18n(locale);

        document.documentElement.lang = locale === 'ne' ? 'ne' : 'en';

        router.on('navigate', (event) => {
            const newLocale = event.detail.page.props.locale;
            if (newLocale && i18n.global.locale.value !== newLocale) {
                i18n.global.locale.value = newLocale;
                document.documentElement.lang = newLocale === 'ne' ? 'ne' : 'en';
            }
        });

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18n)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
