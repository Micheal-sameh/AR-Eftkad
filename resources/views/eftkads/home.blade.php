@extends('layouts.app')

@php($uiSections = ['common', 'visits'])

@php($activeNav = 'home')

@section('title', __('ui.fg.home.title') . ' - Eftkad')

@section('content')
<div class="page-content">
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ __('ui.fg.home.eyebrow') }}</div>
            <div class="page-title">{{ __('ui.fg.home.title') }}</div>
            <p>{{ __('ui.fg.home.subtitle') }}</p>
        </div>
        <a href="/visits/create" class="button button-primary desktop-only-flex">
            <x-icon name="plus" :size="18" />
            <span>{{ __('ui.fg.visits.new_visit') }}</span>
        </a>
    </div>

    <div class="feed-error hidden" id="home-error" role="alert">
        <span><x-icon name="alert" :size="19" /></span>
        <div>
            <strong>{{ __('ui.fg.visits.error_title') }}</strong>
            <small>{{ __('ui.fg.visits.error_hint') }}</small>
        </div>
        <button type="button" class="button button-secondary" id="home-retry"><span>{{ __('ui.fg.visits.retry') }}</span></button>
    </div>

    <div id="home-loading" aria-label="Loading">
        <span class="sk sk-heading"></span>
        <div class="visits-grid">@include('partials.skeleton-cards', ['count' => 2])</div>
        <span class="sk sk-heading"></span>
        <div class="visits-grid">@include('partials.skeleton-cards', ['count' => 4])</div>
    </div>

    <div id="home-content" class="hidden">
        <div class="list-heading">
            <strong>{{ __('ui.fg.home.new_heading') }}</strong>
            <span id="new-count"></span>
        </div>
        <div class="visits-grid" id="new-grid"></div>
        <div class="empty-state hidden" id="new-empty">
            <div class="empty-icon"><x-icon name="check" :size="30" /></div>
            <strong>{{ __('ui.fg.home.new_empty') }}</strong>
        </div>

        <div class="list-heading" style="margin-top: 1.6rem">
            <strong>{{ __('ui.fg.home.recent_heading') }}</strong>
            <a href="/visits/all" class="section-link">{{ __('ui.fg.home.see_all') }}</a>
        </div>
        <div class="visits-grid" id="recent-grid"></div>
        <div class="empty-state hidden" id="recent-empty">
            <div class="empty-icon"><x-icon name="calendar" :size="30" /></div>
            <strong>{{ __('ui.fg.visits.empty_title') }}</strong>
            <span>{{ __('ui.fg.visits.empty_text') }}</span>
            <a href="/visits/create" class="button button-primary">
                <x-icon name="plus" :size="18" />
                <span>{{ __('ui.fg.visits.new_visit') }}</span>
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('partials.share-sheet')
<script>
    const TYPE_TONE = { 1: 'gold', 2: 'blue' }; // 1=Call, 2=Home
    const memberNames = {};
    const $ = function (id) { return document.getElementById(id); };

    function personName(code) {
        if (!code) return UI_TEXT.common.dash;
        return memberNames[code] || code;
    }

    function renderCard(v) {
        const e = Eftkad.esc;
        const tone = TYPE_TONE[(v.type || {}).value] || 'blue';
        const needs = (v.needs || []).map(function (n) { return n.name; }).join(' • ');
        const place = v.location || v.correspondence_address || '';
        const reason = v.new_reason
            ? `<span class="badge badge-green">${e(v.new_reason === 'shared' ? UI_TEXT.fg.home.reason_shared : UI_TEXT.fg.home.reason_assigned)}</span>`
            : '';
        return `
        <a href="/visits/${v.id}" class="visit-card">
            <div class="visit-card-top">
                <div class="member-avatar">${e((v.membership_code || '?').replace(/[^A-Za-z0-9؀-ۿ]/g, '').slice(0, 2).toUpperCase())}</div>
                <div class="visit-title">
                    <strong>${e(v.membership_code)}</strong>
                    ${place ? `<span dir="auto">${e(place)}</span>` : ''}
                </div>
                ${reason}${v.type ? `<span class="badge badge-${tone}">${e(v.type.name)}</span>` : ''}
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

    async function loadHome() {
        $('home-error').classList.add('hidden');
        $('home-loading').classList.remove('hidden');

        const dir = await Eftkad.cachedApi('/settings/filters');
        if (dir.ok && dir.data) {
            const d = dir.data.data || {};
            (d.fathers || []).concat(d.servants || []).forEach(function (p) { memberNames[p.membership_code] = p.name; });
        }

        const res = await Eftkad.api('/eftkads/home');
        $('home-loading').classList.add('hidden');
        if (!res.ok || !res.data || !res.data.data) {
            $('home-error').classList.remove('hidden');
            return;
        }

        const fresh = res.data.data.new || [];
        const recent = res.data.data.recent || [];
        $('new-grid').innerHTML = fresh.map(renderCard).join('');
        $('new-count').textContent = fresh.length ? UI_TEXT.fg.visits.count.replace(':n', fresh.length) : '';
        $('new-empty').classList.toggle('hidden', fresh.length > 0);
        $('recent-grid').innerHTML = recent.map(renderCard).join('');
        $('recent-empty').classList.toggle('hidden', recent.length > 0);
        $('home-content').classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;
        loadHome();
        $('home-retry').addEventListener('click', loadHome);
        // The share button sits inside the card link: open the popup instead of the visit.
        $('home-content').addEventListener('click', function (ev) {
            const b = ev.target.closest('[data-share]');
            if (!b) return;
            ev.preventDefault();
            ShareSheet.open(b.getAttribute('data-share'));
        });
    });
</script>
@endsection
