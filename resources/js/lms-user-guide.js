export default function lmsUserGuide(config = {}) {
    const accents = {
        student: { accent: '#059669', accentLight: '#ecfdf5', accentDark: '#047857', label: 'Student' },
        lecturer: { accent: '#4f46e5', accentLight: '#eef2ff', accentDark: '#4338ca', label: 'Lecturer' },
        admin: { accent: '#0f766e', accentLight: '#f0fdfa', accentDark: '#115e59', label: 'Administrator' },
    };

    return {
        tab: config.defaultTab ?? 'student',
        visibleTabs: config.visibleTabs ?? ['student'],

        accentFor(tab) {
            return accents[tab] ?? accents.student;
        },

        get theme() {
            return this.accentFor(this.tab);
        },

        isVisible(name) {
            return this.visibleTabs.includes(name);
        },

        setTab(name) {
            if (this.isVisible(name)) {
                this.tab = name;
            }
        },
    };
}
