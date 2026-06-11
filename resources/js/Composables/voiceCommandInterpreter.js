const ROUTES = {
    dashboard: 'dashboard',
    'complaints.index': 'complaints.index',
    'complaints.create': 'complaints.create',
    'departments.index': 'departments.index',
    'complaint-categories.index': 'complaint-categories.index',
    'profile.edit': 'profile.edit',
    login: 'login',
    register: 'register',
    home: '/',
};

export { ROUTES };

export function interpretVoiceCommandLocally(transcript, locale = 'en') {
    const text = transcript.trim();
    if (!text) {
        return unknown(locale);
    }

    const lower = text.toLowerCase();

    // --- Create patterns ---
    if (/(create|add|make|open|new|file|report).*(department|departments|विभाग)/iu.test(lower)) {
        return { action: 'open_create', target: 'department', message: locale === 'ne' ? 'सिर्जना फारम खोल्दै।' : 'Opening create form.' };
    }
    if (/(create|add|make|open|new).*(category|categories|श्रेणी)/iu.test(lower)) {
        return { action: 'open_create', target: 'category', message: locale === 'ne' ? 'सिर्जना फारम खोल्दै।' : 'Opening create form.' };
    }
    if (/(create|add|make|open|new|file|report).*(complaint|complaints|गुनासो)/iu.test(lower)) {
        return { action: 'open_create', target: 'complaint', message: locale === 'ne' ? 'सिर्जना फारम खोल्दै।' : 'Opening create form.' };
    }

    // --- Navigation (keyword-based, word-boundary) ---
    if (/\bdashboard\b/iu.test(lower) || /\bड्यासबोर्ड\b/u.test(lower)) {
        return navigate('dashboard', locale);
    }
    if (/\b(new|create|submit|file|report)\s+(complaint|गुनासो)\b/iu.test(lower)) {
        return navigate('complaints.create', locale);
    }
    if (/\bcomplaint(s|\b|\s+list|\s+index)?\b/iu.test(lower) || /\bगुनासो(s|हरू)?\b/u.test(lower)) {
        return navigate('complaints.index', locale);
    }
    if (/\bdepartments?\b/iu.test(lower) || /\bविभाग(हरू)?\b/u.test(lower)) {
        return navigate('departments.index', locale);
    }
    if (/\bcategor(y|ies)\b/iu.test(lower) || /\bश्रेणी(हरू)?\b/u.test(lower)) {
        return navigate('complaint-categories.index', locale);
    }
    if (/\bprofile\b/iu.test(lower) || /\bप्रोफाइल\b/u.test(lower) || /\bखाता\b/u.test(lower)) {
        return navigate('profile.edit', locale);
    }
    if (/\blog(in| out|out)\b/iu.test(lower) || /\bsign\s+in\b/iu.test(lower) || /\bसाइन इन\b/u.test(lower) || /\bलग इन\b/u.test(lower)) {
        return navigate('login', locale);
    }
    if (/\bregister\b/iu.test(lower) || /\bsign\s+up\b/iu.test(lower) || /\bcreate account\b/iu.test(lower) || /\bदर्ता\b/u.test(lower) || /\bखाता बनाउ\b/u.test(lower)) {
        return navigate('register', locale);
    }
    if (/\b(home|welcome|landing|main page)\b/iu.test(lower) || /\bगृहपृष्ठ\b/u.test(lower) || /\bस्वागत\b/u.test(lower)) {
        return navigate('home', locale);
    }

    // --- Language ---
    if (/\b(english|अंग्रेजी)\b/iu.test(lower)) {
        return { action: 'change_language', locale: 'en', message: msg(locale, 'en') };
    }
    if (/\b(nepali|नेपाली)\b/iu.test(lower)) {
        return { action: 'change_language', locale: 'ne', message: msg(locale, 'ne') };
    }

    // --- Actions ---
    if (/\b(log\s?out|sign\s?out|logout|लग आउट)\b/iu.test(lower)) {
        return { action: 'logout', message: locale === 'ne' ? 'लग आउट गर्दै।' : 'Logging out.' };
    }
    if (/\b(go\s+back|back|previous|पछाडि|अघिल्लो)\b/iu.test(lower)) {
        return { action: 'go_back', message: locale === 'ne' ? 'पछाडि जाँदै।' : 'Going back.' };
    }
    if (/\b(submit|send|save)\s*(form|complaint|it)?\b/iu.test(lower) || /\b(पेश गर्नु|फारम पेश|पठाउ)\b/u.test(lower)) {
        return { action: 'submit_form', message: locale === 'ne' ? 'फारम पेश गर्दै।' : 'Submitting form.' };
    }

    // --- Filters ---
    if (/\b(under\s*review|समीक्षाधीन)\b/iu.test(lower)) {
        return { action: 'filter', filter: 'status', value: 'under_review', message: locale === 'ne' ? 'समीक्षाधीन फिल्टर गरियो।' : 'Filtering under review.' };
    }
    if (/\b(in\s*progress|progress|प्रगतिमा)\b/iu.test(lower)) {
        return { action: 'filter', filter: 'status', value: 'in_progress', message: locale === 'ne' ? 'प्रगतिमा फिल्टर गरियो।' : 'Filtering in progress.' };
    }
    if (/\b(resolved|समाधान)\b/iu.test(lower)) {
        return { action: 'filter', filter: 'status', value: 'resolved', message: locale === 'ne' ? 'समाधान फिल्टर गरियो।' : 'Filtering resolved.' };
    }
    if (/\b(pending|submitted|बाँकी)\b/iu.test(lower)) {
        return { action: 'filter', filter: 'status', value: 'submitted', message: locale === 'ne' ? 'बाँकी फिल्टर गरियो।' : 'Filtering pending.' };
    }
    if (/\b(clear|reset)\s*filters?\b/iu.test(lower) || /\bफिल्टर हटाउ\b/u.test(lower)) {
        return { action: 'clear_filters', message: locale === 'ne' ? 'फिल्टर हटाइयो।' : 'Filters cleared.' };
    }

    // --- Search ---
    let searchMatch = text.match(/^(search|find|look for|खोज)\s+(for\s+)?(.+)$/iu);
    if (searchMatch) {
        return { action: 'search', value: searchMatch[3].trim(), message: locale === 'ne' ? 'खोजिरहेको छ।' : 'Searching.' };
    }

    // --- Fill field ---
    let nameMatch = text.match(/^(name|नाम)\s*(is|:)?\s*(.+)$/iu);
    if (nameMatch) {
        return { action: 'fill_field', field: 'name', value: nameMatch[3].trim(), message: locale === 'ne' ? 'नाम सेट गरियो।' : 'Name updated.' };
    }
    let titleMatch = text.match(/^(title|शीर्षक)\s*(is|:)?\s*(.+)$/iu);
    if (titleMatch) {
        return { action: 'fill_field', field: 'title', value: titleMatch[3].trim(), message: locale === 'ne' ? 'शीर्षक सेट गरियो।' : 'Title updated.' };
    }
    let descMatch = text.match(/^(description|विवरण)\s*(is|:)?\s*(.+)$/iu);
    if (descMatch) {
        return { action: 'fill_field', field: 'description', value: descMatch[3].trim(), message: locale === 'ne' ? 'विवरण सेट गरियो।' : 'Description updated.' };
    }
    let locMatch = text.match(/^(location|address|स्थान|ठेगाना)\s*(is|:)?\s*(.+)$/iu);
    if (locMatch) {
        return { action: 'fill_field', field: 'location', value: locMatch[3].trim(), message: locale === 'ne' ? 'स्थान सेट गरियो।' : 'Location updated.' };
    }

    // --- Keyword fallback ---
    if (/\bhome\b/iu.test(lower) || /\bगृहपृष्ठ\b/u.test(lower)) {
        return navigate('home', locale);
    }
    if (/\bdashboard\b/iu.test(lower) || /\bड्यासबोर्ड\b/u.test(lower)) {
        return navigate('dashboard', locale);
    }
    if (/\bcomplaint\b/iu.test(lower) || /\bगुनासो\b/u.test(lower)) {
        return navigate('complaints.index', locale);
    }
    if (/\bdepartment\b/iu.test(lower) || /\bविभाग\b/u.test(lower)) {
        return navigate('departments.index', locale);
    }
    if (/\bcategor(y|ies)\b/iu.test(lower) || /\bश्रेणी\b/u.test(lower)) {
        return navigate('complaint-categories.index', locale);
    }
    if (/\blog(out|out|in)\b/iu.test(lower)) {
        return { action: 'logout', message: locale === 'ne' ? 'लग आउट गर्दै।' : 'Logging out.' };
    }

    // --- Help ---
    if (/^(help|what can i say|commands|मद्दत|आदेश)/iu.test(lower)) {
        return { action: 'show_help', message: locale === 'ne' ? 'उपलब्ध आदेशहरू देखाइयो।' : 'Showing available commands.' };
    }

    return null;
}

function navigate(route, locale) {
    return {
        action: 'navigate',
        route,
        message: locale === 'ne' ? 'पृष्ठमा जाँदै।' : 'Navigating.',
        source: 'local',
    };
}

function unknown(locale) {
    return {
        action: 'unknown',
        message: locale === 'ne'
            ? 'माफ गर्नुहोस्, त्यो आदेश बुझिएन।'
            : 'Sorry, I could not understand that command.',
        source: 'local',
    };
}

function msg(currentLocale, targetLocale) {
    if (currentLocale === 'ne') {
        return targetLocale === 'ne' ? 'नेपालीमा बदलियो।' : 'अंग्रेजीमा बदलियो।';
    }
    return targetLocale === 'ne' ? 'Switched to Nepali.' : 'Switched to English.';
}
