<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
@include('partials.head-assets')
<title>@yield('title', 'إفتقاد')</title>
</head>
<body class="bg-background text-on-background min-h-screen flex flex-col relative overflow-hidden font-body-md">
@include('partials.api-js')

<!-- Decorative Background Elements -->
<div class="absolute inset-0 bg-pattern pointer-events-none"></div>
<div class="absolute top-0 rtl:right-0 ltr:left-0 w-96 h-96 bg-primary-fixed opacity-20 rounded-full blur-3xl -translate-y-1/2 rtl:translate-x-1/2 ltr:-translate-x-1/2 pointer-events-none"></div>
<div class="absolute bottom-0 rtl:left-0 ltr:right-0 w-96 h-96 bg-tertiary-fixed opacity-20 rounded-full blur-3xl translate-y-1/2 rtl:-translate-x-1/2 ltr:translate-x-1/2 pointer-events-none"></div>

<header class="w-full p-container-margin flex justify-end z-10 relative">
    <button type="button" onclick="Eftkad.toggleLocale()" class="flex items-center gap-2 px-4 py-2 rounded-full border border-outline-variant text-on-surface-variant hover:bg-surface-container-low transition-colors font-label-sm text-label-sm">
        <span class="material-symbols-outlined" style="font-size: 18px;">language</span>
        <span id="locale-toggle-label">EN</span>
    </button>
</header>

<main class="flex-grow flex items-center justify-center p-container-margin z-10 relative">
    @yield('content')
</main>

<footer class="p-container-margin text-center z-10 relative">
    <p class="font-label-sm text-label-sm text-outline">© {{ date('Y') }} {{ __('ui.guest.footer') }}</p>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var label = document.getElementById('locale-toggle-label');
        if (label) {
            label.textContent = Eftkad.locale() === 'ar' ? 'EN' : 'AR';
        }
    });
</script>
@yield('scripts')
</body>
</html>
