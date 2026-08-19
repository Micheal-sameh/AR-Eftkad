<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
@include('partials.head-assets')
<title>@yield('title', 'Eftkad')</title>
</head>
<body class="font-body-md min-h-screen flex flex-col md:flex-row pb-20 md:pb-0">
@include('partials.api-js')

<!-- Install App Prompt (shown once, right after login) -->
<div id="install-prompt" class="hidden fixed inset-0 z-[60] flex items-end md:items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-sm bg-surface-container-lowest rounded-2xl shadow-2xl p-card-padding relative">
        <button type="button" id="install-dismiss" class="absolute top-3 left-3 p-1 rounded-full text-on-surface-variant hover:bg-surface-container transition-colors">
            <span class="material-symbols-outlined">close</span>
        </button>
        <div class="flex flex-col items-center text-center gap-3">
            <img src="/icons/icon-192.png" alt="إفتقاد" class="w-16 h-16 rounded-2xl shadow-md" />
            <h3 class="font-title-md text-title-md text-on-surface">ثبّت تطبيق إفتقاد</h3>
            <p class="font-body-md text-body-md text-on-surface-variant" id="install-prompt-text">
                أضف التطبيق إلى شاشتك الرئيسية للوصول السريع والعمل بدون اتصال.
            </p>
            <div class="flex gap-3 w-full mt-2">
                <button type="button" id="install-later" class="flex-1 px-4 py-3 rounded-lg border border-outline-variant text-on-surface-variant font-title-md text-title-md hover:bg-surface-container-low transition-colors">
                    لاحقاً
                </button>
                <button type="button" id="install-confirm" class="flex-1 px-4 py-3 rounded-lg bg-primary text-on-primary font-title-md text-title-md hover:bg-surface-tint transition-colors">
                    تثبيت
                </button>
            </div>
        </div>
    </div>
</div>

<!-- NavigationDrawer (Desktop) -->
<nav class="hidden md:flex flex-col h-full p-gutter fixed right-0 top-0 bg-surface border-l border-outline-variant w-80 z-40 transition-all duration-200 ease-in-out">
    <div class="mb-gutter">
        <h1 class="font-headline-lg text-primary">Eftkad</h1>
    </div>
    <div class="flex items-center gap-stack-gap mb-8">
        <div class="w-12 h-12 rounded-full overflow-hidden bg-surface-container-high flex-shrink-0 border border-outline-variant flex items-center justify-center">
            <span class="material-symbols-outlined text-outline">person</span>
        </div>
        <div>
            <p class="font-title-md text-title-md text-on-surface" id="nav-user-name">&nbsp;</p>
            <p class="font-label-sm text-label-sm text-tertiary" id="nav-user-type">&nbsp;</p>
        </div>
    </div>
    <ul class="flex flex-col gap-2 flex-grow">
        <li>
            <a href="/visits" class="flex items-center gap-stack-gap px-4 py-3 rounded-lg transition-all {{ ($activeNav ?? '') === 'visits' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined">church</span>
                <span class="font-title-md text-title-md">الزيارات الرعوية</span>
            </a>
        </li>
        <li>
            <a href="/visits/create" class="flex items-center gap-stack-gap px-4 py-3 rounded-lg transition-all {{ ($activeNav ?? '') === 'create' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined">edit_calendar</span>
                <span class="font-title-md text-title-md">تسجيل زيارة جديدة</span>
            </a>
        </li>
        <li>
            <a href="/directory" class="flex items-center gap-stack-gap px-4 py-3 rounded-lg transition-all {{ ($activeNav ?? '') === 'directory' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined">menu_book</span>
                <span class="font-title-md text-title-md">دليل الخدمة</span>
            </a>
        </li>
        <li>
            <a href="/users" class="flex items-center gap-stack-gap px-4 py-3 rounded-lg transition-all {{ ($activeNav ?? '') === 'users' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined">manage_accounts</span>
                <span class="font-title-md text-title-md">إدارة المستخدمين</span>
            </a>
        </li>
    </ul>
    <div class="mt-auto">
        <button type="button" id="nav-locale-toggle" class="w-full flex items-center gap-stack-gap text-on-surface-variant px-4 py-3 hover:bg-surface-container-low rounded-lg transition-all">
            <span class="material-symbols-outlined">language</span>
            <span class="font-title-md text-title-md" id="nav-locale-label">AR/EN</span>
        </button>
        <button type="button" id="nav-signout" class="w-full flex items-center gap-stack-gap text-on-surface-variant px-4 py-3 hover:bg-surface-container-low rounded-lg transition-all">
            <span class="material-symbols-outlined">door_open</span>
            <span class="font-title-md text-title-md">تسجيل الخروج</span>
        </button>
    </div>
</nav>

