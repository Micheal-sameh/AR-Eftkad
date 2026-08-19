@extends('layouts.app')

@php($activeNav = 'visits')

@section('title', __('ui.visits.title') . ' - Eftkad')
@section('page-title', __('ui.visits.title'))
@section('page-title-mobile', __('ui.visits.title'))

@section('content')
<!-- Filter Bar -->
<div class="bg-surface-container-lowest p-4 sm:p-card-padding rounded-xl card-shadow border border-outline-variant grid grid-cols-1 md:grid-cols-4 gap-stack-gap relative z-20">
    <!-- Father Dropdown -->
    <div class="relative">
        <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">{{ __('ui.visits.filter_father') }}</label>
        <select id="filter-father" class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 font-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary appearance-none rtl:pr-8 ltr:pl-8 cursor-pointer">
            <option value="">{{ __('ui.visits.filter_father_all') }}</option>
        </select>
        <span class="material-symbols-outlined absolute rtl:left-3 ltr:right-3 top-[32px] text-on-surface-variant pointer-events-none">arrow_drop_down</span>
    </div>
    <!-- Servant Dropdown -->
    <div class="relative">
        <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">{{ __('ui.visits.filter_servant') }}</label>
        <select id="filter-servant" class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 font-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary appearance-none rtl:pr-8 ltr:pl-8 cursor-pointer">
            <option value="">{{ __('ui.visits.filter_servant_all') }}</option>
        </select>
        <span class="material-symbols-outlined absolute rtl:left-3 ltr:right-3 top-[32px] text-on-surface-variant pointer-events-none">arrow_drop_down</span>
    </div>
    <!-- Visit Type -->
    <div class="relative">
        <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">{{ __('ui.visits.filter_type') }}</label>
        <select id="filter-type" class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 font-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary appearance-none rtl:pr-8 ltr:pl-8 cursor-pointer">
            <option value="">{{ __('ui.visits.filter_type_all') }}</option>
        </select>
        <span class="material-symbols-outlined absolute rtl:left-3 ltr:right-3 top-[32px] text-on-surface-variant pointer-events-none">arrow_drop_down</span>
    </div>
    <!-- Date search -->
    <div class="relative">
        <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">{{ __('ui.visits.filter_date') }}</label>
        <div class="flex items-center bg-surface border border-outline-variant rounded-lg px-3 py-2 cursor-text">
            <span class="material-symbols-outlined rtl:ml-2 ltr:mr-2 text-on-surface-variant text-sm">calendar_today</span>
            <input id="filter-date" class="w-full bg-transparent border-none p-0 focus:ring-0 font-body-md text-on-surface" placeholder="{{ __('ui.visits.filter_date_placeholder') }}" type="text" />
        </div>
    </div>
</div>

<p id="filter-note" class="hidden text-label-sm font-label-sm text-on-surface-variant -mt-2">
    {{ __('ui.visits.filter_note') }}
</p>

<!-- Cards List -->
<div id="visits-loading" class="text-center py-12 text-on-surface-variant">{{ __('ui.visits.loading') }}</div>
<div id="visits-empty" class="hidden text-center py-12 text-on-surface-variant">{{ __('ui.visits.empty') }}</div>
<div id="visits-error" class="hidden text-center py-12 text-error">{{ __('ui.visits.error') }}</div>
<div id="visits-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10"></div>
@endsection

@section('fab')
<a href="/visits/create" class="fixed bottom-24 md:bottom-8 rtl:left-6 ltr:right-6 md:rtl:left-8 md:ltr:right-8 w-14 h-14 bg-primary text-on-primary rounded-full shadow-lg flex items-center justify-center hover:bg-surface-tint transition-all scale-95 active:opacity-80 z-40">
    <span class="material-symbols-outlined text-[28px]">add</span>
</a>
@endsection

