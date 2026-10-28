/**
 * The application form's documents, sent straight to storage as each is chosen.
 *
 * Vercel turns away request bodies over 4.5 MB, which two or three scanned
 * documents easily pass, so the files never ride along with the form. Each one
 * is checked the moment it is chosen (type and the 5 MB limit, before any data
 * is spent), then PUT to a presigned storage link with real byte progress. The
 * form submits only the tokens the server handed out, and the server checks
 * every stored file again before accepting it.
 *
 * Without JavaScript the file inputs keep their names and the form uploads as
 * an ordinary multipart post; a row that fails to upload directly falls back
 * the same way.
 */

const EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf'];

/**
 * The server turned an upload down and said why, in words meant for the
 * student. Anything else thrown while asking for a link (no connection, a
 * body that is not JSON) is the network's, and the student sees the plain
 * "didn't finish" line rather than the browser's own wording.
 */
class UploadRefused extends Error {}

export default ({ documentIds, linkUrl, maxBytes, labels }) => {
    // Kept outside Alpine's reactive state: a proxied File cannot be sent.
    const files = new Map();
    const requests = new Map();

    const emptyRow = () => ({
        state: 'idle',
        name: '',
        size: 0,
        loaded: 0,
        preview: null,
        message: '',
        token: '',
        attempt: 0,
    });

    const megabytes = (bytes) => (bytes < 1024 * 1024
        ? `${Math.max(1, Math.round(bytes / 1024))} KB`
        : `${(bytes / (1024 * 1024)).toFixed(1)} MB`);

    return {
        rows: Object.fromEntries(documentIds.map((id) => [id, emptyRow()])),
        sending: false,
        finishing: false,

        get uploading() {
            return Object.values(this.rows).some((row) => row.state === 'uploading');
        },

        fraction(id) {
            const row = this.rows[id];

            return row.size ? Math.min(1, row.loaded / row.size) : 0;
        },

        progressText(id) {
            const row = this.rows[id];

            if (row.state === 'done') {
                return labels.uploaded.replace(':size', megabytes(row.size));
            }

            return labels.progress
                .replace(':loaded', megabytes(row.loaded))
                .replace(':size', megabytes(row.size));
        },

        choose(input, id) {
            this.clear(id);

            const file = input.files[0];
            const row = this.rows[id];

            if (! file) {
                return;
            }

            const extension = file.name.split('.').pop().toLowerCase();
            let problem = '';

            if (! EXTENSIONS.includes(extension)) {
                problem = labels.wrongType;
            } else if (file.size === 0) {
                problem = labels.empty;
            } else if (file.size > maxBytes) {
                problem = labels.tooLarge.replace(':size', megabytes(file.size));
            }

            if (problem) {
                row.state = 'invalid';
                row.message = problem;
                // Blocks submitting; the row shows the reason, so no browser bubble on top of it.
                input.setCustomValidity(problem);

                return;
            }

            input.setCustomValidity('');
            row.name = file.name;
            row.size = file.size;
            row.preview = file.type.startsWith('image/') ? URL.createObjectURL(file) : null;
            files.set(id, file);

            this.send(id);
        },

        retry(id) {
            if (files.has(id)) {
                this.send(id);
            }
        },

        async send(id) {
            const row = this.rows[id];
            const file = files.get(id);
            const attempt = ++row.attempt;
            const current = () => row.attempt === attempt;

            row.state = 'uploading';
            row.loaded = 0;
            row.message = '';

            let link;

            try {
                const response = await fetch(linkUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ required_document_id: id, name: file.name, size: file.size }),
                });

                if (! response.ok) {
                    throw new UploadRefused(await this.reason(response));
                }

                link = await response.json();
            } catch (error) {
                if (current()) {
                    this.fail(id, error instanceof UploadRefused ? error.message : labels.failed);
                }

                return;
            }

            if (! current()) {
                return;
            }

            const request = new XMLHttpRequest();
            requests.set(id, request);
            request.open('PUT', link.url);

            for (const [name, value] of Object.entries(link.headers ?? {})) {
                request.setRequestHeader(name, Array.isArray(value) ? value[0] : value);
            }

            if (file.type) {
                request.setRequestHeader('Content-Type', file.type);
            }

            request.upload.addEventListener('progress', (event) => {
                if (current() && event.lengthComputable) {
                    row.loaded = event.loaded;
                }
            });

            request.addEventListener('load', () => {
                if (! current()) {
                    return;
                }

                if (request.status >= 200 && request.status < 300) {
                    row.loaded = row.size;
                    row.state = 'done';
                    row.token = link.token;
                    this.submitIfWaiting();
                } else {
                    this.fail(id, labels.failed);
                }
            });

            request.addEventListener('error', () => current() && this.fail(id, labels.failed));
            request.send(file);
        },

        async reason(response) {
            if (response.status === 403) {
                return labels.forbidden;
            }

            if (response.status === 419) {
                return labels.expired;
            }

            if (response.status === 429) {
                return labels.busy;
            }

            if (response.status === 422) {
                const body = await response.json().catch(() => ({}));

                return Object.values(body.errors ?? {}).flat()[0] ?? labels.failed;
            }

            return labels.failed;
        },

        fail(id, message) {
            const row = this.rows[id];

            row.state = 'failed';
            row.message = message;
            row.token = '';

            // Stop a waiting submit, so the student sees what failed before anything is sent.
            this.finishing = false;
        },

        clear(id) {
            const row = this.rows[id];

            requests.get(id)?.abort();
            requests.delete(id);
            files.delete(id);

            if (row.preview) {
                URL.revokeObjectURL(row.preview);
            }

            Object.assign(row, { ...emptyRow(), attempt: row.attempt + 1 });
        },

        // A press of Submit while files are still on their way waits for them.
        submit(event) {
            if (this.uploading) {
                event.preventDefault();
                this.finishing = true;

                return;
            }

            this.finishing = false;
            this.sending = true;
        },

        submitIfWaiting() {
            if (this.finishing && ! this.uploading) {
                this.finishing = false;

                // After Alpine has applied the last token and taken the file's name off,
                // or the form would still post the file instead of its token.
                this.$nextTick(() => this.$root.requestSubmit());
            }
        },

        restore() {
            this.sending = false;
            this.finishing = false;
        },
    };
};
