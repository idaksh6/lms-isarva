export default function lmsQaThread(config) {
    return {
        storeUrl: config.storeUrl,
        csrf: config.csrf,
        totalCount: config.totalCount ?? 0,
        replyTo: null,
        submitting: false,
        error: null,

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
                this.appendMessage(data.html);
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

        appendMessage(html) {
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

            this.$nextTick(() => {
                node.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
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
