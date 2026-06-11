import { createI18n } from 'vue-i18n';
import en from '../locales/en.json';
import ne from '../locales/ne.json';

export function createAppI18n(locale = 'en') {
    return createI18n({
        legacy: false,
        locale,
        fallbackLocale: 'en',
        messages: { en, ne },
    });
}

export const speechLocales = {
    en: 'en-US',
    ne: 'ne-NP',
};

// Browsers often lack ne-NP; fall back through Devanagari-capable then English.
export const speechLocaleFallbacks = {
    en: ['en-US', 'en-GB'],
    ne: ['ne-NP', 'hi-IN', 'en-US'],
};
