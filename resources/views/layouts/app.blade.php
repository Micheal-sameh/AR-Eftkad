<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
@php($uiSections = array_merge($uiSections ?? [], ['install_prompt']))
@include('partials.head-assets')
<title>@yield('title', 'Eftkad')</title>
</head>
<body class="font-body-md min-h-screen flex flex-col md:flex-row pb-nav-safe md:pb-0">
@include('partials.api-js')

<!-- Install App Prompt (shown once, right after login) -->
<div id="install-prompt" class="safe-bottom hidden fixed inset-0 z-[60] flex items-end md:items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-sm bg-surface-container-lowest rounded-2xl shadow-2xl p-4 sm:p-card-padding relative">
        <button type="button" id="install-dismiss" class="absolute top-3 rtl:left-3 ltr:right-3 p-1 rounded-full text-on-surface-variant hover:bg-surface-container transition-colors">
            <span class="material-symbols-outlined">close</span>
        </button>
        <div class="flex flex-col items-center text-center gap-3">
            <img src="/icons/icon-192.png?v=2" alt="{{ __('ui.app_name') }}" class="w-16 h-16 rounded-2xl shadow-md" />
            <h3 class="font-title-md text-title-md text-on-surface">{{ __('ui.install_prompt.title') }}</h3>
            <p class="font-body-md text-body-md text-on-surface-variant" id="install-prompt-text">
                {{ __('ui.install_prompt.text_default') }}
            </p>
            <div class="flex gap-3 w-full mt-2">
                <button type="button" id="install-later" class="flex-1 px-4 py-3 rounded-lg border border-outline-variant text-on-surface-variant font-title-md text-title-md hover:bg-surface-container-low transition-colors">
                    {{ __('ui.install_prompt.later') }}
                </button>
                <button type="button" id="install-confirm" class="flex-1 px-4 py-3 rounded-lg bg-primary text-on-primary font-title-md text-title-md hover:bg-surface-tint transition-colors">
                    {{ __('ui.install_prompt.install') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- NavigationDrawer (Desktop) -->
<nav class="hidden md:flex flex-col h-full p-4 fixed rtl:right-0 ltr:left-0 top-0 bg-surface rtl:border-l ltr:border-r border-outline-variant w-64 z-40 transition-all duration-200 ease-in-out">
    <div class="mb-6">
        <h1 class="font-headline-lg text-2xl text-primary">Eftkad</h1>
    </div>
    <div class="flex items-center gap-3 mb-6">
        <div class="w-9 h-9 rounded-full overflow-hidden bg-surface-container-high flex-shrink-0 border border-outline-variant flex items-center justify-center">
            <span class="material-symbols-outlined text-outline text-[20px]">person</span>
        </div>
        <div class="min-w-0">
            <p class="font-title-md text-sm font-semibold text-on-surface truncate" id="nav-user-name">&nbsp;</p>
            <p class="font-label-sm text-label-sm text-tertiary" id="nav-user-type">&nbsp;</p>
        </div>
    </div>
    <ul class="flex flex-col gap-1 flex-grow">
        <li>
            <a href="/visits" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all {{ ($activeNav ?? '') === 'visits' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined text-[20px]">church</span>
                <span class="text-sm">{{ __('ui.nav.visits') }}</span>
            </a>
        </li>
        <li>
            <a href="/visits/create" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all {{ ($activeNav ?? '') === 'create' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined text-[20px]">edit_calendar</span>
                <span class="text-sm">{{ __('ui.nav.new_visit') }}</span>
            </a>
        </li>
        <li>
            <a href="/directory" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all {{ ($activeNav ?? '') === 'directory' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined text-[20px]">menu_book</span>
                <span class="text-sm">{{ __('ui.nav.directory') }}</span>
            </a>
        </li>
        <li>
            <a href="/users" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all {{ ($activeNav ?? '') === 'users' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined text-[20px]">manage_accounts</span>
                <span class="text-sm">{{ __('ui.nav.users') }}</span>
            </a>
        </li>
    </ul>
    <div class="mt-auto">
        <button type="button" id="nav-locale-toggle" class="w-full flex items-center gap-3 text-on-surface-variant px-3 py-2 hover:bg-surface-container-low rounded-lg transition-all">
            <span class="material-symbols-outlined text-[20px]">language</span>
            <span class="text-sm" id="nav-locale-label">AR/EN</span>
        </button>
        <button type="button" id="nav-signout" class="w-full flex items-center gap-3 text-on-surface-variant px-3 py-2 hover:bg-surface-container-low rounded-lg transition-all">
            <span class="material-symbols-outlined text-[20px]">door_open</span>
            <span class="text-sm">{{ __('ui.nav.sign_out') }}</span>
        </button>
    </div>
</nav>

<!-- Main Content Area -->
<div class="flex-grow flex flex-col md:rtl:mr-64 md:ltr:ml-64 w-full relative min-w-0">
    <!-- TopAppBar (Mobile & Desktop Header Area) -->
    <header class="brand-hero safe-top w-full md:static">
        <div class="relative z-10 max-w-7xl mx-auto px-container-margin md:px-8 pt-1 md:py-4 flex flex-col gap-5 md:gap-0">
            <div class="flex justify-between items-center w-full">
                <div class="flex items-center gap-3 md:hidden">
                    <img src="/icons/icon-192.png?v=2" alt="" width="40" height="40" class="w-10 h-10 rounded-2xl shadow-md ring-2 ring-white/40" />
                    <div class="leading-tight min-w-0">
                        <h1 class="font-title-md text-[18px] font-bold">{{ __('ui.app_name') }}</h1>
                        <p class="font-label-sm text-[12px] text-white/80 truncate max-w-[10rem]" id="topbar-user-name">&nbsp;</p>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-stack-gap flex-grow justify-between">
                    <h2 class="font-headline-lg text-2xl font-bold text-on-surface">@yield('page-title', __('ui.app_name'))</h2>
                </div>
                <button type="button" id="topbar-locale-toggle" class="hero-chip md:!bg-primary-fixed/60 md:!text-primary md:!border-transparent font-label-sm text-label-sm">
                    <span class="material-symbols-outlined text-[16px]">language</span>
                    <span id="topbar-locale-label">AR/EN</span>
                </button>
            </div>
            <h2 class="md:hidden font-headline-lg-mobile text-[28px] leading-9 font-bold tracking-tight">@yield('page-title-mobile', __('ui.app_name'))</h2>
        </div>
    </header>

    <main class="flex-grow px-container-margin pb-container-margin pt-0 -mt-7 md:mt-0 md:px-8 md:py-6 max-w-7xl mx-auto w-full flex flex-col gap-6 relative z-10">

        @yield('content')
    </main>
</div>

@hasSection('fab')
    @yield('fab')
@endif

<!-- BottomNavBar (Mobile Only) -->
<?php $bottomNav = [
    ['key' => 'visits', 'href' => '/visits', 'icon' => 'home', 'label' => __('ui.nav.visits_short')],
    ['key' => 'create', 'href' => '/visits/create', 'icon' => 'add_circle', 'label' => __('ui.nav.new_visit_short')],
    ['key' => 'directory', 'href' => '/directory', 'icon' => 'settings', 'label' => __('ui.nav.directory_short')],
    ['key' => 'users', 'href' => '/users', 'icon' => 'manage_accounts', 'label' => __('ui.nav.users_short')],
]; ?>
<nav class="float-nav md:hidden">
    @foreach ($bottomNav as $item)
        @php($active = ($activeNav ?? '') === $item['key'])
        <a href="{{ $item['href'] }}" @if($active) aria-current="page" @endif class="pressable flex-1 flex flex-col items-center justify-center gap-0.5 py-1.5 {{ $active ? 'is-active' : 'text-on-surface-variant' }}">
            <span class="flex items-center justify-center h-7 w-12">
                <span class="material-symbols-outlined text-[24px] {{ $active ? 'filled' : '' }}">{{ $item['icon'] }}</span>
            </span>
            <span class="font-label-sm text-[11px] {{ $active ? 'font-bold' : '' }}">{{ $item['label'] }}</span>
        </a>
    @endforeach
    <a href="#" id="bottomnav-signout" class="pressable flex-1 flex flex-col items-center justify-center gap-0.5 py-1.5 text-on-surface-variant">
        <span class="flex items-center justify-center h-7 w-12">
            <span class="material-symbols-outlined text-[24px]">logout</span>
        </span>
        <span class="font-label-sm text-[11px]">{{ __('ui.nav.sign_out_short') }}</span>
    </a>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        Eftkad.requireAuth();

        var user = Eftkad.user();
        var nameEl = document.getElementById('nav-user-name');
        var typeEl = document.getElementById('nav-user-type');
        if (user && nameEl) {
            nameEl.textContent = user.name || user.membership_code || '';
        }
        if (user && typeEl) {
            typeEl.textContent = (user.type && user.type.name) ? user.type.name : '';
        }

        var topbarName = document.getElementById('topbar-user-name');
        if (user && topbarName) {
            topbarName.textContent = user.name || user.membership_code || '';
        }

        var localeLabel = Eftkad.locale() === 'ar' ? 'EN' : 'AR';
        var navLocaleLabel = document.getElementById('nav-locale-label');
        if (navLocaleLabel) navLocaleLabel.textContent = localeLabel;
        var topbarLocaleLabel = document.getElementById('topbar-locale-label');
        if (topbarLocaleLabel) topbarLocaleLabel.textContent = localeLabel;

        document.getElementById('nav-locale-toggle')?.addEventListener('click', function () { Eftkad.toggleLocale(); });
        document.getElementById('topbar-locale-toggle')?.addEventListener('click', function () { Eftkad.toggleLocale(); });

        function signOut(e) {
            e.preventDefault();
            Eftkad.logout();
        }
        document.getElementById('nav-signout')?.addEventListener('click', signOut);
        document.getElementById('bottomnav-signout')?.addEventListener('click', signOut);

        maybeShowInstallPrompt();
    });

    function showInstallModal(text) {
        var modal = document.getElementById('install-prompt');
        document.getElementById('install-prompt-text').textContent = text;
        modal.classList.remove('hidden');

        var confirmBtn = document.getElementById('install-confirm');
        // No native install flow available (iOS, or the browser never fired
        // beforeinstallprompt) - the text already carries manual steps, so
        // just let the user dismiss.
        confirmBtn.classList.toggle('hidden', !Eftkad.deferredInstallPrompt);

        function close() { modal.classList.add('hidden'); }

        document.getElementById('install-dismiss').addEventListener('click', close, { once: true });
        document.getElementById('install-later').addEventListener('click', close, { once: true });
        confirmBtn.addEventListener('click', async function () {
            if (!Eftkad.deferredInstallPrompt) { close(); return; }
            Eftkad.deferredInstallPrompt.prompt();
            await Eftkad.deferredInstallPrompt.userChoice;
            Eftkad.deferredInstallPrompt = null;
            close();
        }, { once: true });
    }

    function maybeShowInstallPrompt() {
        if (!sessionStorage.getItem(Eftkad.INSTALL_FLAG_KEY)) return;
        sessionStorage.removeItem(Eftkad.INSTALL_FLAG_KEY);

        if (Eftkad.isStandalone()) return;

        // beforeinstallprompt can fire after page load, so give it a short
        // moment before deciding what (if anything) to show.
        setTimeout(function () {
            if (Eftkad.deferredInstallPrompt) {
                showInstallModal(UI_TEXT.install_prompt.text_default);
            } else if (Eftkad.isIos()) {
                showInstallModal(UI_TEXT.install_prompt.text_ios);
            }
            // Otherwise the browser doesn't support installable PWAs (or already installed) - stay silent.
        }, 1200);
    }
</script>
@yield('scripts')
</body>
</html>
