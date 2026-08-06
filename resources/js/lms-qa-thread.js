export default function lmsQaThread(config) {
    return {
        storeUrl: config.storeUrl,
        feedUrl: config.feedUrl,
        csrf: config.csrf,
        totalCount: config.totalCount ?? 0,
        latestId: config.latestId ?? 0,
        pollMs: config.pollMs ?? 4000,
        replyTo: null,
        submitting: false,
        error: null,
        threadSearch: '',
        pollTimer: null,

        get hasSearchMatches() {
            const q = (this.threadSearch || '').trim().toLowerCase();
            if (! q) {
                return true;
            }

            return [...this.$el.querySelectorAll('[data-search-text]')].some((el) => {
                return (el.dataset.searchText || '').includes(q);
            });
        },

        init() {
            const ids = [...this.$el.querySelectorAll('[data-answer-id]')]
                .map((el) => Number(el.dataset.answerId))
                .filter((id) => Number.isFinite(id) && id > 0);

            if (ids.length) {
                this.latestId = Math.max(this.latestId || 0, ...ids);
            }

            this.startPolling();
        },

        destroy() {
            this.stopPolling();
        },

        matchesSearch(el) {
            const q = (this.threadSearch || '').trim().toLowerCase();
            if (! q) {
                return true;
            }

            return (el?.dataset?.searchText || '').includes(q);
        },

        startPolling() {
            this.stopPolling();
            if (! this.feedUrl) {
                return;
            }

            this.pollTimer = setInterval(() => this.pollFeed(), this.pollMs);
        },

        stopPolling() {
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        },

        async pollFeed() {
            if (this.submitting || document.hidden) {
                return;
            }

            try {
                const url = new URL(this.feedUrl, window.location.origin);
                url.searchParams.set('after', String(this.latestId || 0));

                const response = await fetch(url.toString(), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (! response.ok) {
                    return;
                }

                const data = await response.json();
                const items = data.answers || [];

                items.forEach((item) => {
                    if (! item?.id || ! item?.html) {
                        return;
                    }
                    if (this.$el.querySelector(`[data-answer-id="${item.id}"]`)) {
                        return;
                    }
                    this.appendMessage(item.html, { scroll: false });
                    this.latestId = Math.max(this.latestId, item.id);
                });

                if (typeof data.total_answers === 'number') {
                    this.totalCount = data.total_answers;
                }
            } catch (error) {
                // Silent poll failures — network blips should not interrupt the thread.
            }
        },

        setReplyTo(target) {
            this.replyTo = target;
            this.error = null;
            this.$nextTick(() => this.$refs.composer?.focus());
        },

        clearReplyTo() {
            this.replyTo = null;
        },

        async submitMessage(event) {
            const form = event.target?.closest?.('form') || this.$el.querySelector('.gchat-composer-form');
            const field = form?.querySelector('[name="body"]') || this.$refs.composer;
            const body = (field?.value || '').trim();

            if (! body) {
                this.error = 'Write a reply before sending.';
                return;
            }

            this.error = null;

            const payload = { body };
            if (this.replyTo?.id) {
                payload.parent_id = this.replyTo.id;
            }

            if (this.submitting) {
                return;
            }

            this.submitting = true;

            try {
                const response = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json().catch(() => ({}));

                if (! response.ok) {
                    this.error = data.message
                        || Object.values(data.errors || {}).flat()[0]
                        || 'Unable to send right now. Please try again.';
                    return;
                }

                this.totalCount = data.total_answers ?? (this.totalCount + 1);
                if (data.answer?.id) {
                    this.latestId = Math.max(this.latestId, data.answer.id);
                }
                this.appendMessage(data.html, { scroll: true });
                if (field) {
                    field.value = '';
                }
                this.replyTo = null;
            } catch (error) {
                console.error(error);
                this.error = 'Unable to send right now. Please try again.';
            } finally {
                this.submitting = false;
            }
        },

        appendMessage(html, { scroll = true } = {}) {
            const empty = this.$el.querySelector('[data-empty-answers]');
            empty?.remove();

            const root = this.$el.querySelector('[data-discussion-root]');
            if (! root || ! html) {
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const node = wrapper.firstElementChild;
            if (! node) {
                return;
            }

            root.appendChild(node);

            if (window.Alpine) {
                window.Alpine.initTree(node);
            }

            if (scroll) {
                this.$nextTick(() => {
                    node.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                });
            }
        },

        async removeMessage(id, event) {
            if (! confirm('Remove this reply?')) {
                return;
            }

            const form = event.target.closest('form');
            if (! form) {
                return;
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new URLSearchParams({
                        _token: this.csrf,
                        _method: 'DELETE',
                    }),
                });

                if (! response.ok) {
                    throw new Error('Delete failed');
                }

                const data = await response.json().catch(() => ({}));
                this.$el.querySelector(`[data-answer-id="${id}"]`)?.remove();
                this.totalCount = data.total_answers ?? Math.max(0, this.totalCount - 1);

                if (this.totalCount === 0) {
                    const root = this.$el.querySelector('[data-discussion-root]');
                    if (root && ! root.querySelector('.gchat-msg')) {
                        root.innerHTML = `
                            <div class="gchat-empty" data-empty-answers>
                                <p class="gchat-empty-title">No replies yet</p>
                                <p class="gchat-empty-desc">Start the conversation below — replies appear here instantly.</p>
                            </div>
                        `;
                    }
                }
            } catch (error) {
                console.error(error);
                this.error = 'Unable to remove that reply.';
            }
        },
    };
}
