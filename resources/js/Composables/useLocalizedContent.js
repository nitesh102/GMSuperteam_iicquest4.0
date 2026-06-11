import { useI18n } from 'vue-i18n';

const DEPARTMENT_KEYS = {
    'Health Department': 'health_department',
    'Roads & Infrastructure': 'roads_infrastructure',
    'Water Supply & Sanitation': 'water_sanitation',
    'Waste Management': 'waste_management',
    'Electricity & Public Lighting': 'electricity_lighting',
    'Public Safety & Security': 'public_safety',
    'Environment & Parks': 'environment_parks',
    'Transportation': 'transportation',
};

export function useLocalizedContent() {
    const { t, te, locale } = useI18n();

    function localizeDepartment(dept) {
        if (!dept || locale.value === 'en') {
            return dept;
        }

        const key = DEPARTMENT_KEYS[dept.name];
        if (!key) {
            return dept;
        }

        const namePath = `entities.departments.${key}.name`;
        const descPath = `entities.departments.${key}.description`;

        return {
            ...dept,
            name: te(namePath) ? t(namePath) : dept.name,
            description: te(descPath) ? t(descPath) : (dept.description || ''),
        };
    }

    function localizeDepartments(list) {
        return (list ?? []).map(localizeDepartment);
    }

    function statusLabel(status) {
        const normalized = String(status || 'active').toLowerCase();
        const key = `status.${normalized}`;
        return te(key) ? t(key) : status;
    }

    function formatDate(date) {
        if (!date) return '—';
        const loc = locale.value === 'ne' ? 'ne-NP' : 'en-US';
        return new Date(date).toLocaleDateString(loc, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    }

    return {
        localizeDepartment,
        localizeDepartments,
        statusLabel,
        formatDate,
        locale,
    };
}
