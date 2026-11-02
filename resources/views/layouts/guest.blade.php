<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
@php($uiSections = array_merge($uiSections ?? [], ['fg']))
@include('partials.head-assets')
<title>@yield('title', 'إفتقاد')</title>
</head>
<body>
@include('partials.api-js')

<div class="signin-page">
    <div class="signin-tools">
        <div class="language-toggle">
            <button type="button" data-lang="ar">ع</button>
            <button type="button" data-lang="en">EN</button>
        </div>
        <button type="button" class="icon-button" data-theme-toggle aria-label="{{ __('ui.fg.theme') }}"><span data-theme-icon style="display:contents"></span></button>
    </div>

    <div class="signin-card">
        <div class="brand">
            <div class="brand-mark"><img src="/icons/icon-192.png?v=2" alt="{{ __('ui.app_name') }}" /></div>
            <div>
                <div class="brand-name">Eftkad <span>افتقاد</span></div>
                <div class="brand-subtitle">{{ __('ui.fg.subtitle') }}</div>
            </div>
        </div>
        @yield('content')
    </div>

    <div class="signin-footer">© {{ date('Y') }} {{ __('ui.guest.footer') }}</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () { Eftkad.initChrome(); });
</script>
@yield('scripts')
</body>
</html>
