@extends('layouts.guest')

@section('title', 'تسجيل الدخول - إفتقاد')

@section('content')
<div class="w-full max-w-md glass-card rounded-2xl p-8 md:p-10 relative overflow-hidden">
    <!-- Card Accent -->
    <div class="absolute top-0 right-0 w-2 h-full bg-primary-container rounded-r-2xl"></div>

    <!-- Logo / Brand -->
    <div class="flex flex-col items-center mb-10">
        <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center mb-4 shadow-sm border border-outline-variant">
            <span class="material-symbols-outlined text-primary text-3xl">church</span>
        </div>
        <h1 class="font-display-lg text-display-lg text-primary text-center">إفتقاد</h1>
        <p class="font-body-md text-body-md text-on-surface-variant mt-2 text-center">بوابة الخدام - تسجيل الدخول</p>
    </div>

    <!-- Error banner -->
    <div id="login-error" class="hidden mb-6 px-4 py-3 rounded-lg bg-error-container text-on-error-container font-body-md text-sm flex items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 20px;">error</span>
        <span id="login-error-text">حدث خطأ ما</span>
    </div>

    <!-- Login Form -->
    <form id="login-form" class="space-y-6">
        <!-- Membership Code Field -->
        <div class="space-y-2">
            <label class="block font-label-sm text-label-sm text-on-surface-variant" for="membership_code">رقم العضوية</label>
            <div class="relative">
                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                    <span class="material-symbols-outlined text-outline">badge</span>
                </div>
                <input class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg py-3 pr-12 pl-4 text-on-surface font-body-md text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all" id="membership_code" name="membership_code" placeholder="أدخل رقم العضوية الخاص بك" type="text" autocomplete="username" required />
            </div>
            <p class="field-error" id="error-membership_code"></p>
        </div>

        <!-- Password Field -->
        <div class="space-y-2">
            <div class="flex justify-between items-center">
                <label class="block font-label-sm text-label-sm text-on-surface-variant" for="password">كلمة المرور</label>
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                    <span class="material-symbols-outlined text-outline">lock</span>
                </div>
                <input class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg py-3 pr-12 pl-4 text-on-surface font-body-md text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all" id="password" name="password" placeholder="••••••••" type="password" autocomplete="current-password" required />
            </div>
            <p class="field-error" id="error-password"></p>
        </div>

        <!-- Submit Button -->
        <button class="w-full bg-primary-container text-on-primary font-title-md text-title-md py-4 rounded-xl hover:bg-primary transition-all shadow-md active:scale-95 flex items-center justify-center gap-2 mt-8 disabled:opacity-60 disabled:cursor-not-allowed" type="submit" id="login-submit">
            <span id="login-submit-label">تسجيل الدخول</span>
            <span class="material-symbols-outlined" style="font-size: 20px; transform: scaleX(-1);">arrow_forward</span>
        </button>
    </form>

    <!-- Support Link -->
    <div class="mt-8 text-center">
        <a class="font-label-sm text-label-sm text-primary hover:underline underline-offset-4 flex items-center justify-center gap-1" href="#">
            <span class="material-symbols-outlined" style="font-size: 16px;">help</span>
            هل تواجه مشكلة في تسجيل الدخول؟
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

        var form = document.getElementById('login-form');
        var submitBtn = document.getElementById('login-submit');
        var submitLabel = document.getElementById('login-submit-label');
        var errorBox = document.getElementById('login-error');
        var errorText = document.getElementById('login-error-text');

        function clearErrors() {
            errorBox.classList.add('hidden');
            document.querySelectorAll('.field-error').forEach(function (el) {
                el.textContent = '';
                el.classList.remove('show');
            });
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            clearErrors();

            submitBtn.disabled = true;
            submitLabel.textContent = 'جاري تسجيل الدخول...';

            var payload = {
                membership_code: document.getElementById('membership_code').value.trim(),
                password: document.getElementById('password').value,
            };

            var res = await Eftkad.api('/auth/login', { method: 'POST', body: payload });

            submitBtn.disabled = false;
            submitLabel.textContent = 'تسجيل الدخول';

            if (res.ok && res.data && res.data.data && res.data.data.token) {
                Eftkad.setAuth(res.data.data.token, res.data.data.user);
                window.location.href = '/visits';
                return;
            }

            if (res.status === 422 && res.data && res.data.errors) {
                Object.keys(res.data.errors).forEach(function (field) {
                    var el = document.getElementById('error-' + field);
                    if (el) {
                        el.textContent = res.data.errors[field][0];
                        el.classList.add('show');
                    }
                });
                errorText.textContent = 'برجاء التحقق من البيانات المدخلة';
                errorBox.classList.remove('hidden');
                return;
            }

            errorText.textContent = (res.data && res.data.message) || 'رقم العضوية أو كلمة المرور غير صحيحة';
            errorBox.classList.remove('hidden');
        });
    });
</script>
@endsection
