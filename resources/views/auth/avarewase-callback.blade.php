@extends('layouts.guest')

@section('title', 'جاري تسجيل الدخول - إفتقاد')

@section('content')
<div class="w-full max-w-md glass-card rounded-2xl p-8 md:p-10 relative overflow-hidden text-center">
    <div class="absolute top-0 right-0 w-2 h-full bg-primary-container rounded-r-2xl"></div>

    <div class="flex flex-col items-center mb-8">
        <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center mb-4 shadow-sm border border-outline-variant">
            <span class="material-symbols-outlined text-primary text-3xl">church</span>
        </div>
        <h1 class="font-display-lg text-display-lg text-primary text-center">إفتقاد</h1>
    </div>

    <div id="callback-pending">
        <span class="material-symbols-outlined text-primary text-4xl animate-spin" style="display: inline-block;">progress_activity</span>
        <p class="font-body-md text-body-md text-on-surface-variant mt-4">جاري إتمام تسجيل الدخول...</p>
    </div>

    <div id="callback-error" class="hidden">
        <span class="material-symbols-outlined text-error text-4xl" style="display: inline-block;">error</span>
        <p id="callback-error-text" class="font-body-md text-body-md text-on-surface-variant mt-4">تعذر تسجيل الدخول</p>
        <a href="/login" class="inline-block mt-6 font-label-sm text-label-sm text-primary hover:underline underline-offset-4">العودة لصفحة تسجيل الدخول</a>
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
            fail('رابط تسجيل الدخول غير مكتمل');
            return;
        }

        var res = await Eftkad.api('/auth/avarewase/callback', {
            method: 'POST',
            body: { code: code, state: state },
        });

        if (res.ok && res.data && res.data.data && res.data.data.token) {
            Eftkad.setAuth(res.data.data.token, res.data.data.user);
            window.location.href = '/visits';
            return;
        }

        fail((res.data && res.data.message) || 'تعذر تسجيل الدخول عبر أفارويز');
    });
</script>
@endsection
