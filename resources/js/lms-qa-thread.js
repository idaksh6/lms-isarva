export default function lmsQaThread(config) {
    return {
        storeUrl: config.storeUrl,
        feedUrl: config.feedUrl,
        csrf: config.csrf,
        totalCount: config.totalCount ?? 0,
        latestId: config.latestId ?? 0,
        pollMs: config.pollMs ?? 4000,
        embedded: Boolean(config.embedded),
        replyTo: null,
        submitting: false,
        error: null,
        threadSearch: '',
        pollTimer: null,
        repliesOpen: true,
        isFullscreen: false,

        get hasSearchMatches() {
            const q = (this.threadSearch || '').trim().toLowerCase();
            if (! q) {
                return true;
            }

            return [...this.$el.querySelectorAll('[data-search-text]')].some((el) => {
                return ! el.classList.contains('is-search-hidden')
                    && (el.dataset.searchText || '').includes(q);
            });
        },

        init() {
            const ids = [...this.$el.querySelectorAll('[data-answer-id]')]
                .map((el) => Number(el.dataset.answerId))
                .filter((id) => Number.isFinite(id) && id > 0);

            if (ids.length) {
                this.latestId = Math.max(this.latestId || 0, ...ids);
            }

            this._onKeydown = (event) => {
                if (event.key === 'Escape' && this.isFullscreen) {
                    this.toggleFullscreen();
                }
            };
            window.addEventListener('keydown', this._onKeydown);

            // Native listeners — reliable even when the panel was injected via AJAX.
            this._onSubmit = (event) => {
                event.preventDefault();
                event.stopPropagation();
                this.submitMessage(event);
            };
            this._onClick = (event) => {
                const replyBtn = event.target.closest('[data-qa-reply]');
                if (replyBtn && this.$el.contains(replyBtn)) {
                    event.preventDefault();
                    this.setReplyTo({
                        id: Number(replyBtn.dataset.id),
                        name: replyBtn.dataset.name || '',
                        initials: replyBtn.dataset.initials || '',
                        body: replyBtn.dataset.body || '',
                    });
                    return;
                }

                const clearBtn = event.target.closest('[data-qa-clear-quote]');
                if (clearBtn && this.$el.contains(clearBtn)) {
                    event.preventDefault();
                    this.clearReplyTo();
                }
            };

            const form = this.$el.querySelector('.gchat-composer-form');
            form?.addEventListener('submit', this._onSubmit);
            this.$el.addEventListener('click', this._onClick);

            const composer = this.$el.querySelector('.gchat-composer-input');
            this._onComposerKey = (event) => {
                if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
                    event.preventDefault();
                    this.submitMessage(event);
                }
            };
            composer?.addEventListener('keydown', this._onComposerKey);

            this.$watch('threadSearch', () => this.applySearchFilter());
            this.$watch('repliesOpen', (open) => {
                const root = this.$el.querySelector('[data-discussion-root]');
                root?.classList.toggle('is-collapsed', ! open);
            });
            this.applySearchFilter();
            this.$el.querySelector('[data-discussion-root]')
                ?.classList.toggle('is-collapsed', ! this.repliesOpen);

            this.startPolling();
            this.$nextTick(() => this.scrollToLatest({ smooth: false }));
        },

        destroy() {
            this.stopPolling();
            if (this._onKeydown) {
                window.removeEventListener('keydown', this._onKeydown);
            }
            const form = this.$el.querySelector('.gchat-composer-form');
            form?.removeEventListener('submit', this._onSubmit);
            this.$el.removeEventListener('click', this._onClick);
            this.$el.querySelector('.gchat-composer-input')
                ?.removeEventListener('keydown', this._onComposerKey);
            if (this.isFullscreen) {
                this.$dispatch('qa-toggle-fullscreen', { open: false });
            }
        },

        applySearchFilter() {
            const q = (this.threadSearch || '').trim().toLowerCase();

            this.$el.querySelectorAll('[data-search-text]').forEach((el) => {
                const match = ! q || (el.dataset.searchText || '').includes(q);
                el.classList.toggle('is-search-hidden', ! match);
            });
        },

        toggleReplies() {
            this.repliesOpen = ! this.repliesOpen;
            if (this.repliesOpen) {
                this.$nextTick(() => this.scrollToLatest({ smooth: true }));
            }
        },

        toggleFullscreen() {
            this.isFullscreen = ! this.isFullscreen;
            this.$dispatch('qa-toggle-fullscreen', { open: this.isFullscreen });
            this.$nextTick(() => this.scrollToLatest({ smooth: false }));
        },

        exitAndClose() {
            if (this.isFullscreen) {
                this.isFullscreen = false;
                this.$dispatch('qa-toggle-fullscreen', { open: false });
            }
            this.$dispatch('qa-close-thread');
        },

        scrollToLatest({ smooth = true } = {}) {
            const scroller = this.$refs.threadScroll || this.$el.querySelector('[data-thread-scroll]');
            if (! scroller) {
                return;
            }

            requestAnimationFrame(() => {
                scroller.scrollTo({
                    top: scroller.scrollHeight,
                    behavior: smooth ? 'smooth' : 'auto',
                });
            });
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
                let added = false;

                items.forEach((item) => {
                    if (! item?.id || ! item?.html) {
                        return;
                    }
                    if (this.$el.querySelector(`[data-answer-id="${item.id}"]`)) {
                        return;
                    }
                    this.repliesOpen = true;
                    this.appendMessage(item.html, { scroll: false });
                    this.latestId = Math.max(this.latestId, item.id);
                    added = true;
                });

                if (typeof data.total_answers === 'number') {
                    this.totalCount = data.total_answers;
                }

                if (added) {
                    const scroller = this.$refs.threadScroll || this.$el.querySelector('[data-thread-scroll]');
                    const nearBottom = scroller
                        ? (scroller.scrollHeight - scroller.scrollTop - scroller.clientHeight) < 120
                        : true;
                    if (nearBottom) {
                        this.scrollToLatest({ smooth: true });
                    }
                }
            } catch (error) {
                // Silent poll failures.
            }
        },

        setReplyTo(target) {
            this.replyTo = target;
            this.error = null;
            this.repliesOpen = true;
            this.$nextTick(() => {
                const composer = this.$refs.composer || this.$el.querySelector('.gchat-composer-input');
                composer?.focus();
            });
        },

        clearReplyTo() {
            this.replyTo = null;
        },

        async submitMessage(event) {
            if (event?.preventDefault) {
                event.preventDefault();
            }

            const form = this.$el.querySelector('.gchat-composer-form');
            const field = form?.querySelector('[name="body"]')
                || this.$refs.composer
                || this.$el.querySelector('.gchat-composer-input');
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
            this.repliesOpen = true;

            try {
                const response = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
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
                    this.latestId = Math.max(this.latestId, Number(data.answer.id));
                }

                if (data.html) {
                    this.appendMessage(data.html, { scroll: true });
                } else if (data.answer?.id) {
                    // Fallback: pull the new message from the feed endpoint.
                    await this.pullSingle(data.answer.id);
                } else {
                    this.error = 'Reply saved, but could not show it yet. Try refreshing.';
                    return;
                }

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

        async pullSingle(answerId) {
            try {
                const url = new URL(this.feedUrl, window.location.origin);
                url.searchParams.set('after', String(Math.max(0, Number(answerId) - 1)));
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
                const match = (data.answers || []).find((item) => Number(item.id) === Number(answerId));
                if (match?.html) {
                    this.appendMessage(match.html, { scroll: true });
                }
            } catch (error) {
                console.error(error);
            }
        },

        appendMessage(html, { scroll = true } = {}) {
            const empty = this.$el.querySelector('[data-empty-answers]');
            empty?.remove();

            this.repliesOpen = true;

            const root = this.$el.querySelector('[data-discussion-root]');
            if (! root || ! html) {
                return;
            }

            root.classList.remove('is-collapsed');
            root.style.display = '';

            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const node = wrapper.firstElementChild;
            if (! node) {
                return;
            }

            const answerId = node.getAttribute('data-answer-id');
            if (answerId && this.$el.querySelector(`[data-answer-id="${answerId}"]`)) {
                if (scroll) {
                    this.scrollToLatest({ smooth: true });
                }
                return;
            }

            root.appendChild(node);
            this.applySearchFilter();

            if (scroll) {
                this.scrollToLatest({ smooth: true });
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
                    credentials: 'same-origin',
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
