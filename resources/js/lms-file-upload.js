export default function lmsFileUpload(config = {}) {
    return {
        files: [],
        selectedFiles: [],
        dragging: false,
        maxFiles: config.maxFiles ?? 3,
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

        maxSizeBytes() {
            return this.maxSizeMb * 1024 * 1024;
        },

        fileKey(file) {
            return `${file.name}-${file.size}-${file.lastModified}`;
        },

        syncInput() {
            const input = this.$refs.input;
            const dt = new DataTransfer();

            this.selectedFiles.forEach((file) => dt.items.add(file));
            input.files = dt.files;

            this.files = this.selectedFiles.map((file) => ({
                name: file.name,
                size: this.formatSize(file.size),
            }));
        },

        addFiles(incoming) {
            const rejected = [];

            for (const file of incoming) {
                if (this.selectedFiles.length >= this.maxFiles) {
                    rejected.push(`${file.name} (maximum ${this.maxFiles} files)`);
                    continue;
                }

                if (file.size > this.maxSizeBytes()) {
                    rejected.push(`${file.name} (over ${this.maxSizeMb} MB)`);
                    continue;
                }

                if (this.selectedFiles.some((existing) => this.fileKey(existing) === this.fileKey(file))) {
                    continue;
                }

                this.selectedFiles.push(file);
            }

            if (rejected.length > 0) {
                alert(rejected.join('\n'));
            }

            this.syncInput();
        },

        onSelect(event) {
            const input = event.target;
            const incoming = Array.from(input.files);

            input.value = '';
            this.addFiles(incoming);
        },

        onDrop(event) {
            event.preventDefault();
            this.dragging = false;
            this.addFiles(Array.from(event.dataTransfer.files));
        },

        removeAt(index) {
            this.selectedFiles.splice(index, 1);
            this.syncInput();
        },

        clear() {
            this.selectedFiles = [];
            this.syncInput();
            this.$refs.input.value = '';
        },
    };
}
