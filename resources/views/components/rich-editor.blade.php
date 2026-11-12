@props([
    'id' => null,
    'name' => null,
    'value' => null,
    'placeholder' => 'Start writing...',
    'compact' => false,
    'minHeight' => null,
    'disabled' => false,
])

@php
    $editorId = $id ?? 'rte-'.\Illuminate\Support\Str::ulid();
    $resolvedMinHeight = $minHeight ?? ($compact ? '96px' : '150px');
@endphp

<div
    x-data="richTextEditor({ initialValue: @js($value ?? ''), disabled: @js((bool) $disabled) })"
    x-modelable="value"
    {{ $attributes->whereDoesntStartWith('wire:model')->except(['id'])->class(['rte-shell rounded-lg border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-950']) }}
    {{ $attributes->wire('model') }}
>
    {{-- Toolbar --}}
    <div
        role="toolbar"
        aria-label="Text formatting"
        @class([
            'rte-toolbar flex flex-wrap items-center gap-1 border-b border-gray-200 bg-gray-50/90 dark:border-gray-800 dark:bg-gray-900/90',
            'px-2 py-1.5' => $compact,
            'px-2.5 py-2' => ! $compact,
        ])
    >
        <label class="sr-only" for="{{ $editorId }}-format">Text style</label>
        <select
            id="{{ $editorId }}-format"
            x-model="blockType"
            @change="applyBlockFormat($event.target.value)"
            :disabled="disabled || codeMode"
            title="Paragraph / Heading style"
            class="rte-select rounded-md border border-gray-300 bg-white px-2 py-1 text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200 disabled:opacity-50"
        >
            <option value="p">Paragraph</option>
            <option value="h2">Heading 2</option>
            <option value="h3">Heading 3</option>
            <option value="blockquote">Quote</option>
        </select>

        <span class="mx-0.5 h-4 w-px bg-gray-300 dark:bg-gray-700" aria-hidden="true"></span>

        <button
            type="button"
            @mousedown.prevent="exec('bold')"
            :disabled="disabled || codeMode"
            :aria-pressed="active.bold ? 'true' : 'false'"
            :class="active.bold ? 'is-active' : ''"
            class="rte-btn"
            title="Bold (Ctrl+B)"
            aria-label="Bold"
        >
            <span class="font-bold">B</span>
        </button>

        <button
            type="button"
            @mousedown.prevent="exec('italic')"
            :disabled="disabled || codeMode"
            :aria-pressed="active.italic ? 'true' : 'false'"
            :class="active.italic ? 'is-active' : ''"
            class="rte-btn"
            title="Italic (Ctrl+I)"
            aria-label="Italic"
        >
            <span class="font-serif italic">I</span>
        </button>

        <button
            type="button"
            @mousedown.prevent="exec('underline')"
            :disabled="disabled || codeMode"
            :aria-pressed="active.underline ? 'true' : 'false'"
            :class="active.underline ? 'is-active' : ''"
            class="rte-btn"
            title="Underline (Ctrl+U)"
            aria-label="Underline"
        >
            <span class="underline">U</span>
        </button>

        <button
            type="button"
            @mousedown.prevent="exec('strikeThrough')"
            :disabled="disabled || codeMode"
            :aria-pressed="active.strikeThrough ? 'true' : 'false'"
            :class="active.strikeThrough ? 'is-active' : ''"
            class="rte-btn"
            title="Strikethrough"
            aria-label="Strikethrough"
        >
            <span class="line-through">S</span>
        </button>

        <span class="mx-0.5 h-4 w-px bg-gray-300 dark:bg-gray-700" aria-hidden="true"></span>

        <button
            type="button"
            @mousedown.prevent="exec('insertUnorderedList')"
            :disabled="disabled || codeMode"
            :aria-pressed="active.insertUnorderedList ? 'true' : 'false'"
            :class="active.insertUnorderedList ? 'is-active' : ''"
            class="rte-btn"
            title="Bullet list"
            aria-label="Bullet list"
        >
            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M3 5a1 1 0 100-2 1 1 0 000 2zm3-1a1 1 0 011-1h10a1 1 0 110 2H7a1 1 0 01-1-1zm0 6a1 1 0 011-1h10a1 1 0 110 2H7a1 1 0 01-1-1zm1 5a1 1 0 100 2h10a1 1 0 100-2H7zM4 10a1 1 0 11-2 0 1 1 0 012 0zm-1 7a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
        </button>

        <button
            type="button"
            @mousedown.prevent="exec('insertOrderedList')"
            :disabled="disabled || codeMode"
            :aria-pressed="active.insertOrderedList ? 'true' : 'false'"
            :class="active.insertOrderedList ? 'is-active' : ''"
            class="rte-btn"
            title="Numbered list"
            aria-label="Numbered list"
        >
            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" d="M8 5h9M8 10h9M8 15h9M3 4.5l1-.5v3M3 10.5c0-.6.5-1 1-1s1 .4 1 1c0 .5-.4.8-1 1.2L3 13h2M3 15.5c.3-.3.7-.5 1.1-.5.5 0 .9.3.9.7 0 .5-.4.8-.9.8.5 0 .9.3.9.8 0 .5-.4.8-1 .8-.4 0-.8-.2-1.1-.4" />
            </svg>
        </button>

        <button
            type="button"
            @mousedown.prevent="openLinkBar()"
            :disabled="disabled || codeMode"
            :aria-pressed="hasActiveLink || showLinkBar ? 'true' : 'false'"
            :class="hasActiveLink || showLinkBar ? 'is-active' : ''"
            class="rte-btn"
            title="Insert or edit link"
            aria-label="Insert or edit link"
        >
            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 11.5l3-3m-4.243 4.95l-1.414 1.414a3 3 0 01-4.243-4.243l1.414-1.414m6.364-2.121l1.414-1.414a3 3 0 014.243 4.243l-1.414 1.414" />
            </svg>
        </button>

        <span class="mx-0.5 h-4 w-px bg-gray-300 dark:bg-gray-700" aria-hidden="true"></span>

        <button
            type="button"
            @mousedown.prevent="exec('removeFormat')"
            :disabled="disabled || codeMode"
            class="rte-btn"
            title="Clear formatting"
            aria-label="Clear formatting"
        >
            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12.5 3.5l4 4L8 16H4v-4l8.5-8.5zM9 7l4 4M11 16h6" />
            </svg>
        </button>

        <button
            type="button"
            @mousedown.prevent="exec('undo')"
            :disabled="disabled || codeMode"
            class="rte-btn"
            title="Undo"
            aria-label="Undo"
        >
            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7H3V3m0 4a7 7 0 111.7 7.6" />
            </svg>
        </button>

        <button
            type="button"
            @mousedown.prevent="exec('redo')"
            :disabled="disabled || codeMode"
            class="rte-btn"
            title="Redo"
            aria-label="Redo"
        >
            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h4V3m0 4a7 7 0 10-1.7 7.6" />
            </svg>
        </button>

        <button
            type="button"
            @click="toggleCodeMode()"
            :disabled="disabled"
            :aria-pressed="codeMode ? 'true' : 'false'"
            :class="codeMode ? 'is-active' : ''"
            class="rte-btn ml-auto font-mono text-[11px]"
            title="Toggle HTML source view"
            aria-label="Toggle HTML source view"
        >
            &lt;/&gt;
        </button>
    </div>

    {{-- Inline Link Popover --}}
    <div
        x-cloak
        x-show="showLinkBar"
        @keydown.escape.prevent.stop="closeLinkBar()"
        class="border-b border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-800 dark:bg-gray-900"
    >
        <div class="flex flex-wrap items-center gap-2">
            <label for="{{ $editorId }}-link-url" class="sr-only">Link URL</label>
            <input
                id="{{ $editorId }}-link-url"
                x-ref="linkInput"
                x-model="linkUrl"
                @keydown.enter.prevent.stop="applyLink()"
                type="text"
                placeholder="https://example.com or /shop"
                class="admin-primary-focus min-w-[12rem] flex-1 rounded-md border-gray-300 bg-white px-2.5 py-1 text-xs dark:border-gray-700 dark:bg-gray-950"
            >
            <label class="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-300">
                <input type="checkbox" x-model="linkNewTab" class="admin-primary-focus rounded border-gray-300">
                New tab
            </label>
            <button
                type="button"
                @click="applyLink()"
                class="admin-primary-bg rounded-md px-2.5 py-1 text-xs font-semibold text-white"
            >
                Apply
            </button>
            <button
                type="button"
                x-show="hasActiveLink"
                @click="removeLink()"
                class="rounded-md border border-red-200 px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
            >
                Remove
            </button>
            <button
                type="button"
                @click="closeLinkBar()"
                class="rounded-md border border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
            >
                Cancel
            </button>
        </div>
        <p x-show="linkError" x-text="linkError" class="mt-1 text-xs text-red-600 dark:text-red-400"></p>
    </div>

    {{-- Editable Visual Area (wire:ignore preserves caret & live DOM while Alpine syncs to value) --}}
    <div wire:ignore x-show="!codeMode" class="relative">
        <div
            id="{{ $editorId }}"
            x-ref="editor"
            contenteditable="{{ $disabled ? 'false' : 'true' }}"
            role="textbox"
            aria-multiline="true"
            data-placeholder="{{ $placeholder }}"
            @focus="onEditorFocus()"
            @blur="onEditorBlur()"
            @input="onEditorInput()"
            @keyup="refreshActiveStates()"
            @mouseup="refreshActiveStates()"
            @paste="handlePaste($event)"
            :class="{ 'is-empty': isEmpty }"
            style="min-height: {{ $resolvedMinHeight }};"
            @class([
                'rte-editor w-full text-sm text-gray-900 focus:outline-none dark:text-gray-100',
                'px-3 py-2' => $compact,
                'px-3.5 py-3' => ! $compact,
            ])
        ></div>
    </div>

    {{-- Optional HTML Source Mode --}}
    <div x-cloak x-show="codeMode">
        <label for="{{ $editorId }}-source" class="sr-only">HTML source</label>
        <textarea
            id="{{ $editorId }}-source"
            x-ref="codeInput"
            x-model="codeValue"
            @input="onCodeInput()"
            @blur="onCodeInput()"
            rows="{{ $compact ? 4 : 6 }}"
            style="min-height: {{ $resolvedMinHeight }};"
            class="block w-full border-0 bg-gray-950 px-3.5 py-2.5 font-mono text-xs leading-relaxed text-emerald-300 focus:outline-none focus:ring-0"
            placeholder="<p>Write or edit HTML source...</p>"
        ></textarea>
    </div>

    @if ($name)
        <input type="hidden" name="{{ $name }}" :value="value">
    @endif

    {{-- Footer Status Bar --}}
    <div class="flex items-center justify-between border-t border-gray-200 bg-gray-50/60 px-3 py-1 text-[11px] text-gray-500 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-400">
        <span>
            <span x-show="!codeMode">Rich text</span>
            <span x-cloak x-show="codeMode">HTML source</span>
        </span>
        <span x-text="`${wordCount} ${wordCount === 1 ? 'word' : 'words'} · ${charCount} chars`"></span>
    </div>
</div>
