@extends('layouts.app')

@php($uiSections = ['common', 'visits'])

@php($activeNav = 'visits')

@section('title', __('ui.fg.visits.title') . ' - Eftkad')

@section('content')
<div class="page-content">
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ __('ui.fg.visits.eyebrow') }}</div>
            <div class="page-title">{{ __('ui.fg.visits.title') }}</div>
            <p>{{ __('ui.fg.visits.subtitle') }}</p>
        </div>
        <a href="/visits/create" class="button button-primary desktop-only-flex">
            <x-icon name="plus" :size="18" />
            <span>{{ __('ui.fg.visits.new_visit') }}</span>
        </a>
    </div>

    <div class="toolbar">
        <div class="search-bar">
            <x-icon name="search" :size="19" />
            <input id="visit-search" aria-label="{{ __('ui.fg.visits.search') }}" placeholder="{{ __('ui.fg.visits.search') }}" />
            <kbd>⌘ K</kbd>
        </div>
        <button type="button" class="button button-secondary" id="open-filters">
            <x-icon name="filter" :size="18" />
            <span>{{ __('ui.fg.visits.filters') }}</span>
            <span class="filter-count hidden" id="filter-count">0</span>
        </button>
    </div>

    <div class="visit-summary">
        <div><span>{{ __('ui.fg.visits.all_visits') }}</span><strong id="stat-total">—</strong></div>
        <div><span>{{ __('ui.fg.visits.this_month') }}</span><strong id="stat-month">—</strong></div>
        <div class="summary-accent"><span>{{ __('ui.fg.visits.need_followup') }}</span><strong id="stat-followup">—</strong></div>
    </div>

    <div class="list-heading">
        <strong>{{ __('ui.fg.visits.recent') }}</strong>
        <span id="list-count"></span>
    </div>

    <div class="visits-grid" id="visits-grid"></div>

    <div class="visits-grid" id="visits-loading" aria-label="Loading" style="margin-top:.85rem">
        @include('partials.skeleton-cards', ['count' => 6])
    </div>
</div>
@endsection

