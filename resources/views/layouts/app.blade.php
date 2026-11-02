<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
@php($uiSections = array_merge($uiSections ?? [], ['fg', 'install_prompt']))
@include('partials.head-assets')
<title>@yield('title', 'Eftkad')</title>
</head>
<body>
@include('partials.api-js')

@php($nav = [
    ['key' => 'visits', 'href' => '/visits', 'icon' => 'home', 'label' => __('ui.fg.nav.visits')],
    ['key' => 'create', 'href' => '/visits/create', 'icon' => 'plus', 'label' => __('ui.fg.nav.new_visit')],
    ['key' => 'directory', 'href' => '/directory', 'icon' => 'book', 'label' => __('ui.fg.nav.directory')],
    ['key' => 'users', 'href' => '/users', 'icon' => 'users', 'label' => __('ui.fg.nav.users')],
])

<div class="app-shell">
    <div class="sidebar-overlay" id="sidebar-overlay"></div>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand compact">
                <div class="brand-mark"><img src="/icons/icon-192.png?v=2" alt="{{ __('ui.app_name') }}" /></div>
                <div>
                    <div class="brand-name">Eftkad <span>افتقاد</span></div>
                </div>
            </div>
            <button type="button" class="icon-button mobile-only" id="sidebar-close" aria-label="Close"><x-icon name="close" /></button>
        </div>
        <nav class="sidebar-nav">
            @foreach ($nav as $item)
                <a href="{{ $item['href'] }}" class="{{ ($activeNav ?? '') === $item['key'] ? 'active' : '' }}">
                    <x-icon :name="$item['icon']" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
        <div class="sidebar-bottom">
            <div class="church-card">
                <div class="church-icon"><x-icon name="cross" /></div>
                <div>
                    <strong>{{ app()->getLocale() === 'ar' ? 'افتقاد' : 'Eftkad' }}</strong>
                    <span>{{ __('ui.fg.subtitle') }}</span>
                </div>
            </div>
            <button type="button" class="signout-link" data-signout>
                <x-icon name="logout" />
                <span>{{ __('ui.fg.nav.sign_out') }}</span>
            </button>
        </div>
    </aside>

    <div class="main-shell">
        <header class="topbar">
            <div class="mobile-only">
                <button type="button" class="icon-button" id="menu-open" aria-label="{{ __('ui.fg.menu') }}"><x-icon name="menu" /></button>
            </div>
            <div class="topbar-title">
                <span class="topbar-kicker" id="topbar-greeting">{{ __('ui.fg.greeting_anon') }}</span>
                <strong>{{ __('ui.fg.welcome') }}</strong>
            </div>
            <div class="topbar-actions">
                <div class="language-toggle">
                    <button type="button" data-lang="ar">ع</button>
                    <button type="button" data-lang="en">EN</button>
                </div>
                <button type="button" class="icon-button" data-theme-toggle aria-label="{{ __('ui.fg.theme') }}"><span data-theme-icon style="display:contents"></span></button>
                <div class="profile-button">
                    <span id="profile-initials">··</span>
                    <div class="desktop-only">
                        <strong id="profile-name">&nbsp;</strong>
                        <small id="profile-type">&nbsp;</small>
                    </div>
                </div>
            </div>
        </header>

        <div class="install-banner hidden" id="install-banner">
            <div class="install-icon"><img src="/icons/icon-192.png?v=2" alt="" /></div>
            <div class="install-copy">
                <strong>{{ __('ui.fg.install.title') }}</strong>
                <span id="install-text">{{ __('ui.fg.install.text') }}</span>
            </div>
            <button type="button" class="button button-secondary" id="install-confirm"><span>{{ __('ui.fg.install.action') }}</span></button>
            <button type="button" class="icon-button" id="install-dismiss" aria-label="Close"><x-icon name="close" /></button>
        </div>

        <main>
            @yield('content')
        </main>
    </div>

    <nav class="bottom-nav">
        @foreach ($nav as $item)
            @php($active = ($activeNav ?? '') === $item['key'])
            <a href="{{ $item['href'] }}" class="{{ $active ? 'active' : '' }} {{ $item['key'] === 'create' ? 'new-action' : '' }}" @if($active) aria-current="page" @endif>
                <span class="bottom-icon"><x-icon :name="$item['icon']" /></span>
                <small>{{ $item['label'] }}</small>
            </a>
        @endforeach
        <button type="button" data-signout>
            <span class="bottom-icon"><x-icon name="logout" /></span>
            <small>{{ __('ui.fg.nav.sign_out') }}</small>
        </button>
    </nav>
