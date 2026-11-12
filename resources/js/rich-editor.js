import Alpine from 'alpinejs';

const ALLOWED_TAGS = new Set([
    'p',
    'br',
    'div',
    'strong',
    'b',
    'em',
    'i',
    'u',
    's',
    'del',
    'h2',
    'h3',
    'h4',
    'ul',
    'ol',
    'li',
    'blockquote',
    'code',
    'pre',
    'hr',
    'a',
]);

const TAG_ALIASES = {
    h1: 'h2',
    h5: 'h4',
    h6: 'h4',
};

const DROP_ELEMENTS = new Set([
    'script',
    'style',
    'iframe',
    'object',
    'embed',
    'applet',
    'svg',
    'math',
    'form',
    'input',
    'button',
    'textarea',
    'select',
    'option',
    'meta',
    'link',
    'base',
    'template',
    'noscript',
    'img',
]);

function escapeHtml(text) {
    return String(text)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function hasHtmlTags(value) {
    return /<\/?[a-zA-Z][a-zA-Z0-9-]*(?:\s[^>]*)?>/.test(value);
}

function normalizeUrl(raw) {
    const clean = String(raw || '').replace(/[\r\n\t\0]/g, '').trim();
    if (!clean) {
        return null;
    }

    if (clean.startsWith('//')) {
        return null;
    }

    if (clean.startsWith('/') || clean.startsWith('#')) {
        return clean;
    }

    if (/^(mailto|tel):.+$/i.test(clean)) {
        return clean;
    }

    if (/^https?:\/\/[^\s/$.?#].[^\s]*$/i.test(clean)) {
        return clean;
    }

    if (!/^[a-zA-Z][a-zA-Z0-9+.-]*:/.test(clean) && /^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(?:\/.*)?$/.test(clean)) {
        return `https://${clean}`;
    }

    return null;
}

function isSafeExistingHref(raw) {
    const clean = String(raw || '').replace(/[\r\n\t\0]/g, '').trim();
    if (!clean || clean.startsWith('//')) {
        return false;
    }

    if (clean.startsWith('/') || clean.startsWith('#')) {
        return true;
    }

    return /^(https?:\/\/|mailto:|tel:)/i.test(clean);
}

function sanitizeDomNode(parent, doc) {
    for (const child of Array.from(parent.childNodes)) {
        if (child.nodeType === Node.COMMENT_NODE) {
            child.remove();
            continue;
        }

        if (child.nodeType !== Node.ELEMENT_NODE) {
            continue;
        }

        const tag = child.tagName.toLowerCase();

        if (DROP_ELEMENTS.has(tag)) {
            child.remove();
            continue;
        }

        sanitizeDomNode(child, doc);

        if (TAG_ALIASES[tag]) {
            const mapped = doc.createElement(TAG_ALIASES[tag]);
            while (child.firstChild) {
                mapped.appendChild(child.firstChild);
            }
            parent.replaceChild(mapped, child);
            continue;
        }

        if (!ALLOWED_TAGS.has(tag)) {
            while (child.firstChild) {
                parent.insertBefore(child.firstChild, child);
            }
            child.remove();
            continue;
        }

        if (tag === 'a') {
            const href = (child.getAttribute('href') || '').trim();
            const target = (child.getAttribute('target') || '').trim();
            const title = (child.getAttribute('title') || '').trim();

            for (const attr of Array.from(child.attributes)) {
                child.removeAttribute(attr.name);
            }

            if (!isSafeExistingHref(href)) {
                while (child.firstChild) {
                    parent.insertBefore(child.firstChild, child);
                }
                child.remove();
                continue;
            }

            child.setAttribute('href', href);
            if (title) {
                child.setAttribute('title', title);
            }
            if (target === '_blank') {
                child.setAttribute('target', '_blank');
                child.setAttribute('rel', 'noopener noreferrer');
            }
            continue;
        }

        for (const attr of Array.from(child.attributes)) {
            child.removeAttribute(attr.name);
        }
    }
}

function sanitizeHtml(rawHtml) {
    const str = String(rawHtml ?? '').trim();
    if (!str) {
        return '';
    }

    const parser = new DOMParser();
    const doc = parser.parseFromString(`<div id="__rte_clean">${str}</div>`, 'text/html');
    const root = doc.getElementById('__rte_clean');

    if (!root) {
        return '';
    }

    sanitizeDomNode(root, doc);

    // Convert top-level bare <div> wrappers into semantic <p> paragraphs.
    for (const node of Array.from(root.childNodes)) {
        if (node.nodeType === Node.ELEMENT_NODE && node.tagName.toLowerCase() === 'div') {
            const p = doc.createElement('p');
            while (node.firstChild) {
                p.appendChild(node.firstChild);
            }
            root.replaceChild(p, node);
        }
    }

    const plainCheck = (root.textContent || '').replace(/\u00a0/g, ' ').trim();
    if (!plainCheck) {
        return '';
    }

    return root.innerHTML.trim();
}

function plainTextToHtml(text) {
    const trimmed = String(text ?? '').trim();
    if (!trimmed) {
        return '';
    }

    return trimmed
        .split(/\r?\n\r?\n+/)
        .map((block) => `<p>${escapeHtml(block).replaceAll(/\r?\n/g, '<br>')}</p>`)
        .join('');
}

function formatCodePreview(html) {
    if (!html) {
        return '';
    }

    return html
        .replace(/></g, '>\n<')
        .replace(/\n{3,}/g, '\n\n')
        .trim();
}

function createRichTextEditor(config = {}) {
    return {
        value: config.initialValue ?? '',
        disabled: Boolean(config.disabled),
        focused: false,
        codeMode: false,
        codeValue: '',
        showLinkBar: false,
        linkUrl: '',
        linkNewTab: true,
        linkError: '',
        hasActiveLink: false,
        blockType: 'p',
        isEmpty: true,
        wordCount: 0,
        charCount: 0,
        active: {
            bold: false,
            italic: false,
            underline: false,
            strikeThrough: false,
            insertUnorderedList: false,
            insertOrderedList: false,
        },
        savedRange: null,
        activeAnchor: null,
        _lastSyncedValue: null,
        _selectionHandler: null,

        init() {
            try {
                document.execCommand('defaultParagraphSeparator', false, 'p');
            } catch {
                // Ignore unsupported command in non-DOM test environments.
            }

            this.renderExternalValue(this.value);

            this.$watch('value', (next) => {
                const normalizedNext = next ?? '';
                if (normalizedNext === this._lastSyncedValue) {
                    return;
                }

                if (!this.focused || normalizedNext === '') {
                    this.renderExternalValue(normalizedNext);
                }
            });

            this._selectionHandler = () => {
                if (this.focused && !this.codeMode) {
                    this.refreshActiveStates();
                }
            };
            document.addEventListener('selectionchange', this._selectionHandler);
        },

        destroy() {
            if (this._selectionHandler) {
                document.removeEventListener('selectionchange', this._selectionHandler);
            }
        },

        renderExternalValue(raw) {
            const editor = this.$refs.editor;
            const stringValue = String(raw ?? '');

            if (editor) {
                if (!stringValue.trim()) {
                    editor.innerHTML = '';
                } else if (hasHtmlTags(stringValue)) {
                    editor.innerHTML = sanitizeHtml(stringValue);
                } else {
                    editor.innerHTML = plainTextToHtml(stringValue);
                }
            }

            this.codeValue = formatCodePreview(hasHtmlTags(stringValue) ? sanitizeHtml(stringValue) : stringValue);
            this._lastSyncedValue = stringValue;
            this.updateStats();
            this.refreshActiveStates();
        },

        onEditorFocus() {
            this.focused = true;
            this.refreshActiveStates();
        },

        onEditorBlur() {
            this.focused = false;
            if (!this.codeMode) {
                this.syncFromEditor();
            }
        },

        onEditorInput() {
            if (!this.codeMode) {
                this.syncFromEditor();
                this.refreshActiveStates();
            }
        },

        onCodeInput() {
            const clean = sanitizeHtml(this.codeValue);
            this.commitValue(clean);
            if (this.$refs.editor) {
                this.$refs.editor.innerHTML = clean;
            }
            this.updateStats();
        },

        handlePaste(event) {
            if (this.disabled) {
                return;
            }

            const clipboard = event.clipboardData || window.clipboardData;
            if (!clipboard) {
                return;
            }

            event.preventDefault();

            const pastedHtml = clipboard.getData('text/html');
            const pastedText = clipboard.getData('text/plain');

            let fragmentHtml = '';
            if (pastedHtml && pastedHtml.trim()) {
                fragmentHtml = sanitizeHtml(pastedHtml);
            } else if (pastedText) {
                const lines = pastedText.split(/\r?\n/);
                fragmentHtml = lines.map((line) => escapeHtml(line)).join('<br>');
            }

            if (!fragmentHtml) {
                return;
            }

            this.insertHtmlAtSelection(fragmentHtml);
            this.syncFromEditor();
            this.refreshActiveStates();
        },

        insertHtmlAtSelection(html) {
            const editor = this.$refs.editor;
            if (!editor) {
                return;
            }

            editor.focus();
            const selection = window.getSelection();
            if (!selection || selection.rangeCount === 0) {
                editor.insertAdjacentHTML('beforeend', html);
                return;
            }

            const range = selection.getRangeAt(0);
            if (!editor.contains(range.commonAncestorContainer)) {
                editor.insertAdjacentHTML('beforeend', html);
                return;
            }

            range.deleteContents();
            const template = document.createElement('template');
            template.innerHTML = html;
            const frag = template.content;
            const lastNode = frag.lastChild;
            range.insertNode(frag);

            if (lastNode) {
                const after = document.createRange();
                after.setStartAfter(lastNode);
                after.collapse(true);
                selection.removeAllRanges();
                selection.addRange(after);
            }
        },

        exec(command, value = null) {
            if (this.disabled || this.codeMode || !this.$refs.editor) {
                return;
            }

            this.$refs.editor.focus();
            try {
                document.execCommand(command, false, value);
            } catch {
                // Ignore unsupported commands gracefully.
            }
            this.syncFromEditor();
            this.refreshActiveStates();
        },

        applyBlockFormat(tag) {
            if (this.disabled || this.codeMode || !this.$refs.editor) {
                return;
            }

            this.$refs.editor.focus();
            const formattedTag = `<${tag}>`;
            try {
                document.execCommand('formatBlock', false, formattedTag);
            } catch {
                document.execCommand('formatBlock', false, tag);
            }
            this.blockType = tag;
            this.syncFromEditor();
            this.refreshActiveStates();
        },

        openLinkBar() {
            if (this.disabled || this.codeMode || !this.$refs.editor) {
                return;
            }

            this.saveSelection();
            this.linkError = '';

            const anchor = this.findSelectedAnchor();
            this.activeAnchor = anchor;
            this.hasActiveLink = Boolean(anchor);
            this.linkUrl = anchor ? (anchor.getAttribute('href') || '') : '';
            this.linkNewTab = anchor ? anchor.getAttribute('target') === '_blank' : true;
            this.showLinkBar = true;

            this.$nextTick(() => {
                this.$refs.linkInput?.focus();
                this.$refs.linkInput?.select();
            });
        },

        closeLinkBar() {
            this.showLinkBar = false;
            this.linkError = '';
            this.activeAnchor = null;
            this.$nextTick(() => {
                this.$refs.editor?.focus();
            });
        },

        applyLink() {
            const safeUrl = normalizeUrl(this.linkUrl);
            if (!safeUrl) {
                this.linkError = 'Enter a valid https:// URL, relative /path, #anchor, mailto:, or tel: link.';
                return;
            }

            const editor = this.$refs.editor;
            if (!editor) {
                return;
            }

            this.restoreSelection();
            editor.focus();

            const targetAnchor = this.activeAnchor && editor.contains(this.activeAnchor)
                ? this.activeAnchor
                : this.findSelectedAnchor();

            if (targetAnchor) {
                targetAnchor.setAttribute('href', safeUrl);
                if (this.linkNewTab) {
                    targetAnchor.setAttribute('target', '_blank');
                    targetAnchor.setAttribute('rel', 'noopener noreferrer');
                } else {
                    targetAnchor.removeAttribute('target');
                    targetAnchor.removeAttribute('rel');
                }
            } else {
                const selection = window.getSelection();
                const hasSelectionText = selection && !selection.isCollapsed && selection.toString().trim().length > 0;

                if (hasSelectionText) {
                    const marker = `__rte_link_${Date.now()}`;
                    document.execCommand('createLink', false, marker);
                    for (const created of editor.querySelectorAll(`a[href="${marker}"]`)) {
                        created.setAttribute('href', safeUrl);
                        if (this.linkNewTab) {
                            created.setAttribute('target', '_blank');
                            created.setAttribute('rel', 'noopener noreferrer');
                        } else {
                            created.removeAttribute('target');
                            created.removeAttribute('rel');
                        }
                    }
                } else {
                    const attrs = this.linkNewTab
                        ? ` target="_blank" rel="noopener noreferrer"`
                        : '';
                    this.insertHtmlAtSelection(`<a href="${escapeHtml(safeUrl)}"${attrs}>${escapeHtml(safeUrl)}</a>`);
                }
            }

            this.showLinkBar = false;
            this.linkError = '';
            this.activeAnchor = null;
            this.syncFromEditor();
            this.refreshActiveStates();
        },

        removeLink() {
            const editor = this.$refs.editor;
            if (!editor) {
                return;
            }

            this.restoreSelection();
            editor.focus();

            const anchor = this.activeAnchor && editor.contains(this.activeAnchor)
                ? this.activeAnchor
                : this.findSelectedAnchor();

            if (anchor) {
                const parent = anchor.parentNode;
                while (anchor.firstChild) {
                    parent.insertBefore(anchor.firstChild, anchor);
                }
                anchor.remove();
            } else {
                document.execCommand('unlink', false, null);
            }

            this.showLinkBar = false;
            this.linkError = '';
            this.activeAnchor = null;
            this.syncFromEditor();
            this.refreshActiveStates();
        },

        toggleCodeMode() {
            if (this.disabled) {
                return;
            }

            this.showLinkBar = false;

            if (!this.codeMode) {
                this.syncFromEditor();
                this.codeValue = formatCodePreview(this._lastSyncedValue || '');
                this.codeMode = true;
                this.$nextTick(() => {
                    this.$refs.codeInput?.focus();
                });
            } else {
                const clean = sanitizeHtml(this.codeValue);
                if (this.$refs.editor) {
                    this.$refs.editor.innerHTML = clean;
                }
                this.commitValue(clean);
                this.updateStats();
                this.codeMode = false;
                this.$nextTick(() => {
                    this.$refs.editor?.focus();
                    this.refreshActiveStates();
                });
            }
        },

        saveSelection() {
            const selection = window.getSelection();
            const editor = this.$refs.editor;
            if (selection && selection.rangeCount > 0 && editor && editor.contains(selection.getRangeAt(0).commonAncestorContainer)) {
                this.savedRange = selection.getRangeAt(0).cloneRange();
            } else {
                this.savedRange = null;
            }
        },

        restoreSelection() {
            if (!this.savedRange) {
                return;
            }

            const selection = window.getSelection();
            if (selection) {
                selection.removeAllRanges();
                selection.addRange(this.savedRange);
            }
        },

        findSelectedAnchor() {
            const editor = this.$refs.editor;
            const selection = window.getSelection();
            if (!editor || !selection || selection.rangeCount === 0) {
                return null;
            }

            let node = selection.anchorNode;
            while (node && node !== editor) {
                if (node.nodeType === Node.ELEMENT_NODE && node.tagName.toLowerCase() === 'a') {
                    return node;
                }
                node = node.parentNode;
            }

            return null;
        },

        syncFromEditor() {
            const editor = this.$refs.editor;
            if (!editor) {
                return;
            }

            const cleaned = sanitizeHtml(editor.innerHTML);
            this.commitValue(cleaned);
            this.codeValue = formatCodePreview(cleaned);
            this.updateStats();
        },

        commitValue(nextValue) {
            const normalized = nextValue ?? '';
            this._lastSyncedValue = normalized;
            this.value = normalized;
        },

        updateStats() {
            const editor = this.$refs.editor;
            const text = editor
                ? (editor.textContent || '').replace(/\u00a0/g, ' ').trim()
                : '';

            this.isEmpty = text.length === 0;
            this.charCount = text.length;
            this.wordCount = text.length === 0 ? 0 : text.split(/\s+/).filter(Boolean).length;
        },

        refreshActiveStates() {
            const editor = this.$refs.editor;
            if (!editor) {
                return;
            }

            for (const cmd of Object.keys(this.active)) {
                try {
                    this.active[cmd] = Boolean(document.queryCommandState(cmd));
                } catch {
                    this.active[cmd] = false;
                }
            }

            this.hasActiveLink = Boolean(this.findSelectedAnchor());

            try {
                const rawBlock = String(document.queryCommandValue('formatBlock') || '').replace(/[<>]/g, '').toLowerCase();
                if (['p', 'h2', 'h3', 'blockquote'].includes(rawBlock)) {
                    this.blockType = rawBlock;
                } else {
                    this.blockType = 'p';
                }
            } catch {
                this.blockType = 'p';
            }
        },
    };
}

export function registerRichEditor() {
    const register = (alpineInstance) => {
        if (!alpineInstance || typeof alpineInstance.data !== 'function') {
            return;
        }

        if (window.__hubafrikaRichEditorRegisteredFor === alpineInstance) {
            return;
        }

        window.__hubafrikaRichEditorRegisteredFor = alpineInstance;
        alpineInstance.data('richTextEditor', createRichTextEditor);
    };

    document.addEventListener('alpine:init', () => {
        register(window.Alpine || Alpine);
    });

    if (window.Alpine) {
        register(window.Alpine);
    }
}

