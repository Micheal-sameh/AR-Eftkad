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
    ['key' => 'home', 'href' => '/visits', 'icon' => 'home', 'label' => __('ui.fg.nav.home')],
    ['key' => 'visits', 'href' => '/visits/all', 'icon' => 'calendar', 'label' => __('ui.fg.nav.visits')],
    ['key' => 'create', 'href' => '/visits/create', 'icon' => 'plus', 'label' => __('ui.fg.nav.new_visit')],
    ['key' => 'directory', 'href' => '/directory', 'icon' => 'book', 'label' => __('ui.fg.nav.directory')],
    ['key' => 'users', 'href' => '/users', 'icon' => 'users', 'label' => __('ui.fg.nav.users'), 'managers' => true],
])
{{-- Phone bottom bar: Home sits in the middle (Visits and New on one side, Directory and the rest on the other). --}}
@php($bottomNav = collect($nav)->sortBy(fn ($item) => ['visits' => 0, 'create' => 1, 'home' => 2, 'directory' => 3, 'users' => 4][$item['key']])->values()->all())

<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand compact">
                <div class="brand-mark"><img src="/icons/icon-192.png?v=2" alt="{{ __('ui.app_name') }}" /></div>
                <div>
                    <div class="brand-name">Eftkad <span>افتقاد</span></div>
                </div>
            </div>
        </div>
        <nav class="sidebar-nav">
            @foreach ($nav as $item)
                <a href="{{ $item['href'] }}" data-nav-key="{{ $item['key'] }}" class="{{ ($activeNav ?? '') === $item['key'] ? 'active' : '' }} {{ ! empty($item['managers']) ? 'hidden' : '' }}" @if(! empty($item['managers'])) data-managers-only @endif>
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
            <div class="topbar-brand" aria-hidden="true"><img class="brand-logo" src="/icons/icon-192.png?v=2" alt="" width="40" height="40" />{{ app()->getLocale() === 'ar' ? 'افتقاد' : 'Eftkad' }}</div>
            <div class="topbar-title">
                <span class="topbar-kicker" id="topbar-greeting">{{ __('ui.fg.greeting_anon') }}</span>
                <strong>{{ __('ui.fg.welcome') }}</strong>
            </div>
            <div class="topbar-actions">
                <div class="language-toggle">
                    <button type="button" data-lang="ar">ع</button>
                    <button type="button" data-lang="en">EN</button>
                </div>
                <button type="button" class="icon-button font-size-button" data-font-toggle aria-label="{{ __('ui.fg.font_size') }}" title="{{ __('ui.fg.font_size') }}">Aa</button>
                <button type="button" class="icon-button" data-theme-toggle aria-label="{{ __('ui.fg.theme') }}"><span data-theme-icon style="display:contents"></span></button>
                <a href="/profile" class="profile-button" aria-label="{{ __('ui.fg.profile.title') }}" title="{{ __('ui.fg.profile.title') }}">
                    <span id="profile-initials"><x-icon name="user" :size="20" /></span>
                    <div class="desktop-only">
                        <strong id="profile-name">&nbsp;</strong>
                        <small id="profile-type">&nbsp;</small>
                    </div>
                </a>
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
        @foreach ($bottomNav as $item)
            @php($active = ($activeNav ?? '') === $item['key'])
            <a href="{{ $item['href'] }}" data-nav-key="{{ $item['key'] }}" class="{{ $active ? 'active' : '' }} {{ $item['key'] === 'create' ? 'new-action' : '' }} {{ ! empty($item['managers']) ? 'hidden' : '' }}" @if(! empty($item['managers'])) data-managers-only @endif @if($active) aria-current="page" @endif>
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

