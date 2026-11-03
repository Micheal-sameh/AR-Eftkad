@extends('layouts.app')

@php($uiSections = ['common', 'visit_detail'])

@php($activeNav = 'visits')

@section('title', __('ui.visit_detail.title') . ' - Eftkad')

@section('content')
<div class="page-content detail-page">
    <a href="/visits" class="back-link" onclick="if (history.length > 1) { history.back(); return false; }">
        <x-icon name="arrow" :size="18" />
        {{ __('ui.fg.nav.visits') }}
    </a>

    <div id="detail-loading" aria-label="Loading" aria-hidden="true">
        <div class="sk-row" style="margin-bottom:1.2rem">
            <span class="sk sk-avatar lg"></span>
            <div class="sk-col"><span class="sk sk-line" style="width:40%;height:.9rem"></span><span class="sk sk-line" style="width:25%"></span></div>
        </div>
        <div class="details-grid">
            @for ($i = 0; $i < 4; $i++)
                <div class="sk-card"><span class="sk sk-line" style="width:35%"></span><span class="sk sk-line" style="width:80%"></span><span class="sk sk-line" style="width:60%"></span></div>
            @endfor
        </div>
    </div>
    <div id="detail-error" class="state-note is-error hidden">{{ __('ui.visit_detail.error') }}</div>

    <div id="detail-content" class="hidden">
        <div class="detail-hero">
            <div class="member-avatar large" id="d-avatar">··</div>
            <div>
                <div class="detail-hero-line">
                    <div class="page-title" id="d-membership_code">—</div>
                    <span id="d-badges"></span>
                </div>
                <div class="detail-subtitle">
                    <x-icon name="calendar" :size="16" />
                    <span id="d-date">—</span>
                </div>
                <button type="button" class="button button-secondary" id="share-open" style="margin-top:.7rem">
                    <x-icon name="share" :size="18" />
                    <span>{{ __('ui.visit_detail.share_button') }}</span>
                </button>
            </div>
        </div>

        <div class="details-grid">
            <section class="detail-section">
                <div class="detail-section-title"><span><x-icon name="cross" :size="19" /></span><strong>{{ __('ui.fg.detail.needs') }}</strong></div>
                <div class="chips" id="d-needs"></div>
            </section>

            <section class="detail-section">
                <div class="detail-section-title"><span><x-icon name="phone" :size="19" /></span><strong>{{ __('ui.fg.detail.comm') }}</strong></div>
                <div class="chips" id="d-comm"></div>
            </section>

            <section class="detail-section">
                <div class="detail-section-title"><span><x-icon name="location" :size="19" /></span><strong>{{ __('ui.fg.detail.location') }}</strong></div>
                <p class="section-copy" id="d-address">—</p>
                <p class="section-copy" id="d-location" style="margin-top:-.4rem"></p>
                <a href="#" target="_blank" rel="noopener" class="button button-secondary hidden" id="d-location-url">
                    <x-icon name="map" :size="18" />
                    <span>{{ __('ui.fg.detail.maps') }}</span>
                </a>
            </section>

            <section class="detail-section">
                <div class="detail-section-title"><span><x-icon name="users" :size="19" /></span><strong>{{ __('ui.fg.detail.resp') }}</strong></div>
                <div class="stacked-info">
                    <div class="info-row">
                        <div class="info-icon"><x-icon name="cross" :size="18" /></div>
                        <div><span>{{ __('ui.fg.visits.father') }}</span><strong id="d-father">—</strong></div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon"><x-icon name="user" :size="18" /></div>
                        <div><span>{{ __('ui.fg.visits.servant') }}</span><strong id="d-servant">—</strong></div>
                    </div>
                </div>
            </section>

            <section class="detail-section wide">
                <div class="detail-section-title"><span><x-icon name="check" :size="19" /></span><strong>{{ __('ui.fg.detail.attendance') }}</strong></div>
                <div class="status-grid" id="d-status"></div>
            </section>

            <section class="detail-section wide">
                <div class="detail-section-title"><span><x-icon name="book" :size="19" /></span><strong>{{ __('ui.fg.detail.notes') }}</strong></div>
                <p class="notes-copy" id="d-notes">{{ __('ui.visit_detail.no_notes') }}</p>
            </section>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('partials.share-sheet')
