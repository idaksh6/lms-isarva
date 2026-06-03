export default function lmsFileUpload(config = {}) {
    return {
        files: [],
        dragging: false,
        maxFiles: config.maxFiles ?? 5,
        maxSizeMb: config.maxSizeMb ?? 10,

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
            this.files = Array.from(input.files).map((file) => ({
                name: file.name,
                size: this.formatSize(file.size),
            }));
        },

        onSelect(event) {
            const input = event.target;
            if (input.files.length > this.maxFiles) {
                input.value = '';
                this.files = [];
                alert(`You can upload up to ${this.maxFiles} files at once.`);
                return;
            }
            this.syncFromInput(input);
        },

        onDrop(event) {
            event.preventDefault();
            this.dragging = false;
            const input = this.$refs.input;
            const incoming = Array.from(event.dataTransfer.files);
            if (incoming.length > this.maxFiles) {
                alert(`You can upload up to ${this.maxFiles} files at once.`);
                return;
            }
            const dt = new DataTransfer();
            incoming.forEach((file) => dt.items.add(file));
            input.files = dt.files;
            this.syncFromInput(input);
        },

        removeAt(index) {
            const input = this.$refs.input;
            const dt = new DataTransfer();
            Array.from(input.files).forEach((file, i) => {
                if (i !== index) {
                    dt.items.add(file);
                }
            });
            input.files = dt.files;
            this.syncFromInput(input);
        },

        clear() {
            this.$refs.input.value = '';
            this.files = [];
        },
    };
}
