@if ($user->isAdmin())
    <section class="pf-card">
        <div class="pf-card-head">
            <span class="pf-card-icon pf-card-icon--accent">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
            </span>
            <div class="min-w-0">
                <h2 class="pf-card-title">{{ __('Protected account') }}</h2>
                <p class="pf-card-sub">{{ __('Administrator records cannot be deleted from the profile.') }}</p>
            </div>
        </div>

        <div class="pf-card-body">
            <p class="pf-card-sub">
                {{ __('This account manages the store. Deactivate the administrator access first if the account has to be removed.') }}
            </p>
        </div>
    </section>
@else
    <section class="pf-card">
        <div class="pf-card-head">
            <span class="pf-card-icon" style="background-color: color-mix(in srgb, var(--storefront-accent) 14%, transparent)">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            </span>
            <div class="min-w-0">
                <h2 class="pf-card-title">{{ __('Delete account') }}</h2>
                <p class="pf-card-sub">{{ __('Permanently remove this record and everything linked to it.') }}</p>
            </div>
        </div>

        <div class="pf-card-body">
            <p class="pf-card-sub">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
            </p>

            <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                    class="pf-btn pf-btn--danger mt-4">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                {{ __('Delete account') }}
            </button>
        </div>

        <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
            <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                @csrf
                @method('delete')

                <h2 class="pf-card-title" style="font-size: 1.05rem">
                    {{ __('Are you sure you want to delete your account?') }}
                </h2>

                <p class="pf-card-sub mt-2">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>

                <div class="mt-5">
                    <label for="password" class="pf-field-label sr-only">{{ __('Password') }}</label>
                    <input id="password" name="password" type="password" class="pf-input" placeholder="{{ __('Password') }}">
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1.5" />
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" x-on:click="$dispatch('close')" class="pf-btn pf-btn--ghost">
                        {{ __('Cancel') }}
                    </button>

                    <button type="submit" class="pf-btn pf-btn--danger">
                        {{ __('Delete account') }}
                    </button>
                </div>
            </form>
        </x-modal>
    </section>
@endif