@section('scripts')
<script>
    const NEED_ICONS = { 1: 'elderly', 2: 'healing', 3: 'work', 4: 'school', 5: 'local_dining', 6: 'emergency_home' };
    const MASS_STYLES = {
        1: 'bg-surface-dim text-on-surface-variant', // unknown
        2: 'bg-tertiary-fixed text-on-tertiary-fixed-variant', // regular
        3: "bg-[#FFD54F]/20 text-[#8F6A00]", // irregular
    };
    const ACCENT_STYLES = { 1: 'bg-outline-variant', 2: 'bg-primary', 3: 'bg-secondary' };
    const TYPE_ICON = { 1: 'call', 2: 'home' }; // 1=Call, 2=Home

    let allVisits = [];

    function typeBadge(eftkad) {
        const t = eftkad.type;
        if (!t) return '';
        const isHome = t.value === 2;
        const icon = TYPE_ICON[t.value] || 'help';
        const cls = isHome
            ? 'bg-primary-container text-on-primary-container'
            : 'bg-surface-variant text-on-surface border border-outline-variant';
        return `<span class="px-2 py-1 rounded-md ${cls} font-label-sm text-label-sm font-bold flex items-center gap-1">
            <span class="material-symbols-outlined text-[14px] filled">${icon}</span> ${escapeHtml(t.name)}
        </span>`;
    }

    function massBadge(eftkad) {
        const m = eftkad.mass_attendence;
        if (!m) return '';
        const cls = MASS_STYLES[m.value] || 'bg-surface-dim text-on-surface-variant';
        return `<span class="px-2 py-1 rounded-md ${cls} font-label-sm text-label-sm font-bold">${escapeHtml(m.name)}</span>`;
    }

    function needsChips(eftkad) {
        const needs = eftkad.needs || [];
        if (!needs.length) {
            return '<span class="font-label-sm text-on-surface-variant italic">' + UI_TEXT.visits.no_needs_noted + '</span>';
        }
        return needs.map(function (n) {
            const icon = NEED_ICONS[n.value] || 'info';
            return `<div class="flex items-center justify-center w-8 h-8 rounded-full bg-surface-variant text-on-surface-variant" title="${escapeHtml(n.name)}">
                <span class="material-symbols-outlined text-[18px]">${icon}</span>
            </div>`;
        }).join('');
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function renderCard(eftkad) {
        const accent = ACCENT_STYLES[(eftkad.mass_attendence || {}).value] || 'bg-outline-variant';
        const location = eftkad.location || eftkad.correspondence_address || '—';
        return `
        <a href="/visits/${eftkad.id}" class="bg-surface-container-lowest rounded-xl card-shadow border border-outline-variant p-card-padding flex flex-col gap-3 relative overflow-hidden transition-transform hover:-translate-y-1 hover:shadow-lg">
            <div class="absolute top-0 rtl:right-0 ltr:left-0 bottom-0 w-1 ${accent}"></div>
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-title-md text-title-md text-on-surface">${escapeHtml(eftkad.membership_code)}</span>
                    </div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">event</span> ${escapeHtml(eftkad.date)}
                    </p>
                </div>
                <div class="flex flex-col gap-1 items-end">
                    ${typeBadge(eftkad)}
                    ${massBadge(eftkad)}
                </div>
            </div>
            <div class="mt-2 flex items-center gap-2 font-body-md text-body-md text-on-surface">
                <span class="material-symbols-outlined text-tertiary">location_on</span>
                <span>${escapeHtml(location)}</span>
            </div>
            <div class="border-t border-outline-variant pt-3 mt-2 flex gap-2 flex-wrap">
                ${needsChips(eftkad)}
            </div>
        </a>`;
    }

    function applyFilters() {
        const father = document.getElementById('filter-father').value;
        const servant = document.getElementById('filter-servant').value;
        const type = document.getElementById('filter-type').value;
        const date = document.getElementById('filter-date').value.trim();

        const filtered = allVisits.filter(function (v) {
            if (father && v.father_membership_code !== father) return false;
            if (servant && v.servant_membership_code !== servant) return false;
            if (type && String((v.type || {}).value) !== type) return false;
            if (date && !(v.date || '').includes(date)) return false;
            return true;
        });

        const grid = document.getElementById('visits-grid');
        const empty = document.getElementById('visits-empty');
        grid.innerHTML = filtered.map(renderCard).join('');
        empty.classList.toggle('hidden', filtered.length > 0);
    }

    async function loadFilters() {
        const res = await Eftkad.api('/settings/filters');
        if (!res.ok || !res.data) return;
        const fathers = (res.data.data && res.data.data.fathers) || [];
        const servants = (res.data.data && res.data.data.servants) || [];

        const fatherSelect = document.getElementById('filter-father');
        fathers.forEach(function (f) {
            const opt = document.createElement('option');
            opt.value = f.membership_code;
            opt.textContent = f.name + ' (' + f.membership_code + ')';
            fatherSelect.appendChild(opt);
        });

        const servantSelect = document.getElementById('filter-servant');
        servants.forEach(function (s) {
            const opt = document.createElement('option');
            opt.value = s.membership_code;
            opt.textContent = s.name + ' (' + s.membership_code + ')';
            servantSelect.appendChild(opt);
        });

        if (fathers.length || servants.length) {
            document.getElementById('filter-note').classList.remove('hidden');
        }
    }

    async function loadEnums() {
        const res = await Eftkad.api('/settings/enums');
        if (!res.ok || !res.data) return;
        const types = (res.data.data && res.data.data.eftkad_type) || [];
        const typeSelect = document.getElementById('filter-type');
        types.forEach(function (t) {
            const opt = document.createElement('option');
            opt.value = t.value;
            opt.textContent = t.name;
            typeSelect.appendChild(opt);
        });
    }

    async function loadVisits() {
        const loading = document.getElementById('visits-loading');
        const errorBox = document.getElementById('visits-error');
        loading.classList.remove('hidden');
        errorBox.classList.add('hidden');

        const res = await Eftkad.api('/eftkads');

        loading.classList.add('hidden');

        if (!res.ok || !res.data) {
            errorBox.classList.remove('hidden');
            return;
        }

        allVisits = (res.data.data) || [];
        sessionStorage.setItem('eftkad_last_list', JSON.stringify(allVisits));
        applyFilters();
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;

        loadFilters();
        loadEnums();
        loadVisits();

        ['filter-father', 'filter-servant', 'filter-type'].forEach(function (id) {
            document.getElementById(id).addEventListener('change', applyFilters);
        });
        document.getElementById('filter-date').addEventListener('input', applyFilters);
    });
</script>
@endsection