</div>

<div id="toast-root"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;

        Eftkad.initChrome();

        var user = Eftkad.user();
        if (user) {
            var name = user.name || user.membership_code || '';
            var first = name.trim().split(/\s+/)[0];
            if (first) {
                document.getElementById('topbar-greeting').textContent = UI_TEXT.fg.greeting.replace(':name', first);
            }
            document.getElementById('profile-initials').textContent = Eftkad.initials(name);
            document.getElementById('profile-name').textContent = name;
            document.getElementById('profile-type').textContent = (user.type && user.type.name) ? user.type.name : '';
        }

        document.querySelectorAll('[data-signout]').forEach(function (b) {
            b.addEventListener('click', function (e) { e.preventDefault(); Eftkad.logout(); });
        });

        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebar-overlay');
        function setMenu(open) {
            sidebar.classList.toggle('show', open);
            overlay.classList.toggle('show', open);
        }
        document.getElementById('menu-open').addEventListener('click', function () { setMenu(true); });
        document.getElementById('sidebar-close').addEventListener('click', function () { setMenu(false); });
        overlay.addEventListener('click', function () { setMenu(false); });

        setupInstallBanner();

        try {
            var msg = sessionStorage.getItem('eftkad_toast');
            if (msg) { sessionStorage.removeItem('eftkad_toast'); Eftkad.toast(msg); }
        } catch (e) {}
    });

    // Toast: Eftkad.toast('message') shows the green confirmation pill for a moment.
    Eftkad.toast = function (message) {
        var root = document.getElementById('toast-root');
        root.innerHTML = '<div class="toast"><span>' + Eftkad.icon('check', 17) + '</span>' + Eftkad.esc(message) + '</div>';
        setTimeout(function () { root.innerHTML = ''; }, 2400);
    };

    // Install banner: shown when the browser can install the PWA (or on iOS, with
    // manual steps) and the app is not already running standalone.
    function setupInstallBanner() {
        var DISMISS_KEY = 'eftkad_install_dismissed';
        try { if (localStorage.getItem(DISMISS_KEY)) return; } catch (e) {}
        if (Eftkad.isStandalone()) return;

        var banner = document.getElementById('install-banner');
        var text = document.getElementById('install-text');
        var confirmBtn = document.getElementById('install-confirm');

        function show() {
            banner.classList.remove('hidden');
        }

        document.getElementById('install-dismiss').addEventListener('click', function () {
            banner.classList.add('hidden');
            try { localStorage.setItem(DISMISS_KEY, '1'); } catch (e) {}
        });
        confirmBtn.addEventListener('click', async function () {
            // No native prompt (iOS, or the browser hasn't offered one): show the manual steps instead.
            if (!Eftkad.deferredInstallPrompt) {
                text.textContent = Eftkad.isIos() ? UI_TEXT.install_prompt.text_ios : UI_TEXT.fg.install.manual;
                text.parentElement.querySelector('span').style.display = 'block';
                return;
            }
            Eftkad.deferredInstallPrompt.prompt();
            await Eftkad.deferredInstallPrompt.userChoice;
            Eftkad.deferredInstallPrompt = null;
            banner.classList.add('hidden');
        });

        // beforeinstallprompt may fire after load, so wait a moment before deciding.
        setTimeout(show, 1200);
        window.addEventListener('beforeinstallprompt', function () { setTimeout(show, 0); });
    }
</script>
@yield('scripts')
</body>
</html>
