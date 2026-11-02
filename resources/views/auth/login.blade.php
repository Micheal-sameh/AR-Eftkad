@extends('layouts.guest')

@php($uiSections = ['login'])

@section('title', __('ui.login.title'))

@section('content')
<div class="signin-heading">
    <div>{{ __('ui.fg.portal') }}</div>
    <span>{{ __('ui.fg.sign_in_hint') }}</span>
</div>

<div id="login-error" class="inline-error hidden" style="margin-bottom: 1rem;">
    <x-icon name="alert" :size="16" />
    <span id="login-error-text">{{ __('ui.login.generic_error') }}</span>
</div>

<button type="button" class="button button-primary wide sso-button" id="login-sso-btn">
    <span class="sso-logo" id="login-sso-logo">A</span>
    <span class="spinner hidden" id="login-sso-spinner"></span>
    <span id="login-sso-label">{{ __('ui.login.sso_button') }}</span>
</button>
<a class="text-link" href="#">{{ __('ui.login.support_link') }}</a>

@if (config('app.dev_login'))
<div class="divider"><span>{{ __('ui.fg.or') }}</span></div>
<button type="button" class="developer-toggle" id="dev-toggle">
    <x-icon name="chevron" :size="16" />
    <span>{{ __('ui.fg.developer') }}</span>
</button>
<form id="dev-login-form" class="developer-fields hidden" autocomplete="off">
    <label class="field">
        <span class="field-label">{{ __('ui.login.dev_code') }}</span>
        <span class="input-shell"><input id="dev-code" name="membership_code" type="text" autocapitalize="characters" required placeholder="CH-0000" /></span>
    </label>
    <label class="field">
        <span class="field-label">{{ __('ui.login.dev_password') }}</span>
        <span class="input-shell"><input id="dev-password" name="password" type="password" required placeholder="••••••••" /></span>
    </label>
    <button type="submit" id="dev-login-btn" class="button button-secondary wide"><span>{{ __('ui.fg.secure_sign_in') }}</span></button>
</form>
@endif

<div class="privacy-note">
    <x-icon name="check" :size="15" />
    {{ __('ui.fg.privacy') }}
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Already logged in? skip straight to the app.
        if (Eftkad.token()) {
            window.location.href = '/visits';
            return;
        }

        var btn = document.getElementById('login-sso-btn');
        var label = document.getElementById('login-sso-label');
        var logo = document.getElementById('login-sso-logo');
        var spinner = document.getElementById('login-sso-spinner');
        var errorBox = document.getElementById('login-error');
        var errorText = document.getElementById('login-error-text');

        function setLoading(on) {
            btn.disabled = on;
            logo.classList.toggle('hidden', on);
            spinner.classList.toggle('hidden', !on);
            label.textContent = on ? UI_TEXT.login.sso_redirecting : UI_TEXT.login.sso_button;
        }

        // A failed callback bounces back here with ?error=... — surface it.
        var params = new URLSearchParams(window.location.search);
        if (params.get('error')) {
            errorText.textContent = params.get('error');
            errorBox.classList.remove('hidden');
        }

        var devForm = document.getElementById('dev-login-form');
        if (devForm) {
            document.getElementById('dev-toggle').addEventListener('click', function () {
                devForm.classList.toggle('hidden');
            });

            devForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                var devBtn = document.getElementById('dev-login-btn');
                devBtn.disabled = true;
                errorBox.classList.add('hidden');

                var res = await Eftkad.api('/auth/dev-login', {
                    method: 'POST',
                    body: {
                        membership_code: document.getElementById('dev-code').value.trim(),
                        password: document.getElementById('dev-password').value,
                    },
                });

                if (res.ok && res.data && res.data.data && res.data.data.token) {
                    Eftkad.setAuth(res.data.data.token, res.data.data.user);
                    window.location.href = '/visits';
                    return;
                }

                devBtn.disabled = false;
                errorText.textContent = (res.data && res.data.message) || UI_TEXT.login.generic_error;
                errorBox.classList.remove('hidden');
            });
        }

        btn.addEventListener('click', async function () {
            setLoading(true);
            errorBox.classList.add('hidden');

            var res = await Eftkad.api('/auth/avarewase/redirect');

            if (res.ok && res.data && res.data.data && res.data.data.url) {
                window.location.href = res.data.data.url;
                return;
            }

            setLoading(false);
            errorText.textContent = (res.data && res.data.message) || UI_TEXT.login.sso_unavailable;
            errorBox.classList.remove('hidden');
        });
    });
</script>
@endsection
