const SIDEBAR_VAR_MAP = {
    bg: 'bg',
    bgSoft: 'bg-soft',
    border: 'border',
    text: 'text',
    textMuted: 'text-muted',
    hover: 'hover',
    activeBorder: 'active-border',
    activeBg: 'active-bg',
    accent: 'accent',
};

export default function lmsThemePicker(config) {
    return {
        open: false,
        saving: false,
        current: config.current,
        themes: config.themes,
        updateUrl: config.updateUrl,
        csrf: config.csrf,

        toggle() {
            this.open = ! this.open;
        },

        close() {
            this.open = false;
        },

        applyTheme(key) {
            const theme = this.themes[key];

            if (! theme) {
                return;
            }

            const root = document.documentElement;

            Object.entries(theme.colors).forEach(([shade, rgb]) => {
                root.style.setProperty(`--brand-${shade}`, rgb);
            });

            Object.entries(theme.sidebar).forEach(([name, rgb]) => {
                root.style.setProperty(`--sidebar-${SIDEBAR_VAR_MAP[name]}`, rgb);
            });
        },

        async pick(key) {
            if (this.saving || key === this.current) {
                this.close();
                return;
            }

            this.saving = true;

            try {
                const response = await fetch(this.updateUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ theme: key }),
                });

                if (! response.ok) {
                    throw new Error('Theme update failed');
                }

                this.applyTheme(key);
                this.current = key;
                this.close();
            } catch (error) {
                console.error(error);
            } finally {
                this.saving = false;
            }
        },
    };
}
