@php
    $recaptcha = app(\App\Services\RecaptchaService::class);
    $recaptchaEnabled = $recaptcha->isEnabled();
    $recaptchaSiteKey = $recaptcha->siteKey();
@endphp

@if ($recaptchaEnabled && $recaptchaSiteKey !== '')
    <script src="https://www.google.com/recaptcha/api.js?render={{ urlencode($recaptchaSiteKey) }}" async defer></script>
    <script>
        window.hubafrikaRecaptchaSiteKey = @json($recaptchaSiteKey);
        window.hubafrikaRecaptchaToken = function (action) {
            return new Promise(function (resolve) {
                var siteKey = window.hubafrikaRecaptchaSiteKey;
                if (!siteKey || typeof grecaptcha === 'undefined' || !grecaptcha.ready) {
                    resolve('');
                    return;
                }
                try {
                    grecaptcha.ready(function () {
                        grecaptcha.execute(siteKey, { action: action || 'submit' })
                            .then(function (token) { resolve(token || ''); })
                            .catch(function () { resolve(''); });
                    });
                } catch (e) {
                    resolve('');
                }
            });
        };

        document.addEventListener('submit', function (event) {
            var form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }
            var action = form.getAttribute('data-recaptcha-action');
            if (!action || form.dataset.recaptchaSubmitting === '1') {
                return;
            }
            event.preventDefault();
            form.dataset.recaptchaSubmitting = '1';

            window.hubafrikaRecaptchaToken(action).then(function (token) {
                var input = form.querySelector('input[name="g-recaptcha-response"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'g-recaptcha-response';
                    form.appendChild(input);
                }
                input.value = token;
                HTMLFormElement.prototype.submit.call(form);
            }).catch(function () {
                form.dataset.recaptchaSubmitting = '';
            });
        });
    </script>
@else
    <script>
        window.hubafrikaRecaptchaToken = function () {
            return Promise.resolve('');
        };
    </script>
@endif
