@extends('layouts.app')

@php($uiSections = ['visits'])

@php($activeNav = 'visits')

@section('title', __('ui.visits.title') . ' - Eftkad')
@section('page-title', __('ui.visits.title'))
@section('page-title-mobile', __('ui.visits.title'))

@section('content')
<!-- Filter Bar -->
<div class="bg-surface-container-lowest rounded-2xl card-shadow border border-outline-variant/70 relative z-20 overflow-hidden">
    <button type="button" id="filter-toggle-btn" class="pressable w-full flex items-center justify-between px-4 py-3 md:hidden" onclick="toggleFilterPanel()">
        <span class="flex items-center gap-2 font-title-md text-[17px] text-on-surface">
            <span class="flex items-center justify-center w-9 h-9 rounded-full bg-primary-fixed text-primary"><span class="material-symbols-outlined text-[20px]">filter_list</span></span>
            {{ __('ui.visits.filters_button') }}
        </span>
        <span class="material-symbols-outlined text-on-surface-variant chevron" id="filter-chevron">expand_more</span>
    </button>
    <div class="section-content collapsed md:!max-h-none md:!opacity-100 md:overflow-visible" id="filter-panel">
        <div class="p-4 pt-0 md:p-5 grid grid-cols-1 md:grid-cols-4 gap-stack-gap">
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
    </div>
</div>

<!-- Cards List -->
<div id="visits-loading" class="state-box" role="status">
    <div class="spinner"></div>
    <p class="font-body-md">{{ __('ui.visits.loading') }}</p>
</div>
<div id="visits-empty" class="hidden state-box">
    <div class="state-icon"><span class="material-symbols-outlined">church</span></div>
    <p class="font-title-md text-[18px] text-on-surface">{{ __('ui.visits.empty') }}</p>
    <a href="/visits/create" class="pressable mt-1 inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary text-on-primary font-label-sm text-[14px] font-semibold shadow-sm">
        <span class="material-symbols-outlined text-[18px]">add</span>{{ __('ui.nav.new_visit') }}
    </a>
</div>
<div id="visits-error" class="hidden state-box is-error" role="alert">
    <div class="state-icon"><span class="material-symbols-outlined">error</span></div>
    <p class="font-body-md text-error">{{ __('ui.visits.error') }}</p>
</div>
<div id="visits-grid" class="rise grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10"></div>
<button type="button" id="visits-more" class="hidden mx-auto px-6 py-3 rounded-lg border border-outline-variant text-primary font-title-md text-title-md hover:bg-surface-container-low transition-colors disabled:opacity-50">{{ __('ui.visits.load_more') }}</button>
@endsection

