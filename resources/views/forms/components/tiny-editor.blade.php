<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @php
        $statePath = $getStatePath();
        $editorId = 'tiny-editor-' . str_replace(['.', '[', ']'], '-', $statePath) . '-' . $getId();
    @endphp

    <div
        x-data="{
            state: $wire.entangle('{{ $statePath }}'),
            editor: null,
            editorId: '{{ $editorId }}',

            loadTinyMceScript() {
                if (typeof tinymce !== 'undefined') {
                    return Promise.resolve();
                }

                if (window.__tinyMceLoadingPromise) {
                    return window.__tinyMceLoadingPromise;
                }

                window.__tinyMceLoadingPromise = new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.6.1/tinymce.min.js';
                    script.referrerPolicy = 'origin';
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });

                return window.__tinyMceLoadingPromise;
            },

            destroyEditor() {
                if (!this.editor) {
                    return;
                }

                this.editor.destroy();
                this.editor = null;
            },

            initEditor() {
                this.loadTinyMceScript().then(() => {
                    if (this.editor) {
                        return;
                    }

                    this.destroyEditor();

                    tinymce.init({
                        target: this.$refs.editor,
                        base_url: 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.6.1',
                        suffix: '.min',
                        license_key: 'gpl',
                        menubar: 'file edit view insert format tools table',
                        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align numlist bullist outdent indent | removeformat',
                        plugins: 'lists link table code wordcount autoresize charmap preview searchreplace visualblocks fullscreen help',
                        branding: true,
                        promotion: false,
                        statusbar: true,
                        resize: true,
                        min_height: {{ $getMinHeight() }},
                        directionality: '{{ $isRtl() ? 'rtl' : 'ltr' }}',
                        content_style: 'body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; font-size: 14px; }',
                        setup: (editor) => {
                            this.editor = editor;

                            editor.on('init', () => {
                                editor.setContent(this.state || '');
                            });

                            editor.on('change keyup setcontent', () => {
                                this.state = editor.getContent();
                            });
                        },
                    });
                }).catch(() => {
                    // Keep textarea fallback if script fails to load.
                });
            }
        }"
        x-init="$nextTick(() => initEditor())"
        x-on:livewire:navigating.window="destroyEditor()"
    >
        <textarea
            id="{{ $editorId }}"
            x-ref="editor"
            wire:model.defer="{{ $statePath }}"
        ></textarea>
    </div>
</x-dynamic-component>