<script>
    const VISIT_ID = {{ (int) $id }};
    const memberNames = {};
    const TYPE_TONE = { 1: 'gold', 2: 'blue' };
    const $ = function (id) { return document.getElementById(id); };

    function chips(items, emptyText) {
        if (!items.length) return '<span class="section-copy" style="margin:0">' + Eftkad.esc(emptyText) + '</span>';
        return items.map(function (n) { return '<span class="chip selected">' + Eftkad.esc(n.name) + '</span>'; }).join('');
    }

    function statusItem(label, yes, text, note) {
        const shown = text || (yes ? UI_TEXT.common.yes : UI_TEXT.common.no);
        return `<div class="status-item"><span>${Eftkad.esc(label)}</span>
            <span class="badge ${yes ? 'badge-green' : 'badge-neutral'}">${Eftkad.icon(yes ? 'check' : 'close', 13)}${Eftkad.esc(shown)}</span>${note ? `<span class="status-note">${Eftkad.esc(note)}</span>` : ''}</div>`;
    }

    const isYes = function (v) { return v === '1' || v === 1 || v === true; };
    const enumYes = function (r) { return !!r && r.value === 1; };
    // "<whose confessor>: <name>" when the confession is on and a name was recorded.
    const confessorNote = function (flag, label, name) { return isYes(flag) && name ? label + ': ' + name : ''; };

    async function loadDirectory() {
        const res = await Eftkad.cachedApi('/settings/filters');
        if (!res.ok || !res.data) return;
        const d = res.data.data || {};
        (d.fathers || []).concat(d.servants || []).forEach(function (p) { memberNames[p.membership_code] = p.name; });
    }

    function person(code) {
        if (!code) return UI_TEXT.common.dash;
        return memberNames[code] ? memberNames[code] + ' (' + code + ')' : code;
    }

    function renderDetail(e) {
        const t = UI_TEXT.fg.detail;
        $('d-avatar').textContent = (e.membership_code || '?').replace(/[^A-Za-z0-9؀-ۿ]/g, '').slice(0, 2).toUpperCase();
        $('d-membership_code').textContent = e.membership_code || UI_TEXT.common.dash;
        $('d-date').textContent = e.date || UI_TEXT.common.dash;
        $('d-badges').innerHTML = e.type
            ? '<span class="badge badge-' + (TYPE_TONE[e.type.value] || 'blue') + '">' + Eftkad.esc(e.type.name) + '</span>' : '';

        $('d-needs').innerHTML = chips(e.needs || [], UI_TEXT.visit_detail.no_needs);
        $('d-comm').innerHTML = chips(e.communication_means || [], UI_TEXT.visit_detail.no_comm);

        $('d-address').textContent = e.correspondence_address || UI_TEXT.common.dash;
        $('d-location').textContent = e.location || '';
        if (e.location_url) {
            $('d-location-url').href = e.location_url;
            $('d-location-url').classList.remove('hidden');
        }

        $('d-father').textContent = person(e.father_membership_code);
        $('d-servant').textContent = person(e.servant_membership_code);

        $('d-status').innerHTML = [
            statusItem(t.mass, !!e.mass_attendence && e.mass_attendence.value === 2, e.mass_attendence ? e.mass_attendence.name : ''),
            statusItem(t.meetings, enumYes(e.attend_meetings)),
            statusItem(t.father_conf, isYes(e.father_confession), '', confessorNote(e.father_confession, t.father_confessor, e.father_confessor)),
            statusItem(t.mother_conf, isYes(e.mother_confession), '', confessorNote(e.mother_confession, t.mother_confessor, e.mother_confessor)),
            statusItem(t.children_conf, isYes(e.children_confession), '', confessorNote(e.children_confession, t.children_confessor, e.children_confessor)),
            statusItem(t.need_meeting, isYes(e.need_eftkad_from_meeting)),
            statusItem(t.need_father, enumYes(e.need_eftkad_by_father)),
        ].join('');

        $('d-notes').textContent = e.general_notes || UI_TEXT.visit_detail.no_notes;
    }

    async function loadDetail() {
        await loadDirectory();
        const res = await Eftkad.api('/eftkads/detail/' + VISIT_ID);
        $('detail-loading').classList.add('hidden');

        if (!res.ok || !res.data || !res.data.data) {
            $('detail-error').classList.remove('hidden');
            return;
        }
        renderDetail(res.data.data);
        $('detail-content').classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;
        loadDetail();
        $('share-open').addEventListener('click', function () { ShareSheet.open(VISIT_ID); });
    });
</script>
@endsection
