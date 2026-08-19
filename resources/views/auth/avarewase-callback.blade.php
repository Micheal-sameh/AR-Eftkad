@extends('layouts.guest')

@section('title', __('ui.callback.title'))

@section('content')
<div class="w-full max-w-md glass-card rounded-2xl p-6 sm:p-8 md:p-10 relative overflow-hidden text-center">
    <div class="absolute top-0 rtl:right-0 ltr:left-0 w-2 h-full bg-primary-container rtl:rounded-r-2xl ltr:rounded-l-2xl"></div>

    <div class="flex flex-col items-center mb-8">
        <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center mb-4 shadow-sm border border-outline-variant">
            <span class="material-symbols-outlined text-primary text-3xl">church</span>
        </div>
        <h1 class="font-display-lg text-display-lg text-primary text-center">{{ __('ui.app_name') }}</h1>
    </div>

    <div id="callback-pending">
        <span class="material-symbols-outlined text-primary text-4xl animate-spin" style="display: inline-block;">progress_activity</span>
        <p class="font-body-md text-body-md text-on-surface-variant mt-4">{{ __('ui.callback.pending') }}</p>
    </div>

    <div id="callback-error" class="hidden">
        <span class="material-symbols-outlined text-error text-4xl" style="display: inline-block;">error</span>
        <p id="callback-error-text" class="font-body-md text-body-md text-on-surface-variant mt-4">{{ __('ui.callback.error_default') }}</p>
        <a href="/login" class="inline-block mt-6 font-label-sm text-label-sm text-primary hover:underline underline-offset-4">{{ __('ui.callback.back_to_login') }}</a>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async function () {
        var pending = document.getElementById('callback-pending');
        var errorBox = document.getElementById('callback-error');
        var errorText = document.getElementById('callback-error-text');

        function fail(message) {
            pending.classList.add('hidden');
            errorText.textContent = message;
            errorBox.classList.remove('hidden');
        }

        var params = new URLSearchParams(window.location.search);
        var code = params.get('code');
        var state = params.get('state');

        if (params.get('error')) {
            fail(params.get('error_description') || params.get('error'));
            return;
        }

        if (!code || !state) {
            fail(UI_TEXT.callback.error_incomplete_link);
            return;
        }

        var res = await Eftkad.api('/auth/avarewase/callback', {
            method: 'POST',
            body: { code: code, state: state, device_id: Eftkad.deviceId() },
        });

        if (res.ok && res.data && res.data.data && res.data.data.token) {
            Eftkad.setAuth(res.data.data.token, res.data.data.user);
            Eftkad.markShowInstallPromptAfterLogin();
            window.location.href = '/visits';
            return;
        }

        fail((res.data && res.data.message) || UI_TEXT.callback.error_avarewase);
    });
</script>
@endsection
