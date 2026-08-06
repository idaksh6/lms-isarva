export default function lmsQaBoard(config) {
    return {
        panelUrlTemplate: config.panelUrlTemplate,
        csrf: config.csrf,
        initialThreadId: config.initialThreadId,
        threadOpen: false,
        panelLoading: false,
        panelReady: false,
        activeThreadId: null,

        init() {
            if (this.initialThreadId) {
                this.openThread(this.initialThreadId, { skipHistory: true });
            }
        },

        panelUrl(id) {
            return this.panelUrlTemplate.replace('__ID__', String(id));
        },

        async openThread(id, { skipHistory = false } = {}) {
            if (! id) {
                return;
            }

            this.threadOpen = true;
            this.panelLoading = true;
            this.panelReady = false;
            this.activeThreadId = id;

            try {
                const response = await fetch(this.panelUrl(id), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (! response.ok) {
                    throw new Error('Failed to load thread');
                }

                const data = await response.json();
                const mount = this.$refs.panelMount;
                if (! mount) {
                    return;
                }

                // Tear down previous Alpine/thread pollers before replacing HTML.
                mount.querySelectorAll('[x-data]').forEach((el) => {
                    if (el._x_dataStack) {
                        el._x_dataStack.forEach((scope) => {
                            if (typeof scope.destroy === 'function') {
                                scope.destroy();
                            }
                        });
                    }
                });

                mount.innerHTML = data.html || '';
                this.panelReady = true;

                if (window.Alpine) {
                    window.Alpine.initTree(mount);
                }

                if (! skipHistory) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('thread', String(id));
                    window.history.replaceState({}, '', url.toString());
                }
            } catch (error) {
                console.error(error);
                const mount = this.$refs.panelMount;
                if (mount) {
                    mount.innerHTML = `
                        <div class="gchat-panel-placeholder">
                            <p class="gchat-empty-title">Could not open thread</p>
                            <p class="gchat-empty-desc">Please try again in a moment.</p>
                        </div>
                    `;
                }
                this.panelReady = false;
            } finally {
                this.panelLoading = false;
            }
        },

        closeThread() {
            const mount = this.$refs.panelMount;
            if (mount) {
                mount.querySelectorAll('[x-data]').forEach((el) => {
                    if (el._x_dataStack) {
                        el._x_dataStack.forEach((scope) => {
                            if (typeof scope.destroy === 'function') {
                                scope.destroy();
                            }
                        });
                    }
                });
                mount.innerHTML = `
                    <div class="gchat-panel-placeholder">
                        <p class="gchat-empty-title">Select a conversation</p>
                        <p class="gchat-empty-desc">Click a question or its replies to open the thread here.</p>
                    </div>
                `;
            }

            this.threadOpen = false;
            this.panelReady = false;
            this.panelLoading = false;
            this.activeThreadId = null;

            const url = new URL(window.location.href);
            url.searchParams.delete('thread');
            window.history.replaceState({}, '', url.toString());
        },
    };
}