@section('scripts')
@include('partials.share-sheet')
<script>
    const PER_PAGE = 12;
    const TYPE_TONE = { 1: 'gold', 2: 'blue' }; // 1=Call, 2=Home
    let allVisits = [];
    let currentPage = 1;
    let lastPage = 1;
    let totalVisits = null;
    let loadSeq = 0;
    let applied = { father: '', servant: '', type: '', date_from: '', date_to: '' };
    const memberNames = {};
    const $ = function (id) { return document.getElementById(id); };

    function toDMY(iso) {
        if (!iso) return '';
        const p = iso.split('-');
        return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : iso;
    }

    function personName(code) {
        if (!code) return UI_TEXT.common.dash;
        return memberNames[code] || code;
    }

    function needsFollowUp(v) {
        return (v.need_eftkad_by_father && v.need_eftkad_by_father.value === 1)
            || v.need_eftkad_from_meeting === '1' || v.need_eftkad_from_meeting === 1;
    }

    function renderCard(v) {
        const e = Eftkad.esc;
        const tone = TYPE_TONE[(v.type || {}).value] || 'blue';
        const needs = (v.needs || []).map(function (n) { return n.name; }).join(' • ');
        const place = v.location || v.correspondence_address || '';
        return `
        <a href="/visits/${v.id}" class="visit-card">
            <div class="visit-card-top">
                <div class="member-avatar">${e((v.membership_code || '?').replace(/[^A-Za-z0-9؀-ۿ]/g, '').slice(0, 2).toUpperCase())}</div>
                <div class="visit-title">
                    <strong>${e(v.membership_code)}</strong>
                    ${place ? `<span dir="auto">${e(place)}</span>` : ''}
                </div>
                ${v.type ? `<span class="badge badge-${tone}">${e(v.type.name)}</span>` : ''}
                <button type="button" class="icon-button" data-share="${v.id}" aria-label="${e(UI_TEXT.fg.visits.share)}">${Eftkad.icon('share', 18)}</button>
            </div>
            <div class="visit-meta">
                <span>${Eftkad.icon('calendar', 16)}${e(v.date)}</span>
                <span>${Eftkad.icon('cross', 16)}${e(personName(v.father_membership_code))}</span>
                <span>${Eftkad.icon('user', 16)}${e(personName(v.servant_membership_code))}</span>
            </div>
            <div class="needs-line ${needs ? '' : 'muted'}">
                <span class="needs-dot"></span>
                <span>${e(needs || UI_TEXT.visits.no_needs_noted)}</span>
                ${Eftkad.icon('chevron', 18)}
            </div>
        </a>`;
    }

    function matchesQuery(v, q) {
        if (!q) return true;
        return [v.membership_code, v.location, v.correspondence_address, personName(v.father_membership_code), personName(v.servant_membership_code)]
            .join(' ').toLowerCase().includes(q.toLowerCase());
    }

    function render() {
        const q = $('visit-search').value.trim();
        const shown = allVisits.filter(function (v) { return matchesQuery(v, q); });
        $('visits-grid').innerHTML = shown.map(renderCard).join('');
        $('list-count').textContent = UI_TEXT.fg.visits.count.replace(':n', shown.length);
        $('visits-empty').classList.toggle('hidden', allVisits.length > 0 || !loaded);
        $('visits-more').classList.toggle('hidden', currentPage >= lastPage || !allVisits.length);
        $('stat-total').textContent = totalVisits === null ? '—' : totalVisits;
        $('stat-followup').textContent = allVisits.filter(needsFollowUp).length;
    }

    let loaded = false;

    function activeFilterCount() {
        return Object.keys(applied).filter(function (k) { return applied[k]; }).length;
    }

    function updateFilterBadge() {
        const n = activeFilterCount();
        $('filter-count').textContent = n;
        $('filter-count').classList.toggle('hidden', n === 0);
    }

    // Loads one server-side page. reset=true starts over at page 1 (filters changed);
    // otherwise appends the next page. Stale responses from an older filter state are dropped.
    async function loadVisits(reset) {
        const seq = ++loadSeq;
        const page = reset ? 1 : currentPage + 1;

        $('visits-error').classList.add('hidden');
        $('visits-more').classList.add('hidden');
        $('visits-loading').classList.remove('hidden');
        if (reset) { allVisits = []; loaded = false; render(); }

        const params = new URLSearchParams({ page: page, per_page: PER_PAGE });
        Object.keys(applied).forEach(function (k) { if (applied[k]) params.set(k, applied[k]); });
        const res = await Eftkad.api('/eftkads?' + params.toString());

        if (seq !== loadSeq) return;
        $('visits-loading').classList.add('hidden');

        if (!res.ok || !res.data) {
            $('visits-error').classList.remove('hidden');
            return;
        }

        const items = res.data.data || [];
        const meta = res.data.meta || {};
        currentPage = meta.current_page || page;
        lastPage = meta.last_page || currentPage;
        totalVisits = meta.total !== undefined ? meta.total
            : ((res.data.additional_data || {}).eftkads_count !== undefined ? res.data.additional_data.eftkads_count : allVisits.length + items.length);
        allVisits = reset ? items : allVisits.concat(items);
        loaded = true;
        render();
    }

    async function loadMonthStat() {
        const now = new Date();
        const mm = String(now.getMonth() + 1).padStart(2, '0');
        const res = await Eftkad.api('/eftkads?per_page=1&date=' + mm + '-' + now.getFullYear());
        if (res.ok && res.data && res.data.meta) $('stat-month').textContent = res.data.meta.total;
    }

    async function loadFilters() {
        const res = await Eftkad.cachedApi('/settings/filters');
        if (!res.ok || !res.data) return;
        const data = res.data.data || {};
        function fill(id, people) {
            const select = $(id);
            people.forEach(function (p) {
                memberNames[p.membership_code] = p.name;
                const opt = document.createElement('option');
                opt.value = p.membership_code;
                opt.textContent = p.name + ' (' + p.membership_code + ')';
                select.appendChild(opt);
            });
        }
        fill('filter-father', data.fathers || []);
        fill('filter-servant', data.servants || []);
        render();
    }

    async function loadEnums() {
        const res = await Eftkad.cachedApi('/settings/enums');
        if (!res.ok || !res.data) return;
        const select = $('filter-type');
        ((res.data.data && res.data.data.eftkad_type) || []).forEach(function (t) {
            const opt = document.createElement('option');
            opt.value = t.value;
            opt.textContent = t.name;
            select.appendChild(opt);
        });
    }

    function setSheet(open) { $('filter-sheet').classList.toggle('open', open); }

    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;

        loadFilters().then(function () { loadVisits(true); });
        loadEnums();
        loadMonthStat();

        // The share button sits inside the card link: open the popup instead of the visit.
        $('visits-grid').addEventListener('click', function (ev) {
            const b = ev.target.closest('[data-share]');
            if (!b) return;
            ev.preventDefault();
            ShareSheet.open(b.getAttribute('data-share'));
        });

        $('visit-search').addEventListener('input', render);
        $('visits-more').addEventListener('click', function () { loadVisits(false); });
        $('visits-retry').addEventListener('click', function () { loadVisits(allVisits.length === 0); });

        $('open-filters').addEventListener('click', function () { setSheet(true); });
        $('close-filters').addEventListener('click', function () { setSheet(false); });
        $('filter-backdrop').addEventListener('click', function () { setSheet(false); });

        $('apply-filters').addEventListener('click', function () {
            let from = $('filter-date-from').value;
            let to = $('filter-date-to').value;
            if (from && to && from > to) { const t = from; from = to; to = t; $('filter-date-from').value = from; $('filter-date-to').value = to; }
            applied = {
                father: $('filter-father').value,
                servant: $('filter-servant').value,
                type: $('filter-type').value,
                date_from: toDMY(from),
                date_to: toDMY(to),
            };
            updateFilterBadge();
            setSheet(false);
            loadVisits(true);
        });
        $('clear-filters').addEventListener('click', function () {
            ['filter-father', 'filter-servant', 'filter-type', 'filter-date-from', 'filter-date-to'].forEach(function (id) { $(id).value = ''; });
            applied = { father: '', servant: '', type: '', date_from: '', date_to: '' };
            updateFilterBadge();
            setSheet(false);
            loadVisits(true);
        });
    });
</script>
@endsection