<!-- Main Content Area -->
<div class="flex-grow flex flex-col md:mr-80 w-full relative">
    <!-- TopAppBar (Mobile & Desktop Header Area) -->
    <header class="flex justify-between items-center w-full px-container-margin py-unit max-w-7xl mx-auto bg-surface border-b border-outline-variant md:border-none sticky top-0 z-30 md:static md:bg-transparent">
        <div class="flex items-center gap-stack-gap md:hidden">
            <div class="w-10 h-10 rounded-full overflow-hidden bg-surface-container-high border border-outline-variant flex items-center justify-center">
                <span class="material-symbols-outlined text-outline">person</span>
            </div>
            <h1 class="font-title-md text-title-md font-semibold text-primary">Eftkad</h1>
        </div>
        <div class="hidden md:flex items-center gap-stack-gap flex-grow justify-between">
            <h2 class="font-display-lg text-display-lg text-on-surface">@yield('page-title', 'إفتقاد')</h2>
        </div>
        <button type="button" id="topbar-locale-toggle" class="text-primary hover:bg-surface-container p-2 rounded-full transition-colors font-label-sm text-label-sm">
            AR/EN
        </button>
    </header>

    <main class="flex-grow p-container-margin max-w-7xl mx-auto w-full flex flex-col gap-6">
        <h2 class="font-headline-lg-mobile text-headline-lg-mobile md:hidden text-on-surface mb-2">@yield('page-title-mobile', 'إفتقاد')</h2>

        @yield('content')
    </main>
</div>

@hasSection('fab')
    @yield('fab')
@endif

<!-- BottomNavBar (Mobile Only) -->
<nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 pb-4 pt-2 md:hidden bg-surface shadow-[0px_-4px_20px_rgba(0,0,0,0.05)] rounded-t-xl">
    <a href="/visits" class="flex flex-col items-center justify-center px-3 py-1 transition-transform duration-200 {{ ($activeNav ?? '') === 'visits' ? 'bg-primary-container text-on-primary-container rounded-xl scale-90' : 'text-on-surface-variant hover:text-primary' }}">
        <span class="material-symbols-outlined {{ ($activeNav ?? '') === 'visits' ? 'filled' : '' }}">home</span>
        <span class="font-label-sm text-label-sm mt-1 {{ ($activeNav ?? '') === 'visits' ? 'font-bold' : '' }}">الزيارات</span>
    </a>
    <a href="/visits/create" class="flex flex-col items-center justify-center px-3 py-1 transition-transform duration-200 {{ ($activeNav ?? '') === 'create' ? 'bg-primary-container text-on-primary-container rounded-xl scale-90' : 'text-on-surface-variant hover:text-primary' }}">
        <span class="material-symbols-outlined {{ ($activeNav ?? '') === 'create' ? 'filled' : '' }}">add_circle</span>
        <span class="font-label-sm text-label-sm mt-1 {{ ($activeNav ?? '') === 'create' ? 'font-bold' : '' }}">جديد</span>
    </a>
    <a href="/directory" class="flex flex-col items-center justify-center px-3 py-1 transition-transform duration-200 {{ ($activeNav ?? '') === 'directory' ? 'bg-primary-container text-on-primary-container rounded-xl scale-90' : 'text-on-surface-variant hover:text-primary' }}">
        <span class="material-symbols-outlined {{ ($activeNav ?? '') === 'directory' ? 'filled' : '' }}">settings</span>
        <span class="font-label-sm text-label-sm mt-1 {{ ($activeNav ?? '') === 'directory' ? 'font-bold' : '' }}">أدوات</span>
    </a>
    <a href="/users" class="flex flex-col items-center justify-center px-3 py-1 transition-transform duration-200 {{ ($activeNav ?? '') === 'users' ? 'bg-primary-container text-on-primary-container rounded-xl scale-90' : 'text-on-surface-variant hover:text-primary' }}">
        <span class="material-symbols-outlined {{ ($activeNav ?? '') === 'users' ? 'filled' : '' }}">manage_accounts</span>
        <span class="font-label-sm text-label-sm mt-1 {{ ($activeNav ?? '') === 'users' ? 'font-bold' : '' }}">مستخدمين</span>
    </a>
    <a href="#" id="bottomnav-signout" class="flex flex-col items-center justify-center text-on-surface-variant px-3 py-1 hover:text-primary transition-colors">
        <span class="material-symbols-outlined">logout</span>
        <span class="font-label-sm text-label-sm mt-1">خروج</span>
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

        var localeLabel = Eftkad.locale() === 'ar' ? 'EN' : 'AR';
        var navLocaleLabel = document.getElementById('nav-locale-label');
        if (navLocaleLabel) navLocaleLabel.textContent = localeLabel;

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
                showInstallModal('أضف التطبيق إلى شاشتك الرئيسية للوصول السريع والعمل بدون اتصال.');
            } else if (Eftkad.isIos()) {
                showInstallModal('لتثبيت التطبيق: اضغط على زر المشاركة ثم "إضافة إلى الشاشة الرئيسية".');
            }
            // Otherwise the browser doesn't support installable PWAs (or already installed) - stay silent.
        }, 1200);
    }
</script>
@yield('scripts')
</body>
</html>
