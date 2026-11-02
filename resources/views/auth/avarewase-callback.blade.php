@extends('layouts.guest')

@php($uiSections = ['callback'])

@section('title', __('ui.callback.title'))

@section('content')
<div class="signin-heading" id="callback-pending">
    <div style="display:flex;justify-content:center;margin-bottom:.8rem"><span class="spinner" style="width:1.6rem;height:1.6rem;color:var(--primary)"></span></div>
    <span>{{ __('ui.callback.pending') }}</span>
</div>

<div id="callback-error" class="hidden">
    <div class="inline-error" style="margin-top:1.5rem">
        <x-icon name="alert" :size="16" />
        <span id="callback-error-text">{{ __('ui.callback.error_default') }}</span>
    </div>
    <a href="/login" class="text-link">{{ __('ui.callback.back_to_login') }}</a>
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
            body: { code: code, state: state },
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
