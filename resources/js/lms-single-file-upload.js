export default function lmsSingleFileUpload(config = {}) {
    return {
        file: null,
        dragging: false,
        maxSizeMb: config.maxSizeMb ?? 20,

        formatSize(bytes) {
            if (bytes < 1024) {
                return `${bytes} B`;
            }
            if (bytes < 1024 * 1024) {
                return `${(bytes / 1024).toFixed(1)} KB`;
            }

            return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
        },

        syncFromInput(input) {
            const selected = input.files[0];
            if (! selected) {
                this.file = null;
                return;
            }

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

            const transfer = new DataTransfer();
            transfer.items.add(dropped);
            input.files = transfer.files;
            this.syncFromInput(input);
        },

        clear() {
            this.file = null;
            this.$refs.input.value = '';
        },
    };
}
