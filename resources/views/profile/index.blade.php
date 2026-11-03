@extends('layouts.app')

@php($uiSections = ['common'])

@php($activeNav = 'profile')

@section('title', __('ui.fg.profile.title') . ' - Eftkad')

@section('content')
<div class="page-content form-page">
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ __('ui.fg.profile.eyebrow') }}</div>
            <div class="page-title">{{ __('ui.fg.profile.title') }}</div>
            <p>{{ __('ui.fg.profile.subtitle') }}</p>
        </div>
    </div>

    <div id="profile-loading" class="is-loading-block"><span class="spinner"></span></div>
    <div id="profile-error" class="state-note is-error hidden">{{ __('ui.fg.profile.error') }}</div>

    <div class="form-layout hidden" id="profile-content">
        <section class="form-section">
            <div class="modal-profile">
                <div class="person-avatar large" id="p-initials">··</div>
                <div><strong id="p-name">—</strong><span id="p-code">—</span></div>
            </div>
            <div class="form-section-heading">
                <span><x-icon name="user" :size="18" /></span>
                <div><strong>{{ __('ui.fg.profile.details') }}</strong></div>
            </div>
            <div id="p-details" class="status-grid"></div>
        </section>

        <section class="form-section">
            <div class="form-section-heading">
                <span><x-icon name="menu" :size="18" /></span>
                <div><strong>{{ __('ui.fg.profile.settings') }}</strong></div>
            </div>

            <div class="field-label">{{ __('ui.fg.profile.language') }}</div>
            <div class="chips form-chips" id="p-lang" style="margin-bottom: 1.1rem">
                <button type="button" class="chip" data-lang="ar"><span>العربية</span></button>
                <button type="button" class="chip" data-lang="en"><span>English</span></button>
            </div>

            <div class="field-label">{{ __('ui.fg.profile.theme') }}</div>
            <div class="chips form-chips" id="p-theme">
                <button type="button" class="chip" data-theme="light"><x-icon name="sun" :size="16" /><span>{{ __('ui.fg.profile.light') }}</span></button>
                <button type="button" class="chip" data-theme="dark"><x-icon name="moon" :size="16" /><span>{{ __('ui.fg.profile.dark') }}</span></button>
            </div>
        </section>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const $ = function (id) { return document.getElementById(id); };
    const P = UI_TEXT.fg.profile;

    function row(label, value) {
        const shown = value ? Eftkad.esc(value) : '<span style="color:var(--ink-faint)">' + Eftkad.esc(P.not_set) + '</span>';
        return '<div class="status-item"><span>' + Eftkad.esc(label) + '</span><strong dir="auto">' + shown + '</strong></div>';
    }

    function renderProfile(u) {
        $('p-initials').textContent = Eftkad.initials(u.name);
        $('p-name').textContent = u.name || UI_TEXT.common.dash;
        $('p-code').textContent = u.membership_code || UI_TEXT.common.dash;
        $('p-details').innerHTML =
            row(P.name, u.name) +
            row(P.email, u.email) +
            row(P.phone, u.phone) +
            row(P.code, u.membership_code) +
            row(P.type, u.type && u.type.name) +
            row(P.role, u.role && u.role.name);
    }

    // Highlights the active language / theme chip.
    function markChoices() {
        const dark = document.documentElement.classList.contains('dark');
        document.querySelectorAll('#p-lang .chip').forEach(function (c) { c.classList.toggle('selected', c.dataset.lang === Eftkad.locale()); });
        document.querySelectorAll('#p-theme .chip').forEach(function (c) { c.classList.toggle('selected', (c.dataset.theme === 'dark') === dark); });
    }

    async function loadProfile() {
        const res = await Eftkad.api('/user');
        $('profile-loading').classList.add('hidden');
        const u = res && res.ok !== false && res.data && (res.data.data || res.data);
        if (!u || !u.id) { $('profile-error').classList.remove('hidden'); return; }
        renderProfile(u);
        $('profile-content').classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;
        loadProfile();
        markChoices();

        $('p-lang').addEventListener('click', function (e) {
            const b = e.target.closest('[data-lang]');
            if (b) Eftkad.setLocale(b.dataset.lang); // reloads the page in the new language
        });
        $('p-theme').addEventListener('click', function (e) {
            const b = e.target.closest('[data-theme]');
            if (!b) return;
            const dark = document.documentElement.classList.contains('dark');
            if ((b.dataset.theme === 'dark') !== dark) Eftkad.toggleTheme();
            markChoices();
        });
        // The topbar theme button changes the theme too: keep the chips in step.
        document.querySelectorAll('[data-theme-toggle]').forEach(function (b) { b.addEventListener('click', markChoices); });
    });
</script>
@endsection
