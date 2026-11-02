@extends('layouts.guest')

@php($uiSections = ['login'])

@section('title', __('ui.login.title'))

@section('content')
<div class="w-full max-w-md glass-card rounded-3xl p-7 sm:p-9 md:p-10 relative overflow-hidden shadow-[0_8px_40px_rgba(0,93,172,0.10)]">
    <!-- Logo / Brand -->
    <div class="flex flex-col items-center mb-10">
        <img src="/images/logo.png?v=2" alt="{{ __('ui.app_name') }}" class="w-32 h-auto mb-3" />
        <p class="font-body-md text-body-md text-on-surface-variant mt-1 text-center max-w-[18rem]">{{ __('ui.login.subtitle') }}</p>
    </div>

    <!-- Error banner -->
    <div id="login-error" class="hidden mb-6 px-4 py-3 rounded-lg bg-error-container text-on-error-container font-body-md text-sm flex items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 20px;">error</span>
        <span id="login-error-text">{{ __('ui.login.generic_error') }}</span>
    </div>

    <!-- SSO Login -->
    <button class="pressable w-full bg-primary-container text-on-primary font-title-md text-[18px] py-3.5 rounded-2xl hover:bg-primary transition-colors shadow-[0_6px_16px_rgba(0,93,172,0.28)] flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed" type="button" id="login-sso-btn">
        <span class="material-symbols-outlined" style="font-size: 20px;">passkey</span>
        <span id="login-sso-label">{{ __('ui.login.sso_button') }}</span>
    </button>

    @if (config('app.dev_login'))
    <!-- Developer sign-in (local only; see config/app.php dev_login) -->
    <form id="dev-login-form" class="mt-8 pt-6 border-t border-outline-variant/60 flex flex-col gap-3" autocomplete="off">
        <p class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1 uppercase tracking-wider">
            <span class="material-symbols-outlined" style="font-size: 16px;">lock</span>
            {{ __('ui.login.dev_title') }}
        </p>
        <input id="dev-code" name="membership_code" type="text" autocapitalize="characters" required placeholder="{{ __('ui.login.dev_code') }}" class="w-full border border-outline-variant/70 rounded-xl py-3 px-4 bg-surface-container-lowest font-body-md focus:outline-none focus:ring-2 focus:ring-primary-container/40 focus:border-primary-container" />
        <input id="dev-password" name="password" type="password" required placeholder="{{ __('ui.login.dev_password') }}" class="w-full border border-outline-variant/70 rounded-xl py-3 px-4 bg-surface-container-lowest font-body-md focus:outline-none focus:ring-2 focus:ring-primary-container/40 focus:border-primary-container" />
        <button type="submit" id="dev-login-btn" class="pressable w-full border border-primary-container text-primary font-title-md text-[16px] py-3 rounded-2xl hover:bg-primary-fixed/50 transition-colors disabled:opacity-60">{{ __('ui.login.dev_button') }}</button>
    </form>
    @endif

    <!-- Support Link -->
    <div class="mt-8 text-center">
        <a class="font-label-sm text-label-sm text-primary hover:underline underline-offset-4 flex items-center justify-center gap-1" href="#">
            <span class="material-symbols-outlined" style="font-size: 16px;">help</span>
            {{ __('ui.login.support_link') }}
        </a>
    </div>
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
        var errorBox = document.getElementById('login-error');
        var errorText = document.getElementById('login-error-text');

        // A failed callback bounces back here with ?error=... — surface it.
        var params = new URLSearchParams(window.location.search);
        if (params.get('error')) {
            errorText.textContent = params.get('error');
            errorBox.classList.remove('hidden');
        }

        var devForm = document.getElementById('dev-login-form');
        if (devForm) {
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
            btn.disabled = true;
            label.textContent = UI_TEXT.login.sso_redirecting;
            errorBox.classList.add('hidden');

            var res = await Eftkad.api('/auth/avarewase/redirect');

            if (res.ok && res.data && res.data.data && res.data.data.url) {
                window.location.href = res.data.data.url;
                return;
            }

            btn.disabled = false;
            label.textContent = UI_TEXT.login.sso_button;
            errorText.textContent = (res.data && res.data.message) || UI_TEXT.login.sso_unavailable;
            errorBox.classList.remove('hidden');
        });
    });
</script>
@endsection
