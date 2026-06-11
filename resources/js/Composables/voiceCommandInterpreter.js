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

    const createPatterns = [
        [/(create|add|make|open)(\s+(a\s+)?)?(new\s+)?(department|departments|विभाग)/iu, 'department'],
        [/^(new)\s+(department|departments|विभाग)/iu, 'department'],
        [/(नयाँ|नया|सिर्जना|बनाउ|थप).*(विभाग)/u, 'department'],
        [/(create|add|make|open)(\s+(a\s+)?)?(new\s+)?(category|categories|श्रेणी)/iu, 'category'],
        [/^(new)\s+(category|categories|श्रेणी)/iu, 'category'],
        [/(नयाँ|नया|सिर्जना|बनाउ|थप).*(श्रेणी)/u, 'category'],
        [/(create|add|make|open|file|report)(\s+(a\s+)?)?(new\s+)?(complaint|complaints|गुनासो)/iu, 'complaint'],
        [/^(new)\s+(complaint|complaints|गुनासो)/iu, 'complaint'],
        [/(नयाँ|नया|सिर्जना|बनाउ|थप).*(गुनासो)/u, 'complaint'],
    ];
    for (const [pattern, target] of createPatterns) {
        if (pattern.test(text)) {
            return { action: 'open_create', target, message: locale === 'ne' ? 'सिर्जना फारम खोल्दै।' : 'Opening create form.' };
        }
    }

    const navigation = [
        [/^(go\s+to|open|show|navigate\s+to|जाउ|खोल|हेर्नु)\s*(the\s+)?dashboard/iu, 'dashboard'],
        [/^(dashboard|ड्यासबोर्ड)$/iu, 'dashboard'],
        [/^(go\s+to|open|show|navigate\s+to|जाउ|खोल)\s*(the\s+)?(new\s+)?complaint(s?\s+create)?/iu, 'complaints.create'],
        [/^(new complaint|submit complaint|create complaint|file complaint|report issue|नयाँ गुनासो|गुनासो पेश|गुनासो दर्ता|समस्या रिपोर्ट)/iu, 'complaints.create'],
        [/^(go\s+to|open|show|navigate\s+to|जाउ|खोल)\s*(the\s+)?complaints?/iu, 'complaints.index'],
        [/^(complaints? list|view complaints?|my complaints?|गुनासो सूची|गुनासोहरू)/iu, 'complaints.index'],
        [/^(go\s+to|open|show|navigate\s+to|जाउ|खोल)\s*(the\s+)?departments?/iu, 'departments.index'],
        [/^(departments?|manage departments?|विभाग|विभागहरू)/iu, 'departments.index'],
        [/^(go\s+to|open|show|navigate\s+to|जाउ|खोल)\s*(the\s+)?categor(y|ies)/iu, 'complaint-categories.index'],
        [/^(categories?|complaint categories?|श्रेणी|श्रेणीहरू)/iu, 'complaint-categories.index'],
        [/^(go\s+to|open|show|navigate\s+to|जाउ|खोल)\s*(the\s+)?profile/iu, 'profile.edit'],
        [/^(profile|my profile|my account|प्रोफाइल|खाता)/iu, 'profile.edit'],
        [/^(go\s+to|open|show|navigate\s+to|जाउ|खोल)\s*(the\s+)?(login|sign in)/iu, 'login'],
        [/^(login|sign in|log in|साइन इन|लग इन)/iu, 'login'],
        [/^(go\s+to|open|show|navigate\s+to|जाउ|खोल)\s*(the\s+)?register/iu, 'register'],
        [/^(register|sign up|create account|दर्ता|खाता बनाउ)/iu, 'register'],
        [/^(home|welcome|landing|main page|गृहपृष्ठ|स्वागत)/iu, 'home'],
    ];

    for (const [pattern, route] of navigation) {
        if (pattern.test(lower) || pattern.test(text)) {
            return navigate(route, locale);
        }
    }

    if (/^(switch\s+to\s+)?(english|अंग्रेजी)/iu.test(lower)) {
        return { action: 'change_language', locale: 'en', message: msg(locale, 'en') };
    }
    if (/^(switch\s+to\s+)?(nepali|नेपाली)/iu.test(lower)) {
        return { action: 'change_language', locale: 'ne', message: msg(locale, 'ne') };
    }

    if (/^(log\s?out|sign\s?out|logout|लग आउट)/iu.test(lower)) {
        return { action: 'logout', message: locale === 'ne' ? 'लग आउट गर्दै।' : 'Logging out.' };
    }

    if (/^(go\s+back|back|previous page|पछाडि|अघिल्लो)/iu.test(lower)) {
        return { action: 'go_back', message: locale === 'ne' ? 'पछाडि जाँदै।' : 'Going back.' };
    }

    if (/^(submit|send|save)\s*(form|complaint|it)?$/iu.test(lower) ||
        /^(पेश गर्नु|फारम पेश|पठाउ)/u.test(text)) {
        return { action: 'submit_form', message: locale === 'ne' ? 'फारम पेश गर्दै।' : 'Submitting form.' };
    }

    const filterPatterns = [
        [/^(show|filter|display)\s+(pending|बाँकी)/iu, 'submitted'],
        [/^(show|filter|display)\s+(under review|समीक्षाधीन)/iu, 'under_review'],
        [/^(show|filter|display)\s+(in progress|progress|प्रगतिमा)/iu, 'in_progress'],
        [/^(show|filter|display)\s+(resolved|समाधान)/iu, 'resolved'],
        [/^(show|filter|display)\s+(all|सबै)/iu, ''],
        [/^(clear|reset)\s+(filters?|filter|फिल्टर)/iu, 'clear'],
    ];
    for (const [pattern, status] of filterPatterns) {
        if (pattern.test(lower) || pattern.test(text)) {
            if (status === 'clear') {
                return { action: 'clear_filters', message: locale === 'ne' ? 'फिल्टर हटाइयो।' : 'Filters cleared.' };
            }
            return { action: 'filter', filter: 'status', value: status, message: locale === 'ne' ? 'फिल्टर लागू गरियो।' : 'Filter applied.' };
        }
    }

    let searchMatch = text.match(/^(search|find|look for|खोज)\s+(for\s+)?(.+)$/iu);
    if (searchMatch) {
        return { action: 'search', value: searchMatch[3].trim(), message: locale === 'ne' ? 'खोजिरहेको छ।' : 'Searching.' };
    }

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
