export default function lmsSingleFileUpload(config = {}) {
    return {
        file: null,
        dragging: false,
        error: null,
        maxSizeMb: config.maxSizeMb ?? 3,

        formatSize(bytes) {
            if (bytes < 1024) {
                return `${bytes} B`;
            }
            if (bytes < 1024 * 1024) {
                return `${(bytes / 1024).toFixed(1)} KB`;
            }

            return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
        },

        isTooLarge(file) {
            return file.size > this.maxSizeMb * 1024 * 1024;
        },

        rejectFile(input) {
            this.error = `This file is too large. Maximum size is ${this.maxSizeMb} MB.`;
            this.file = null;
            input.value = '';
        },

        syncFromInput(input) {
            const selected = input.files[0];
            if (! selected) {
                this.file = null;
                this.error = null;
                return;
            }

            if (this.isTooLarge(selected)) {
                this.rejectFile(input);
                return;
            }

            this.error = null;
            this.file = {
                name: selected.name,
                size: this.formatSize(selected.size),
            };
        },

        onSelect(event) {
            this.syncFromInput(event.target);
        },

        onDrop(event) {
            event.preventDefault();
            this.dragging = false;
            const input = this.$refs.input;
            const dropped = event.dataTransfer.files[0];
            if (! dropped) {
                return;
            }

            if (this.isTooLarge(dropped)) {
                this.rejectFile(input);
                return;
            }

            const transfer = new DataTransfer();
            transfer.items.add(dropped);
            input.files = transfer.files;
            this.syncFromInput(input);
        },

        clear() {
            this.file = null;
            this.error = null;
            this.$refs.input.value = '';
        },
    };
}
