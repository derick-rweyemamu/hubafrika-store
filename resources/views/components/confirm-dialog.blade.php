{{--
    Centred confirmation dialog.

    Replaces the browser's native confirm() prompt for every wire:confirm
    attribute. The Livewire directive is re-pointed at this dialog in
    resources/js/app.js, so no individual button needs changing.

    Fires on window: new CustomEvent('open-confirm', { detail: { message, onConfirm, onCancel } })
--}}
<div
    x-data="{
        open: false,
        message: '',
        pending: null,
        pendingCancel: null,
        show(detail) {
            this.message = detail.message || 'Are you sure?';
            this.pending = typeof detail.onConfirm === 'function' ? detail.onConfirm : null;
            this.pendingCancel = typeof detail.onCancel === 'function' ? detail.onCancel : null;
            this.open = true;
            this.$nextTick(() => this.$refs.confirmButton?.focus());
        },
        confirm() {
            const run = this.pending;
            this.close();
            if (run) {
                run();
            }
        },
        cancel() {
            const skip = this.pendingCancel;
            this.close();
            if (skip) {
                skip();
            }
        },
        close() {
            this.open = false;
            this.pending = null;
            this.pendingCancel = null;
            document.body.style.removeProperty('overflow');
        },
    }"
    x-init="window.__confirmDialogReady = true"
    x-on:open-confirm.window="document.body.style.overflow = 'hidden'; show($event.detail)"
    x-on:keydown.escape.window="open && cancel()"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[9999] overflow-y-auto"
    role="alertdialog"
    aria-modal="true"
    aria-labelledby="confirm-dialog-title"
    aria-describedby="confirm-dialog-message"
>
    <div
        class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm"
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click="cancel()"
    ></div>

    <div class="relative flex min-h-full items-center justify-center p-4">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-3 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-3 scale-95"
            class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10"
        >
            <div class="flex items-start gap-4 p-6">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h3 id="confirm-dialog-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Please confirm</h3>
                    <p id="confirm-dialog-message" class="mt-1.5 text-sm leading-relaxed text-gray-600 dark:text-gray-300" x-text="message"></p>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-800 dark:bg-gray-950/60 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    x-on:click="cancel()"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                >Cancel</button>
                <button
                    type="button"
                    x-on:click="confirm()"
                    x-ref="confirmButton"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
                >Confirm</button>
            </div>
        </div>
    </div>
</div>