@section('fab')
<a href="/visits/create" aria-label="{{ __('ui.nav.new_visit') }}" class="pressable fixed bottom-[6.5rem] md:bottom-8 rtl:left-5 ltr:right-5 md:rtl:left-8 md:ltr:right-8 w-14 h-14 md:w-12 md:h-12 btn-brand rounded-full shadow-[0_10px_24px_rgba(0,93,172,0.45)] ring-4 ring-white/70 flex items-center justify-center hover:bg-surface-tint z-40">
    <span class="material-symbols-outlined text-[28px] md:text-[24px]">add</span>
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

    const PER_PAGE = 12;
    let allVisits = [];
    let currentPage = 1;
    let lastPage = 1;
    let loadSeq = 0;

    function toggleFilterPanel() {
        document.getElementById('filter-panel').classList.toggle('collapsed');
        document.getElementById('filter-chevron').classList.toggle('rotate-180');
    }

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
        <a href="/visits/${eftkad.id}" class="pressable bg-surface-container-lowest rounded-2xl card-shadow border border-outline-variant/70 p-4 md:p-5 flex flex-col gap-3 relative overflow-hidden md:hover:-translate-y-0.5 md:hover:shadow-lg">
            <div class="absolute top-0 rtl:right-0 ltr:left-0 bottom-0 w-1 ${accent}"></div>
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-title-md text-[17px] font-bold text-on-surface tracking-wide">${escapeHtml(eftkad.membership_code)}</span>
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
                <span class="min-w-0 break-words">${escapeHtml(location)}</span>
            </div>
            <div class="border-t border-outline-variant pt-3 mt-2 flex gap-2 flex-wrap">
                ${needsChips(eftkad)}
            </div>
        </a>`;
    }

    function renderVisits() {
        document.getElementById('visits-grid').innerHTML = allVisits.map(renderCard).join('');
        document.getElementById('visits-empty').classList.toggle('hidden', allVisits.length > 0);
        document.getElementById('visits-more').classList.toggle('hidden', currentPage >= lastPage);
    }

    async function loadFilters() {
        const res = await Eftkad.cachedApi('/settings/filters');
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
    }

    async function loadEnums() {
        const res = await Eftkad.cachedApi('/settings/enums');
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

    function currentFilters() {
        return {
            father: document.getElementById('filter-father').value,
            servant: document.getElementById('filter-servant').value,
            type: document.getElementById('filter-type').value,
            date: document.getElementById('filter-date').value.trim(),
        };
    }

    function hasFilters(f) {
        return !!(f.father || f.servant || f.type || f.date);
    }

    // Loads one server-side page. reset=true starts over at page 1 (filters changed);
    // otherwise appends the next page. Stale responses from an older filter state are dropped.
    async function loadVisits(reset) {
        const loading = document.getElementById('visits-loading');
        const errorBox = document.getElementById('visits-error');
        const moreBtn = document.getElementById('visits-more');
        const filters = currentFilters();
        const seq = ++loadSeq;
        const page = reset ? 1 : currentPage + 1;

        errorBox.classList.add('hidden');

        // Paint the last-seen first page right away (unfiltered view only), refresh behind it.
        let painted = false;
        if (reset && !hasFilters(filters) && !allVisits.length) {
            try {
                const cached = JSON.parse(sessionStorage.getItem('eftkad_last_list') || 'null');
                if (Array.isArray(cached) && cached.length) {
                    allVisits = cached;
                    renderVisits();
                    painted = true;
                }
            } catch (e) {}
        }
        if (!painted && reset) {
            allVisits = [];
            renderVisits();
            document.getElementById('visits-empty').classList.add('hidden');
            loading.classList.remove('hidden');
        }
        moreBtn.disabled = true;

        const params = new URLSearchParams({ page: page, per_page: PER_PAGE });
        Object.keys(filters).forEach(function (k) { if (filters[k]) params.set(k, filters[k]); });
        const res = await Eftkad.api('/eftkads?' + params.toString());

        if (seq !== loadSeq) return;
        loading.classList.add('hidden');
        moreBtn.disabled = false;

        if (!res.ok || !res.data) {
            if (!painted) errorBox.classList.remove('hidden');
            return;
        }

        const items = res.data.data || [];
        const meta = res.data.meta || {};
        currentPage = meta.current_page || page;
        lastPage = meta.last_page || currentPage;
        allVisits = reset ? items : allVisits.concat(items);
        if (reset && !hasFilters(filters)) {
            try { sessionStorage.setItem('eftkad_last_list', JSON.stringify(items)); } catch (e) {}
        }
        renderVisits();
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;

        loadFilters();
        loadEnums();
        loadVisits(true);

        const reload = function () { loadVisits(true); };
        ['filter-father', 'filter-servant', 'filter-type'].forEach(function (id) {
            document.getElementById(id).addEventListener('change', reload);
        });
        let dateTimer;
        document.getElementById('filter-date').addEventListener('input', function () {
            clearTimeout(dateTimer);
            dateTimer = setTimeout(reload, 300);
        });
        document.getElementById('visits-more').addEventListener('click', function () { loadVisits(false); });
    });
</script>
@endsection