<div class="modal-wrap hidden" id="signout-modal" role="dialog" aria-modal="true" aria-labelledby="signout-title">
    <div class="modal-backdrop" id="signout-backdrop"></div>
    <div class="modal signout-modal">
        <div class="signout-icon"><x-icon name="logout" :size="24" /></div>
        <div class="section-title" id="signout-title">{{ __('ui.fg.signout_title') }}</div>
        <p class="signout-text">{{ __('ui.fg.signout_text') }}</p>
        <div class="modal-actions">
            <button type="button" class="button button-ghost" id="signout-cancel"><span>{{ __('ui.fg.cancel') }}</span></button>
            <button type="button" class="button button-danger" id="signout-confirm">
                <x-icon name="logout" :size="18" />
                <span>{{ __('ui.fg.nav.sign_out') }}</span>
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;

        Eftkad.initChrome();

        // Pick up a text size changed on another device: refresh it from the account once per tab session.
        try {
            if (!sessionStorage.getItem('eftkad_font_synced')) {
                sessionStorage.setItem('eftkad_font_synced', '1');
                Eftkad.api('/user').then(function (res) {
                    var me = res && res.ok !== false && res.data && (res.data.data || res.data);
                    if (me && typeof me.font_size === 'number') {
                        Eftkad.applyFontSize(me.font_size);
                        var u = Eftkad.user();
                        if (u) { u.font_size = me.font_size; u.role = me.role; localStorage.setItem(Eftkad.USER_KEY, JSON.stringify(u)); }
                    }
                }).catch(function () {});
            }
        } catch (e) {}

        var user = Eftkad.user();
        // The users page is for admins and above; plain users never see the link.
        if (user && user.role && ['admin', 'superadmin', 'owner'].indexOf(user.role.value) !== -1) {
            document.querySelectorAll('[data-managers-only]').forEach(function (el) { el.classList.remove('hidden'); });
        }
        // Badge on the Home tab: how many visits are new for me (assigned or shared, not opened yet).
        Eftkad.api('/eftkads/home').then(function (res) {
            var d = res && res.ok && res.data && res.data.data;
            var n = d && d.new ? d.new.length : 0;
            if (!n) return;
            document.querySelectorAll('[data-nav-key="home"]').forEach(function (a) {
                (a.querySelector('.bottom-icon') || a).insertAdjacentHTML('beforeend', '<span class="nav-badge">' + (n > 9 ? '9+' : n) + '</span>');
            });
        }).catch(function () {});

        if (user) {
            var name = user.name || user.membership_code || '';
            var first = name.trim().split(/\s+/)[0];
            if (first) {
                document.getElementById('topbar-greeting').textContent = UI_TEXT.fg.greeting.replace(':name', first);
            }
            document.getElementById('profile-name').textContent = name;
            document.getElementById('profile-type').textContent = (user.type && user.type.name) ? user.type.name : '';
        }

        // Signing out always asks first (sidebar, bottom bar and profile button all land here).
        var signoutModal = document.getElementById('signout-modal');
        function setSignoutModal(open) {
            signoutModal.classList.toggle('hidden', !open);
            if (open) document.getElementById('signout-cancel').focus();
        }
        document.getElementById('signout-backdrop').addEventListener('click', function () { setSignoutModal(false); });
        document.getElementById('signout-cancel').addEventListener('click', function () { setSignoutModal(false); });
        document.getElementById('signout-confirm').addEventListener('click', function () {
            this.disabled = true;
            Eftkad.logout();
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setSignoutModal(false); });

        document.querySelectorAll('[data-signout]').forEach(function (b) {
            b.addEventListener('click', function (e) { e.preventDefault(); setSignoutModal(true); });
        });

        setupInstallBanner();
        Eftkad.enhanceSelects();
        Eftkad.initPullToRefresh();

        try {
            var msg = sessionStorage.getItem('eftkad_toast');
            if (msg) { sessionStorage.removeItem('eftkad_toast'); Eftkad.toast(msg); }
        } catch (e) {}
    });

    // Pull-to-refresh: drag down while the page is at the very top, release past the threshold to
    // reload. The browser's own gesture is disabled (overscroll-behavior), so this replaces it.
    // A page can supply its own refresh by setting window.EftkadRefresh = function () { ... }.
    Eftkad.initPullToRefresh = function () {
        // Native app only: the Android shell's web view has the Capacitor bridge; a phone's
        // browser (which has its own refresh) and the installed PWA do not.
        var native = window.Capacitor && typeof window.Capacitor.isNativePlatform === 'function' && window.Capacitor.isNativePlatform();
        if (!native) return;

        var THRESHOLD = 70, MAX = 120;
        var ring = document.createElement('div');
        ring.className = 'ptr';
        ring.innerHTML = '<span class="ptr-ring"></span>';
        document.body.appendChild(ring);
        var spinner = ring.firstChild;

        var startY = 0, startX = 0, pull = 0, tracking = false, busy = false, armed = false;

        function blocked(target) {
            return target.closest('.select-menu, .sheet-wrap.open, .modal-wrap:not(.hidden), textarea, input');
        }
        function render(p) {
            ring.style.transform = 'translateY(' + (p - 52) + 'px)';
            ring.style.opacity = Math.min(1, p / 40);
            spinner.style.transform = 'rotate(' + (p * 4) + 'deg)';
        }
        function reset() {
            ring.classList.remove('is-loading');
            ring.classList.add('is-settling');
            render(0);
            setTimeout(function () { ring.classList.remove('is-settling'); ring.style.opacity = 0; }, 250);
        }

        document.addEventListener('touchstart', function (e) {
            if (busy || e.touches.length !== 1 || window.scrollY > 0 || blocked(e.target)) { tracking = false; return; }
            tracking = true; armed = false; pull = 0;
            startY = e.touches[0].clientY; startX = e.touches[0].clientX;
            ring.classList.remove('is-settling');
        }, { passive: true });

        document.addEventListener('touchmove', function (e) {
            if (!tracking || busy) return;
            var dy = e.touches[0].clientY - startY;
            var dx = e.touches[0].clientX - startX;
            if (dy <= 0 || Math.abs(dx) > dy || window.scrollY > 0) { if (pull) { pull = 0; render(0); } if (dy <= 0) tracking = false; return; }
            if (e.cancelable) e.preventDefault();
            pull = Math.min(MAX, dy * 0.5);
            render(pull);
            armed = pull >= THRESHOLD;
        }, { passive: false });

        function end() {
            if (!tracking) return;
            tracking = false;
            if (pull >= THRESHOLD && !busy) {
                busy = true;
                ring.classList.add('is-loading', 'is-settling');
                render(THRESHOLD * 0.8);
                if (typeof window.EftkadRefresh === 'function') {
                    Promise.resolve(window.EftkadRefresh()).catch(function () {}).then(function () { busy = false; reset(); });
                } else {
                    setTimeout(function () { window.location.reload(); }, 250);
                }
            } else if (pull > 0) {
                reset();
            }
            pull = 0;
        }
        document.addEventListener('touchend', end, { passive: true });
        document.addEventListener('touchcancel', end, { passive: true });
    };

    // Custom dropdowns. The browser's own <select> list can't be themed (white popup on the
    // dark theme, huge font on phones), so every `.input-shell select` gets a themed trigger
    // and menu. The real <select> stays in the DOM and stays the source of truth: reading or
    // setting .value, `change` listeners and form code all keep working untouched.
    Eftkad.enhanceSelects = function () {
        var menu = null;
        var openFor = null;

        function closeMenu() {
            if (!menu) return;
            menu.remove();
            menu = null;
            if (openFor) { openFor.shell.classList.remove('is-open'); openFor.trigger.setAttribute('aria-expanded', 'false'); }
            openFor = null;
        }

        function labelOf(select) {
            var opt = select.options[select.selectedIndex];
            return opt ? opt.textContent : '';
        }

        function sync(c) {
            var opt = c.select.options[c.select.selectedIndex];
            var empty = !opt || opt.value === '';
            c.trigger.textContent = labelOf(c.select);
            c.trigger.classList.toggle('is-placeholder', empty);
        }

        // Sits under the field (or above it when there is no room); re-run on scroll/resize so it follows the field.
        function place() {
            if (!menu || !openFor) return;
            var r = openFor.shell.getBoundingClientRect();
            var below = window.innerHeight - r.bottom - 12;
            var h = Math.min(menu.scrollHeight, 256);
            var up = below < h && r.top > below;
            menu.style.minWidth = r.width + 'px';
            menu.style.maxWidth = Math.max(r.width, 280) + 'px';
            menu.style.maxHeight = Math.max(120, Math.min(256, up ? r.top - 12 : below)) + 'px';
            var left = document.documentElement.dir === 'rtl' ? r.right - menu.offsetWidth : r.left;
            menu.style.left = Math.max(8, Math.min(left, window.innerWidth - menu.offsetWidth - 8)) + 'px';
            menu.style.top = up ? (r.top - menu.offsetHeight - 6) + 'px' : (r.bottom + 6) + 'px';
        }

        function openMenu(c) {
            closeMenu();
            if (c.select.disabled) return;
            menu = document.createElement('div');
            menu.className = 'select-menu';
            menu.setAttribute('role', 'listbox');
            Array.prototype.forEach.call(c.select.options, function (o, i) {
                var item = document.createElement('div');
                var chosen = i === c.select.selectedIndex;
                item.className = 'select-option' + (chosen ? ' selected' : '') + (o.disabled ? ' disabled' : '') + (o.value === '' ? ' is-placeholder' : '');
                item.setAttribute('role', 'option');
                item.dataset.index = i;
                item.innerHTML = '<span></span>' + (chosen ? Eftkad.icon('check', 16) : '');
                item.firstChild.textContent = o.textContent;
                menu.appendChild(item);
            });
            document.body.appendChild(menu);

            openFor = c;
            place();

            var sel = menu.querySelector('.selected');
            // Scroll inside the menu only; scrollIntoView would scroll the page and trip the close-on-scroll rule below.
            if (sel) menu.scrollTop = Math.max(0, sel.offsetTop - menu.clientHeight / 2 + sel.offsetHeight / 2);

            menu.addEventListener('mousedown', function (e) { e.preventDefault(); });
            menu.addEventListener('click', function (e) {
                var item = e.target.closest('.select-option');
                if (!item || item.classList.contains('disabled')) return;
                c.select.selectedIndex = parseInt(item.dataset.index, 10);
                c.select.dispatchEvent(new Event('change', { bubbles: true }));
                sync(c);
                closeMenu();
                c.trigger.focus();
            });

            c.shell.classList.add('is-open');
            c.trigger.setAttribute('aria-expanded', 'true');
        }

        function move(c, dir) {
            var s = c.select, i = s.selectedIndex;
            do { i += dir; } while (i >= 0 && i < s.options.length && s.options[i].disabled);
            if (i < 0 || i >= s.options.length) return;
            s.selectedIndex = i;
            s.dispatchEvent(new Event('change', { bubbles: true }));
            sync(c);
        }

        function enhance(select) {
            if (select.dataset.enhanced) return;
            var shell = select.closest('.input-shell');
            if (!shell) return;
            select.dataset.enhanced = '1';
            shell.classList.add('is-enhanced');

            var trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'select-trigger';
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-expanded', 'false');
            select.tabIndex = -1;
            select.setAttribute('aria-hidden', 'true');
            shell.insertBefore(trigger, select);

            var c = { select: select, shell: shell, trigger: trigger };
            sync(c);

            trigger.addEventListener('click', function () { openFor === c ? closeMenu() : openMenu(c); });
            trigger.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') { e.preventDefault(); move(c, e.key === 'ArrowDown' ? 1 : -1); }
                else if (e.key === 'Escape') closeMenu();
            });

            // Keep the trigger in step with the real select: options added later (API data),
            // and .value set from code (which does not fire any event).
            new MutationObserver(function () { sync(c); }).observe(select, { childList: true, subtree: true, attributes: true });
            var desc = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');
            Object.defineProperty(select, 'value', {
                get: function () { return desc.get.call(this); },
                set: function (v) { desc.set.call(this, v); sync(c); },
            });
            select.addEventListener('change', function () { sync(c); });
        }

        document.querySelectorAll('.input-shell select').forEach(enhance);
        document.addEventListener('mousedown', function (e) {
            if (menu && !menu.contains(e.target) && !(openFor && openFor.shell.contains(e.target))) closeMenu();
        });
        window.addEventListener('resize', place);
        window.addEventListener('scroll', function (e) { if (menu && !menu.contains(e.target)) place(); }, true);
    };

    // Toast: Eftkad.toast('message') shows the green confirmation pill for a moment.
    Eftkad.toast = function (message) {
        var root = document.getElementById('toast-root');
        root.innerHTML = '<div class="toast"><span>' + Eftkad.icon('check', 17) + '</span>' + Eftkad.esc(message) + '</div>';
        setTimeout(function () { root.innerHTML = ''; }, 2400);
    };

    // Install banner: shown when the browser can install the PWA (or on iOS, with
    // manual steps) and the app is not already running standalone.
    function setupInstallBanner() {
        // Temporarily off: set to true to show the banner again.
        var INSTALL_BANNER_ENABLED = false;
        if (!INSTALL_BANNER_ENABLED) return;

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
