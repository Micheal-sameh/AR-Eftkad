@extends('layouts.guest')

@section('title', __('ui.login.title'))

@section('content')
<div class="w-full max-w-md glass-card rounded-2xl p-6 sm:p-8 md:p-10 relative overflow-hidden">
    <!-- Card Accent -->
    <div class="absolute top-0 rtl:right-0 ltr:left-0 w-2 h-full bg-primary-container rtl:rounded-r-2xl ltr:rounded-l-2xl"></div>

    <!-- Logo / Brand -->
    <div class="flex flex-col items-center mb-10">
        <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center mb-4 shadow-sm border border-outline-variant">
            <span class="material-symbols-outlined text-primary text-3xl">church</span>
        </div>
        <h1 class="font-display-lg text-display-lg text-primary text-center">{{ __('ui.app_name') }}</h1>
        <p class="font-body-md text-body-md text-on-surface-variant mt-2 text-center">{{ __('ui.login.subtitle') }}</p>
    </div>

    <!-- Error banner -->
    <div id="login-error" class="hidden mb-6 px-4 py-3 rounded-lg bg-error-container text-on-error-container font-body-md text-sm flex items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 20px;">error</span>
        <span id="login-error-text">{{ __('ui.login.generic_error') }}</span>
    </div>

    <!-- SSO Login -->
    <button class="w-full bg-primary-container text-on-primary font-title-md text-title-md py-4 rounded-xl hover:bg-primary transition-all shadow-md active:scale-95 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed" type="button" id="login-sso-btn">
        <span class="material-symbols-outlined" style="font-size: 20px;">passkey</span>
        <span id="login-sso-label">{{ __('ui.login.sso_button') }}</span>
    </button>

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
