@extends('layouts.app')

@php($uiSections = ['common', 'visit_form'])

@php($activeNav = 'create')

@section('title', __('ui.visit_form.title'))

@section('content')
<form class="page-content form-page" id="visit-form" onsubmit="return false">
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ __('ui.fg.form.eyebrow') }}</div>
            <div class="page-title">{{ __('ui.fg.form.title') }}</div>
            <p>{{ __('ui.fg.form.hint') }}</p>
        </div>
        <button type="button" class="button button-primary desktop-only-flex" data-save>
            <x-icon name="check" :size="18" />
            <span data-save-label>{{ __('ui.fg.form.save') }}</span>
        </button>
    </div>

    <div class="error-banner hidden" id="form-error" role="alert">
        <x-icon name="alert" />
        <div>
            <strong>{{ __('ui.fg.form.banner_title') }}</strong>
            <span id="form-error-text">{{ __('ui.visit_form.generic_error') }}</span>
        </div>
    </div>

    <div class="form-layout">
        {{-- 01 Basic --}}
        <section class="form-section">
            <div class="form-section-heading">
                <span>01</span>
                <div><strong>{{ __('ui.fg.form.s1') }}</strong><small>{{ __('ui.fg.form.s1_sub') }}</small></div>
            </div>
            <div class="form-grid">
                <label class="field">
                    <span class="field-label">{{ __('ui.fg.form.member_code') }} *</span>
                    <span class="input-shell" data-shell="membership_code"><input id="f-membership_code" type="text" placeholder="E1C1FxxxNRx" style="text-transform: uppercase" autocomplete="off" /></span>
                    <span class="field-error" id="error-membership_code"></span>
                </label>
                <label class="field">
                    <span class="field-label">{{ __('ui.fg.form.date') }} *</span>
                    <span class="input-shell" data-shell="date"><input id="f-date" type="date" /></span>
                    <span class="field-error" id="error-date"></span>
                </label>
                <label class="field">
                    <span class="field-label">{{ __('ui.fg.form.address') }}</span>
                    <span class="input-shell" data-shell="correspondence_address"><input id="f-correspondence_address" type="text" placeholder="{{ __('ui.visit_form.address_placeholder') }}" /></span>
                    <span class="field-error" id="error-correspondence_address"></span>
                </label>
                <label class="field">
                    <span class="field-label">{{ __('ui.fg.form.area') }}</span>
                    <span class="input-shell" data-shell="location"><input id="f-location" type="text" placeholder="{{ __('ui.visit_form.location_placeholder') }}" /></span>
                    <span class="field-error" id="error-location"></span>
                </label>
                <label class="field">
                    <span class="field-label">{{ __('ui.fg.form.visit_type') }} *</span>
                    <span class="input-shell" data-shell="type"><select id="f-type"></select><x-icon name="chevron" :size="16" /></span>
                    <span class="field-error" id="error-type"></span>
                </label>
            </div>
        </section>

        {{-- 02 Attendance --}}
        <section class="form-section">
            <div class="form-section-heading">
                <span>02</span>
                <div><strong>{{ __('ui.fg.form.s2') }}</strong><small>{{ __('ui.fg.form.s2_sub') }}</small></div>
            </div>
            <div class="field-label">{{ __('ui.fg.form.mass') }} *</div>
            <div class="type-options form-chips" id="mass-attendance-options" style="margin-bottom: 1rem"></div>
            <span class="field-error" id="error-mass_attendence"></span>
            <div class="toggle-grid">
                @foreach (['father', 'mother', 'children'] as $who)
                    @include('partials.toggle-row', ['id' => $who.'_confession', 'label' => __('ui.fg.detail.'.$who.'_conf'), 'reveals' => 'field-'.$who.'_confessor'])
                    <label class="field hidden" id="field-{{ $who }}_confessor" style="grid-column: 1 / -1">
                        <span class="field-label">{{ __('ui.fg.detail.'.$who.'_confessor') }}</span>
                        <span class="input-shell" data-shell="{{ $who }}_confessor"><input id="f-{{ $who }}_confessor" type="text" maxlength="255" autocomplete="off" /></span>
                        <span class="field-error" id="error-{{ $who }}_confessor"></span>
                    </label>
                @endforeach
            </div>
        </section>

        {{-- 03 Follow-up --}}
        <section class="form-section">
            <div class="form-section-heading">
                <span>03</span>
                <div><strong>{{ __('ui.fg.form.s3') }}</strong><small>{{ __('ui.fg.form.s3_sub') }}</small></div>
            </div>
            <div class="toggle-grid">
                @include('partials.toggle-row', ['id' => 'attend_meetings', 'label' => __('ui.fg.detail.meetings')])
                @include('partials.toggle-row', ['id' => 'need_eftkad_from_meeting', 'label' => __('ui.fg.detail.need_meeting')])
                @include('partials.toggle-row', ['id' => 'need_eftkad_by_father', 'label' => __('ui.fg.detail.need_father')])
            </div>
        </section>

        {{-- 04 Needs --}}
        <section class="form-section">
            <div class="form-section-heading">
                <span>04</span>
                <div><strong>{{ __('ui.fg.form.s4') }}</strong><small>{{ __('ui.fg.form.s4_sub') }}</small></div>
            </div>
            <div class="field-label">{{ __('ui.fg.form.needs') }} *</div>
            <div class="chips form-chips" id="needs-options"></div>
            <span class="field-error" id="error-needs"></span>
            <div class="field-label field-gap">{{ __('ui.fg.form.comm') }} *</div>
            <div class="chips" id="communication-options"></div>
            <span class="field-error" id="error-communication_means"></span>
        </section>

        {{-- 05 Admin --}}
        <section class="form-section">
            <div class="form-section-heading">
                <span>05</span>
                <div><strong>{{ __('ui.fg.form.s5') }}</strong><small>{{ __('ui.fg.form.s5_sub') }}</small></div>
            </div>
            <div class="location-box">
                <button type="button" class="button button-secondary" id="set-location-btn">
                    <x-icon name="location" :size="18" />
                    <span id="set-location-label">{{ __('ui.fg.form.use_location') }}</span>
                </button>
                <label class="field">
                    <span class="input-shell" data-shell="location_url"><input id="f-location_url" type="url" placeholder="https://maps.google.com/…" /></span>
                </label>
                <span class="success-text hidden" id="location-ok"><x-icon name="check" :size="15" />{{ __('ui.fg.form.location_ok') }}</span>
                <span class="field-error" id="error-location_url"></span>
            </div>
            <div class="form-grid">
                <label class="field">
                    <span class="field-label">{{ __('ui.fg.form.father') }}</span>
                    <span class="input-shell" data-shell="father_membership_code"><select id="f-father_membership_code"><option value="">{{ __('ui.visit_form.father_select_placeholder') }}</option></select><x-icon name="chevron" :size="16" /></span>
                    <span class="field-error" id="error-father_membership_code"></span>
                </label>
                <label class="field">
                    <span class="field-label">{{ __('ui.fg.form.servant') }}</span>
                    <span class="input-shell" data-shell="servant_membership_code"><select id="f-servant_membership_code"><option value="">{{ __('ui.visit_form.servant_select_placeholder') }}</option></select><x-icon name="chevron" :size="16" /></span>
                    <span class="field-error" id="error-servant_membership_code"></span>
                </label>
            </div>
            <p style="margin: .5rem 0 0; color: var(--ink-soft); font-size: .72rem">{{ __('ui.fg.form.father_or_servant_hint') }}</p>
            <label class="field" style="margin-top: .9rem">
                <span class="field-label">{{ __('ui.fg.form.notes') }}</span>
                <textarea id="f-general_notes" class="textarea" rows="4" placeholder="{{ __('ui.visit_form.general_notes_placeholder') }}"></textarea>
                <span class="field-error" id="error-general_notes"></span>
            </label>
        </section>
    </div>

    <div class="sticky-save">
        <button type="button" class="button button-primary wide" data-save>
            <x-icon name="check" :size="18" />
            <span data-save-label>{{ __('ui.fg.form.save') }}</span>
        </button>
    </div>
