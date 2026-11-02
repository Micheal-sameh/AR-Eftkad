@extends('layouts.app')

@php($uiSections = [])

@php($activeNav = 'directory')

@section('title', __('ui.fg.directory.title') . ' - Eftkad')

@section('content')
<div class="page-content">
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ __('ui.fg.directory.eyebrow') }}</div>
            <div class="page-title">{{ __('ui.fg.directory.title') }}</div>
            <p>{{ __('ui.fg.directory.subtitle') }}</p>
        </div>
    </div>

    <div class="search-bar">
        <x-icon name="search" :size="19" />
        <input id="directory-search" aria-label="{{ __('ui.fg.directory.search') }}" placeholder="{{ __('ui.fg.directory.search') }}" />
        <kbd>⌘ K</kbd>
    </div>

    <div class="tabs">
        <button type="button" class="active" data-tab="father">{{ __('ui.fg.directory.fathers') }}<span id="count-father">0</span></button>
        <button type="button" data-tab="servant">{{ __('ui.fg.directory.servants') }}<span id="count-servant">0</span></button>
    </div>

    <div id="directory-loading" class="is-loading-block"><span class="spinner"></span></div>
    <div id="directory-error" class="state-note is-error hidden">{{ __('ui.directory.error') }}</div>
    <div class="state-note hidden" id="directory-empty">{{ __('ui.directory.no_results') }}</div>
    <div class="people-grid" id="people-grid"></div>
</div>
@endsection

@section('scripts')
<script>
    const $ = function (id) { return document.getElementById(id); };
    let tab = 'father';
    let people = { father: [], servant: [] };

    function renderCard(p) {
        const e = Eftkad.esc;
        const phone = p.phone ? p.phone.replace(/[^\d+]/g, '') : '';
        return `<div class="person-card">
            <div class="person-avatar">${e(Eftkad.initials(p.name))}</div>
            <div class="person-main">
                <strong>${e(p.name)}</strong>
                <span>${e(p.membership_code)}</span>
                ${p.phone ? `<small>${e(p.phone)}</small>` : ''}
            </div>
            ${phone ? `<div class="person-actions">
                <a class="icon-button" href="tel:${e(phone)}" aria-label="Call">${Eftkad.icon('phone')}</a>
                <a class="icon-button" href="https://wa.me/${e(phone.replace('+', ''))}" target="_blank" rel="noopener" aria-label="WhatsApp">${Eftkad.icon('whatsapp')}</a>
            </div>` : ''}
        </div>`;
    }

    function render() {
        const q = $('directory-search').value.trim().toLowerCase();
        const visible = people[tab].filter(function (p) {
            return !q || (p.name || '').toLowerCase().includes(q) || (p.membership_code || '').toLowerCase().includes(q);
        });
        $('people-grid').innerHTML = visible.map(renderCard).join('');
        $('directory-empty').classList.toggle('hidden', visible.length > 0);
        $('count-father').textContent = people.father.length;
        $('count-servant').textContent = people.servant.length;
        document.querySelectorAll('.tabs button').forEach(function (b) { b.classList.toggle('active', b.dataset.tab === tab); });
    }

    async function loadDirectory() {
        const res = await Eftkad.cachedApi('/settings/filters');
        $('directory-loading').classList.add('hidden');

        if (!res.ok || !res.data) {
            $('directory-error').classList.remove('hidden');
            return;
        }
        const d = res.data.data || {};
        people.father = d.fathers || [];
        people.servant = d.servants || [];
        render();
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;
        loadDirectory();
        $('directory-search').addEventListener('input', render);
        document.querySelectorAll('.tabs button').forEach(function (b) {
            b.addEventListener('click', function () { tab = b.dataset.tab; render(); });
        });
    });
</script>
@endsection
