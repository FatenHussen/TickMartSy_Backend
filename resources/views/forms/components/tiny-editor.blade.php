<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @php
        $statePath = $getStatePath();
        $editorId = 'tiny-editor-' . str_replace(['.', '[', ']'], '-', $statePath) . '-' . $getId();
    @endphp

    <div
        x-data="{
            state: $wire.$entangle('{{ $statePath }}').live,
            editor: null,
            initEditor() {
                const setupEditor = () => {
                    if (this.editor) {
                        return;
                    }

                    tinymce.init({
                        target: this.$refs.editor,
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
                };

                if (typeof tinymce === 'undefined') {
                    const script = document.createElement('script');
                    script.src = 'https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js';
                    script.referrerPolicy = 'origin';
                    script.onload = () => setupEditor();
                    document.head.appendChild(script);
                    return;
                }

                setupEditor();
            }
        }"
        x-init="initEditor()"
        x-on:destroy.window="if (editor) { editor.destroy(); editor = null; }"
    >
        <textarea
            id="{{ $editorId }}"
            x-ref="editor"
            wire:model.defer="{{ $statePath }}"
        ></textarea>
    </div>
</x-dynamic-component>