</form>
@endsection

@section('scripts')
<script>
    const $ = function (id) { return document.getElementById(id); };

    // Toggle rows (button + switch).
    function initToggles() {
        document.querySelectorAll('.toggle-row').forEach(function (row) {
            row.addEventListener('click', function () {
                const on = row.getAttribute('aria-pressed') !== 'true';
                row.setAttribute('aria-pressed', on ? 'true' : 'false');
                row.querySelector('.toggle').classList.toggle('on', on);
                // A confession toggle reveals the field for the confessor's name right below it.
                if (row.dataset.reveals) $(row.dataset.reveals).classList.toggle('hidden', !on);
            });
        });
    }

    function boolToggle(id) {
        return $(id).getAttribute('aria-pressed') === 'true' ? 1 : 0;
    }

    function renderTypeSelect(options) {
        $('f-type').innerHTML = options.map(function (o) {
            return '<option value="' + o.value + '">' + Eftkad.esc(o.name) + '</option>';
        }).join('');
    }

    function renderRadioOptions(container, options, name) {
        container.innerHTML = options.map(function (o, i) {
            return `<label><input ${i === 0 ? 'checked' : ''} name="${name}" type="radio" value="${o.value}" /><span>${Eftkad.esc(o.name)}</span></label>`;
        }).join('');
    }

    function renderChips(container, options, group) {
        container.innerHTML = options.map(function (o) {
            return `<button type="button" class="chip" data-group="${group}" data-value="${o.value}"><span>${Eftkad.esc(o.name)}</span></button>`;
        }).join('');
        container.querySelectorAll('.chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                const on = chip.classList.toggle('selected');
                const check = chip.querySelector('.icon');
                if (on && !check) chip.insertAdjacentHTML('beforeend', Eftkad.icon('check', 14));
                if (!on && check) check.remove();
            });
        });
    }

    function checkedValues(group) {
        return Array.from(document.querySelectorAll('.chip.selected[data-group="' + group + '"]'))
            .map(function (el) { return parseInt(el.dataset.value, 10); });
    }

    function clearErrors() {
        $('form-error').classList.add('hidden');
        document.querySelectorAll('.field-error').forEach(function (el) { el.textContent = ''; });
        document.querySelectorAll('.input-shell.is-error').forEach(function (el) { el.classList.remove('is-error'); });
    }

    function showFieldErrors(errors) {
        Object.keys(errors).forEach(function (field) {
            const key = field.replace(/\.\d+$/, '').split('.')[0];
            const el = $('error-' + key);
            if (el) el.innerHTML = Eftkad.icon('alert', 14) + Eftkad.esc(errors[field][0]);
            const shell = document.querySelector('[data-shell="' + key + '"]');
            if (shell) shell.classList.add('is-error');
        });
    }

    function toDMY(iso) {
        if (!iso) return '';
        const p = iso.split('-');
        return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : iso;
    }

    async function loadEnums() {
        const res = await Eftkad.cachedApi('/settings/enums');
        if (!res.ok || !res.data) return;
        const data = res.data.data || {};
        renderTypeSelect(data.eftkad_type || []);
        renderRadioOptions($('mass-attendance-options'), data.mass_attendence_type || [], 'mass_freq');
        renderChips($('needs-options'), data.visits_status || [], 'need');
        renderChips($('communication-options'), data.user_status || [], 'comm');
    }

    function fillPeople(selectId, people) {
        const select = $(selectId);
        people.forEach(function (p) {
            const option = document.createElement('option');
            option.value = p.membership_code;
            option.textContent = p.name;
            select.appendChild(option);
        });
    }

    async function loadAdminSelects() {
        const res = await Eftkad.cachedApi('/settings/filters');
        if (!res.ok || !res.data) return;
        const data = res.data.data || {};
        fillPeople('f-father_membership_code', data.fathers || []);
        fillPeople('f-servant_membership_code', data.servants || []);
    }

    function setCurrentLocation() {
        const btn = $('set-location-btn');
        const label = $('set-location-label');
        $('location-ok').classList.add('hidden');

        if (!navigator.geolocation) {
            alert(UI_TEXT.visit_form.location_not_supported);
            return;
        }

        function done() {
            btn.disabled = false;
            label.textContent = UI_TEXT.fg.form.use_location;
        }

        btn.disabled = true;
        label.textContent = UI_TEXT.fg.form.locating;

        navigator.geolocation.getCurrentPosition(
            function (position) {
                $('f-location_url').value = 'https://www.google.com/maps?q=' + position.coords.latitude + ',' + position.coords.longitude;
                $('location-ok').classList.remove('hidden');
                done();
            },
            function (error) {
                done();
                alert(error.code === error.PERMISSION_DENIED
                    ? UI_TEXT.visit_form.location_permission_denied
                    : UI_TEXT.visit_form.location_error);
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    function setSaving(saving) {
        document.querySelectorAll('[data-save]').forEach(function (b) { b.disabled = saving; });
        document.querySelectorAll('[data-save-label]').forEach(function (l) {
            l.textContent = saving ? UI_TEXT.common.saving : UI_TEXT.fg.form.save;
        });
    }

    async function submitForm() {
        clearErrors();
        setSaving(true);

        const massEl = document.querySelector('input[name="mass_freq"]:checked');
        const typeVal = $('f-type').value;

        const payload = {
            membership_code: $('f-membership_code').value.trim(),
            date: toDMY($('f-date').value),
            correspondence_address: $('f-correspondence_address').value.trim(),
            location: $('f-location').value.trim(),
            location_url: $('f-location_url').value.trim(),
            type: typeVal ? parseInt(typeVal, 10) : null,
            mass_attendence: massEl ? parseInt(massEl.value, 10) : null,
            needs: checkedValues('need'),
            communication_means: checkedValues('comm'),
            attend_meetings: boolToggle('f-attend_meetings'),
            need_eftkad_from_meeting: boolToggle('f-need_eftkad_from_meeting') ? '1' : '0',
            need_eftkad_by_father: boolToggle('f-need_eftkad_by_father'),
            father_confession: boolToggle('f-father_confession') ? '1' : '0',
            mother_confession: boolToggle('f-mother_confession') ? '1' : '0',
            children_confession: boolToggle('f-children_confession') ? '1' : '0',
            general_notes: $('f-general_notes').value.trim(),
        };

        // Only one of father / servant is required, so empty selections are not sent.
        ['father', 'servant'].forEach(function (who) {
            const code = $('f-' + who + '_membership_code').value.trim();
            if (code) payload[who + '_membership_code'] = code;
        });

        // The confessor's name is only sent when that confession toggle is on and a name was typed.
        ['father', 'mother', 'children'].forEach(function (who) {
            const name = $('f-' + who + '_confessor').value.trim();
            if (boolToggle('f-' + who + '_confession') && name) payload[who + '_confessor'] = name;
        });

        const res = await Eftkad.api('/eftkads', { method: 'POST', body: payload });
        setSaving(false);

        if (res.ok) {
            try { sessionStorage.setItem('eftkad_toast', UI_TEXT.fg.form.saved); } catch (e) {}
            window.location.href = '/visits';
            return;
        }

        if (res.status === 422 && res.data && res.data.errors) {
            showFieldErrors(res.data.errors);
            $('form-error-text').textContent = UI_TEXT.common.review_fields;
        } else {
            $('form-error-text').textContent = (res.data && res.data.message) || UI_TEXT.visit_form.save_error;
        }
        $('form-error').classList.remove('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;

        initToggles();
        loadEnums();
        loadAdminSelects();

        document.querySelectorAll('[data-save]').forEach(function (b) { b.addEventListener('click', submitForm); });
        $('set-location-btn').addEventListener('click', setCurrentLocation);
    });
</script>
@endsection
