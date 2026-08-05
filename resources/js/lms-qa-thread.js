export default function lmsQaThread(config) {
    return {
        storeUrl: config.storeUrl,
        csrf: config.csrf,
        totalCount: config.totalCount ?? 0,
        replyOpenId: null,
        submitting: false,
        error: null,
        rootError: null,
        collapsed: {},

        isCollapsed(id, defaultCollapsed = false) {
            if (Object.prototype.hasOwnProperty.call(this.collapsed, id)) {
                return this.collapsed[id];
            }

            return defaultCollapsed;
        },

        expandThread(id) {
            this.collapsed[id] = false;
        },

        collapseThread(id) {
            this.collapsed[id] = true;
        },

        openReply(id) {
            this.replyOpenId = id;
            this.error = null;
            this.rootError = null;

            this.$nextTick(() => {
                const field = this.$refs[`replyBody${id}`]
                    || document.getElementById(`reply-body-${id}`);
                field?.focus();
            });
        },

        cancelReply() {
            this.replyOpenId = null;
            this.error = null;
        },

        async submitRoot(event) {
            const form = event.target;
            const body = form.body?.value?.trim() ?? '';

            if (! body) {
                this.rootError = 'Please write a response before submitting.';
                return;
            }

            this.rootError = null;
            await this.postAnswer({ body }, {
                onSuccess: (data) => {
                    this.appendNode(null, data.html);
                    form.reset();
                },
                onError: (message) => {
                    this.rootError = message;
                },
            });
        },

        async submitReply(parentId, event) {
            const form = event.target;
            const body = form.body?.value?.trim() ?? '';

            if (! body) {
                this.error = 'Please write a reply before submitting.';
                return;
            }

            this.error = null;
            await this.postAnswer({ body, parent_id: parentId }, {
                onSuccess: (data) => {
                    this.appendNode(parentId, data.html);
                    form.reset();
                    this.replyOpenId = null;
                },
                onError: (message) => {
                    this.error = message;
                },
            });
        },

        async postAnswer(payload, { onSuccess, onError }) {
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
                    const message = data.message
                        || Object.values(data.errors || {}).flat()[0]
                        || 'Unable to post right now. Please try again.';
                    onError(message);
                    return;
                }

                this.totalCount = data.total_answers ?? (this.totalCount + 1);
                onSuccess(data);
            } catch (error) {
                console.error(error);
                onError('Unable to post right now. Please try again.');
            } finally {
                this.submitting = false;
            }
        },

        appendNode(parentId, html) {
            const empty = this.$el.querySelector('[data-empty-answers]');
            if (empty) {
                empty.remove();
            }

            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const node = wrapper.firstElementChild;

            if (! node) {
                return;
            }

            if (! parentId) {
                this.$el.querySelector('[data-discussion-root]')?.appendChild(node);
                if (window.Alpine) {
                    window.Alpine.initTree(node);
                }
                return;
            }

            const parentNode = this.$el.querySelector(`[data-answer-id="${parentId}"]`);
            const children = parentNode?.querySelector(':scope > .corp-qa-thread-children');

            if (! children) {
                return;
            }

            let branch = children.querySelector(':scope > .corp-qa-thread-branch');

            if (! branch) {
                branch = document.createElement('div');
                branch.className = 'corp-qa-thread-branch';
                branch.dataset.branchFor = String(parentId);

                const list = document.createElement('div');
                list.className = 'corp-qa-thread-branch-list';
                branch.appendChild(list);
                children.appendChild(branch);
            }

            let list = branch.querySelector(':scope > .corp-qa-thread-branch-list');

            if (! list) {
                list = document.createElement('div');
                list.className = 'corp-qa-thread-branch-list';
                branch.appendChild(list);
            }

            this.expandThread(parentId);
            list.appendChild(node);

            if (window.Alpine) {
                window.Alpine.initTree(node);
            }

            const viewBtn = branch.querySelector(':scope > .corp-qa-view-replies:not(.corp-qa-view-replies--hide)');
            if (viewBtn) {
                const count = list.querySelectorAll(':scope > .corp-qa-thread-node').length;
                viewBtn.textContent = `View ${count} ${count === 1 ? 'reply' : 'replies'}`;
            }
        },
    };
}
