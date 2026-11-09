import Alpine from 'alpinejs';

if (!window.Livewire) {
    window.Alpine = Alpine;
    Alpine.start();
}

let confirmHookInstalled = false;

document.addEventListener('livewire:init', useCentredConfirmDialog);


import grapesjs from 'grapesjs';
import basicBlocks from 'grapesjs-blocks-basic';


import 'grapesjs/dist/css/grapes.min.css';
window.grapesjs = grapesjs;
window.basicBlocks = basicBlocks;

/**
 * Send every wire:confirm prompt to the centred confirm dialog.
 *
 * Livewire's built-in `confirm` directive registers its listener when the
 * bundle loads, so hooking `directive.init` here (registered later, during
 * livewire:init) runs afterwards and replaces the native prompt for each
 * element. Replacing the handler rather than the directive keeps Livewire's
 * own action/cancel flow intact.
 *
 * If the dialog is not on the page we fall back to the browser prompt so a
 * destructive action can never fire without asking.
 */
function useCentredConfirmDialog() {
    if (confirmHookInstalled || typeof window.Livewire?.hook !== 'function') {
        return;
    }

    confirmHookInstalled = true;

    window.Livewire.hook('directive.init', ({ el, directive }) => {
        if (directive.value !== 'confirm') {
            return;
        }

        const message = (directive.expression || 'Are you sure?').replaceAll('\\n', '\n');

        el.__livewire_confirm = (action, instead) => {
            if (!window.__confirmDialogReady) {
                if (window.confirm(message)) {
                    action();
                } else if (typeof instead === 'function') {
                    instead();
                }

                return;
            }

            window.dispatchEvent(
                new CustomEvent('open-confirm', {
                    detail: { message, onConfirm: action, onCancel: instead },
                }),
            );
        };
    });
}

// Livewire normalises start() onto DOMContentLoaded, but register immediately
// too in case the script order ever puts us after the framework booted.
useCentredConfirmDialog();
