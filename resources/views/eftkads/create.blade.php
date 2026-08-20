@extends('layouts.app')

@php($activeNav = 'create')

@section('title', __('ui.visit_form.title'))
@section('page-title', __('ui.visit_form.heading'))
@section('page-title-mobile', __('ui.visit_form.heading'))

@section('content')
<div class="md:max-w-3xl">
<div class="mb-2 -mt-4">
    <p class="text-on-surface-variant text-sm">{{ __('ui.visit_form.subtitle') }}</p>
</div>

<div id="form-error" class="hidden mb-2 px-4 py-3 rounded-lg bg-error-container text-on-error-container font-body-md text-sm flex items-center gap-2">
    <span class="material-symbols-outlined" style="font-size: 20px;">error</span>
    <span id="form-error-text">{{ __('ui.visit_form.generic_error') }}</span>
</div>

<form id="visit-form" class="space-y-6">
    <!-- Section 1: Basic Info -->
    <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] rtl:border-r-4 ltr:border-l-4 border-primary overflow-hidden">
        <button class="w-full flex items-center justify-between p-4 md:p-5 bg-surface-container-lowest hover:bg-surface-container-low transition-colors" onclick="toggleSection('section-1')" type="button">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">info</span>
                <h3 class="font-title-md text-base md:text-lg text-on-surface">{{ __('ui.visit_form.section_basic') }}</h3>
            </div>
            <span class="material-symbols-outlined text-outline-variant chevron" id="chevron-1">expand_more</span>
        </button>
        <div class="section-content px-4 md:px-5 pb-4 md:pb-5 pt-0 space-y-4" id="section-1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">{{ __('ui.visit_form.membership_code') }} <span class="text-error">*</span></label>
                    <input id="f-membership_code" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-shadow" placeholder="{{ __('ui.visit_form.membership_code_placeholder') }}" type="text" />
                    <p class="field-error" id="error-membership_code"></p>
                </div>
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">{{ __('ui.visit_form.visit_date') }} <span class="text-error">*</span></label>
                    <input id="f-date" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-shadow" type="date" />
                    <p class="field-error" id="error-date"></p>
                </div>
            </div>
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">{{ __('ui.visit_form.address') }}</label>
                <input id="f-correspondence_address" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-shadow" placeholder="{{ __('ui.visit_form.address_placeholder') }}" type="text" />
                <p class="field-error" id="error-correspondence_address"></p>
            </div>
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">{{ __('ui.visit_form.location') }}</label>
                <input id="f-location" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-shadow" placeholder="{{ __('ui.visit_form.location_placeholder') }}" type="text" />
                <p class="field-error" id="error-location"></p>
            </div>
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">{{ __('ui.visit_form.location_url') }}</label>
                <div class="flex items-center gap-2">
                    <input id="f-location_url" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-shadow" placeholder="{{ __('ui.visit_form.location_url_placeholder') }}" type="url" readonly />
                    <button id="set-location-btn" class="shrink-0 flex items-center gap-1.5 bg-primary-container text-on-primary-container rounded-lg px-4 py-3 hover:opacity-90 transition-opacity whitespace-nowrap" type="button">
                        <span class="material-symbols-outlined text-[20px]" id="set-location-icon">my_location</span>
                        <span id="set-location-label">{{ __('ui.visit_form.set_current_location') }}</span>
                    </button>
                </div>
                <p class="field-error" id="error-location_url"></p>
            </div>
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2">{{ __('ui.visit_form.visit_type') }} <span class="text-error">*</span></label>
                <div class="flex flex-wrap gap-4" id="visit-type-options"></div>
                <p class="field-error" id="error-type"></p>
            </div>
        </div>
    </div>

    <!-- Section 2: Attendance & Confession -->
    <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] rtl:border-r-4 ltr:border-l-4 border-primary overflow-hidden">
        <button class="w-full flex items-center justify-between p-4 md:p-5 bg-surface-container-lowest hover:bg-surface-container-low transition-colors" onclick="toggleSection('section-2')" type="button">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">church</span>
                <h3 class="font-title-md text-base md:text-lg text-on-surface">{{ __('ui.visit_form.section_attendance') }}</h3>
            </div>
            <span class="material-symbols-outlined text-outline-variant chevron" id="chevron-2">expand_more</span>
        </button>
        <div class="section-content px-4 md:px-5 pb-4 md:pb-5 pt-0 space-y-6" id="section-2">
            <!-- Mass Attendance -->
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-3">{{ __('ui.visit_form.mass_attendance') }} <span class="text-error">*</span></label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3" id="mass-attendance-options"></div>
                <p class="field-error" id="error-mass_attendence"></p>
            </div>
            <div class="h-px bg-outline-variant w-full opacity-50"></div>
            <!-- Confession Toggles -->
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-3">{{ __('ui.visit_form.confession_heading') }}</label>
                <div class="space-y-3">
                    <label class="flex items-center justify-between p-3 border border-outline-variant rounded-lg bg-surface cursor-pointer hover:bg-surface-container-low transition-colors">
                        <span class="text-on-surface">{{ __('ui.visit_form.father') }}</span>
                        <div class="relative">
                            <input id="f-father_confession" class="sr-only peer" type="checkbox" />
                            <div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:rtl:-translate-x-full peer-checked:after:ltr:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:rtl:right-[2px] after:ltr:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </div>
                    </label>
                    <label class="flex items-center justify-between p-3 border border-outline-variant rounded-lg bg-surface cursor-pointer hover:bg-surface-container-low transition-colors">
                        <span class="text-on-surface">{{ __('ui.visit_form.mother') }}</span>
                        <div class="relative">
                            <input id="f-mother_confession" class="sr-only peer" type="checkbox" />
                            <div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:rtl:-translate-x-full peer-checked:after:ltr:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:rtl:right-[2px] after:ltr:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </div>
                    </label>
                    <label class="flex items-center justify-between p-3 border border-outline-variant rounded-lg bg-surface cursor-pointer hover:bg-surface-container-low transition-colors">
                        <span class="text-on-surface">{{ __('ui.visit_form.children') }}</span>
                        <div class="relative">
                            <input id="f-children_confession" class="sr-only peer" type="checkbox" />
                            <div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:rtl:-translate-x-full peer-checked:after:ltr:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:rtl:right-[2px] after:ltr:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </div>
                    </label>
                </div>
            </div>
            <div class="h-px bg-outline-variant w-full opacity-50"></div>
            <!-- Meetings & follow-up Toggles -->
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-3">{{ __('ui.visit_form.meetings_heading') }}</label>
                <div class="space-y-3">
                    <label class="flex items-center justify-between p-3 border border-outline-variant rounded-lg bg-surface cursor-pointer hover:bg-surface-container-low transition-colors">
                        <span class="text-on-surface">{{ __('ui.visit_form.attend_meetings') }}</span>
                        <div class="relative">
                            <input id="f-attend_meetings" class="sr-only peer" type="checkbox" />
                            <div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:rtl:-translate-x-full peer-checked:after:ltr:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:rtl:right-[2px] after:ltr:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </div>
                    </label>
                    <label class="flex items-center justify-between p-3 border border-outline-variant rounded-lg bg-surface cursor-pointer hover:bg-surface-container-low transition-colors">
                        <span class="text-on-surface">{{ __('ui.visit_form.need_from_meeting') }}</span>
                        <div class="relative">
                            <input id="f-need_eftkad_from_meeting" class="sr-only peer" type="checkbox" />
                            <div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:rtl:-translate-x-full peer-checked:after:ltr:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:rtl:right-[2px] after:ltr:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </div>
                    </label>
                    <label class="flex items-center justify-between p-3 border border-outline-variant rounded-lg bg-surface cursor-pointer hover:bg-surface-container-low transition-colors">
                        <span class="text-on-surface">{{ __('ui.visit_form.need_by_father') }}</span>
                        <div class="relative">
                            <input id="f-need_eftkad_by_father" class="sr-only peer" type="checkbox" />
                            <div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:rtl:-translate-x-full peer-checked:after:ltr:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:rtl:right-[2px] after:ltr:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Needs -->
    <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] rtl:border-r-4 ltr:border-l-4 border-primary overflow-hidden">
        <button class="w-full flex items-center justify-between p-4 md:p-5 bg-surface-container-lowest hover:bg-surface-container-low transition-colors" onclick="toggleSection('section-3')" type="button">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">volunteer_activism</span>
                <h3 class="font-title-md text-base md:text-lg text-on-surface">{{ __('ui.visit_form.section_needs') }}</h3>
            </div>
            <span class="material-symbols-outlined text-outline-variant chevron" id="chevron-3">expand_more</span>
        </button>
        <div class="section-content px-4 md:px-5 pb-4 md:pb-5 pt-0 space-y-6" id="section-3">
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-3">{{ __('ui.visit_form.needs_heading') }} <span class="text-error">*</span></label>
                <div class="flex flex-wrap gap-3" id="needs-options"></div>
                <p class="field-error" id="error-needs"></p>
            </div>
            <div class="h-px bg-outline-variant w-full opacity-50"></div>
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-3">{{ __('ui.visit_form.comm_heading') }} <span class="text-error">*</span></label>
                <div class="flex flex-wrap gap-3" id="communication-options"></div>
                <p class="field-error" id="error-communication_means"></p>
            </div>
        </div>
    </div>

    <!-- Section 4: Admin -->
    <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] rtl:border-r-4 ltr:border-l-4 border-primary overflow-hidden">
        <button class="w-full flex items-center justify-between p-4 md:p-5 bg-surface-container-lowest hover:bg-surface-container-low transition-colors" onclick="toggleSection('section-4')" type="button">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">admin_panel_settings</span>
                <h3 class="font-title-md text-base md:text-lg text-on-surface">{{ __('ui.visit_form.section_admin') }}</h3>
            </div>
            <span class="material-symbols-outlined text-outline-variant chevron" id="chevron-4">expand_more</span>
        </button>
        <div class="section-content px-4 md:px-5 pb-4 md:pb-5 pt-0 space-y-4" id="section-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">{{ __('ui.visit_form.father_code') }} <span class="text-error">*</span></label>
                    <select id="f-father_membership_code" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-shadow">
                        <option value="">{{ __('ui.visit_form.father_select_placeholder') }}</option>
                    </select>
                    <p class="field-error" id="error-father_membership_code"></p>
                </div>
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">{{ __('ui.visit_form.servant_code') }} <span class="text-error">*</span></label>
                    <select id="f-servant_membership_code" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-shadow">
                        <option value="">{{ __('ui.visit_form.servant_select_placeholder') }}</option>
                    </select>
                    <p class="field-error" id="error-servant_membership_code"></p>
                </div>
            </div>
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">{{ __('ui.visit_form.general_notes') }}</label>
                <textarea id="f-general_notes" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-shadow min-h-[100px] resize-y" placeholder="{{ __('ui.visit_form.general_notes_placeholder') }}"></textarea>
                <p class="field-error" id="error-general_notes"></p>
            </div>
        </div>
    </div>

    <!-- Save Action -->
    <div class="flex justify-center pt-2">
        <button type="button" id="save-visit-btn" class="w-full max-w-sm bg-primary text-on-primary rounded-lg py-4 px-6 font-title-md text-title-md shadow-lg hover:shadow-xl hover:bg-surface-tint transition-all active:scale-95 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
            <span class="material-symbols-outlined">save</span>
            <span id="save-visit-label">{{ __('ui.visit_form.save_button') }}</span>
        </button>
    </div>
</form>
</div>
@endsection

@section('scripts')
<script>
    function toggleSection(sectionId) {
        const section = document.getElementById(sectionId);
        const chevron = document.getElementById('chevron-' + sectionId.split('-')[1]);
        section.classList.toggle('collapsed');
    }

    const TYPE_ICONS = { 1: 'call', 2: 'home' };
    const NEED_ICONS = { 1: 'elderly', 2: 'healing', 3: 'work', 4: 'school', 5: 'local_dining', 6: 'emergency_home' };
    const COMM_ICONS = { 1: 'chat', 2: 'groups', 3: 'church', 4: 'block' };

    function renderVisitTypeOptions(options) {
        const container = document.getElementById('visit-type-options');
        container.innerHTML = options.map(function (o, i) {
            const icon = TYPE_ICONS[o.value] || 'help';
            return `<label class="flex-1 min-w-[45%] sm:min-w-0 cursor-pointer">
                <input ${i === 0 ? 'checked' : ''} class="peer sr-only" name="visit_type" type="radio" value="${o.value}" />
                <div class="w-full text-center px-4 py-3 border border-outline-variant rounded-lg peer-checked:bg-primary-container peer-checked:border-primary-container peer-checked:text-on-primary-container transition-all">
                    <span class="material-symbols-outlined align-middle rtl:ml-2 ltr:mr-2">${icon}</span>${o.name}
                </div>
            </label>`;
        }).join('');
    }

    function renderMassOptions(options) {
        const container = document.getElementById('mass-attendance-options');
        container.innerHTML = options.map(function (o, i) {
            return `<label class="cursor-pointer">
                <input ${i === 0 ? 'checked' : ''} class="peer sr-only" name="mass_freq" type="radio" value="${o.value}" />
                <div class="text-center px-3 py-2 border border-outline-variant rounded-lg text-sm peer-checked:bg-tertiary-container peer-checked:border-tertiary-container peer-checked:text-on-tertiary-container transition-all">${o.name}</div>
            </label>`;
        }).join('');
    }

    function renderChipOptions(container, options, iconMap, namePrefix) {
        container.innerHTML = options.map(function (o) {
            const icon = iconMap[o.value] || 'label';
            const id = namePrefix + '-' + o.value;
            return `<div class="relative">
                <input class="chip-checkbox sr-only" id="${id}" type="checkbox" value="${o.value}" data-group="${namePrefix}" />
                <label class="flex items-center gap-2 px-4 py-2 border border-outline-variant rounded-full text-sm cursor-pointer transition-colors bg-surface text-on-surface-variant hover:bg-surface-container-low" for="${id}">
                    <span class="material-symbols-outlined text-base">${icon}</span>${o.name}
                </label>
            </div>`;
        }).join('');
    }

    function clearErrors() {
        document.getElementById('form-error').classList.add('hidden');
        document.querySelectorAll('.field-error').forEach(function (el) {
            el.textContent = '';
            el.classList.remove('show');
        });
    }

    function showFieldErrors(errors) {
        Object.keys(errors).forEach(function (field) {
            const key = field.replace(/\.\d+$/, '').split('.')[0];
            const el = document.getElementById('error-' + key);
            if (el) {
                el.textContent = errors[field][0];
                el.classList.add('show');
            }
        });
    }

    function toDMY(isoDate) {
        if (!isoDate) return '';
        const parts = isoDate.split('-'); // YYYY-MM-DD
        if (parts.length !== 3) return isoDate;
        return parts[2] + '-' + parts[1] + '-' + parts[0];
    }

    function boolToggle(id) {
        return document.getElementById(id).checked ? 1 : 0;
    }

    function checkedValues(group) {
        return Array.from(document.querySelectorAll('input[data-group="' + group + '"]:checked'))
            .map(function (el) { return parseInt(el.value, 10); });
    }

    async function loadEnums() {
        const res = await Eftkad.api('/settings/enums');
        if (!res.ok || !res.data) return;
        const data = res.data.data || {};

        renderVisitTypeOptions(data.eftkad_type || []);
        renderMassOptions(data.mass_attendence_type || []);
        renderChipOptions(document.getElementById('needs-options'), data.visits_status || [], NEED_ICONS, 'need');
        renderChipOptions(document.getElementById('communication-options'), data.user_status || [], COMM_ICONS, 'comm');
    }

    async function loadAdminSelects() {
        const res = await Eftkad.api('/settings/filters');
        if (!res.ok || !res.data) return;
        const data = res.data.data || {};

        renderPersonOptions('f-father_membership_code', data.fathers || []);
        renderPersonOptions('f-servant_membership_code', data.servants || []);
    }

    function setCurrentLocation() {
        const btn = document.getElementById('set-location-btn');
        const icon = document.getElementById('set-location-icon');
        const label = document.getElementById('set-location-label');
        const input = document.getElementById('f-location_url');

        if (!navigator.geolocation) {
            alert(UI_TEXT.visit_form.location_not_supported);
            return;
        }

        btn.disabled = true;
        icon.textContent = 'progress_activity';
        icon.classList.add('animate-spin');
        label.textContent = UI_TEXT.visit_form.locating;

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                input.value = 'https://www.google.com/maps?q=' + lat + ',' + lng;

                btn.disabled = false;
                icon.textContent = 'my_location';
                icon.classList.remove('animate-spin');
                label.textContent = UI_TEXT.visit_form.set_current_location;
            },
            function (error) {
                btn.disabled = false;
                icon.textContent = 'my_location';
                icon.classList.remove('animate-spin');
                label.textContent = UI_TEXT.visit_form.set_current_location;

                if (error.code === error.PERMISSION_DENIED) {
                    alert(UI_TEXT.visit_form.location_permission_denied);
                } else {
                    alert(UI_TEXT.visit_form.location_error);
                }
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    function renderPersonOptions(selectId, people) {
        const select = document.getElementById(selectId);
        people.forEach(function (person) {
            const option = document.createElement('option');
            option.value = person.membership_code;
            option.textContent = person.name;
            select.appendChild(option);
        });
    }

    async function submitForm() {
        clearErrors();

        const saveBtn = document.getElementById('save-visit-btn');
        const saveLabel = document.getElementById('save-visit-label');
        saveBtn.disabled = true;
        saveLabel.textContent = UI_TEXT.common.saving;

        const visitTypeEl = document.querySelector('input[name="visit_type"]:checked');
        const massEl = document.querySelector('input[name="mass_freq"]:checked');

        const payload = {
            membership_code: document.getElementById('f-membership_code').value.trim(),
            date: toDMY(document.getElementById('f-date').value),
            correspondence_address: document.getElementById('f-correspondence_address').value.trim(),
            location: document.getElementById('f-location').value.trim(),
            location_url: document.getElementById('f-location_url').value.trim(),
            type: visitTypeEl ? parseInt(visitTypeEl.value, 10) : null,
            mass_attendence: massEl ? parseInt(massEl.value, 10) : null,
            needs: checkedValues('need'),
            communication_means: checkedValues('comm'),
            attend_meetings: boolToggle('f-attend_meetings'),
            need_eftkad_from_meeting: boolToggle('f-need_eftkad_from_meeting') ? '1' : '0',
            need_eftkad_by_father: boolToggle('f-need_eftkad_by_father'),
            father_confession: boolToggle('f-father_confession') ? '1' : '0',
            mother_confession: boolToggle('f-mother_confession') ? '1' : '0',
            children_confession: boolToggle('f-children_confession') ? '1' : '0',
            general_notes: document.getElementById('f-general_notes').value.trim(),
            father_membership_code: document.getElementById('f-father_membership_code').value.trim(),
            servant_membership_code: document.getElementById('f-servant_membership_code').value.trim(),
        };

        const res = await Eftkad.api('/eftkads', { method: 'POST', body: payload });

        saveBtn.disabled = false;
        saveLabel.textContent = UI_TEXT.visit_form.save_button;

        if (res.ok) {
            window.location.href = '/visits';
            return;
        }

        if (res.status === 422 && res.data && res.data.errors) {
            showFieldErrors(res.data.errors);
            document.getElementById('form-error-text').textContent = UI_TEXT.common.review_fields;
            document.getElementById('form-error').classList.remove('hidden');
            return;
        }

        document.getElementById('form-error-text').textContent = (res.data && res.data.message) || UI_TEXT.visit_form.save_error;
        document.getElementById('form-error').classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;

        loadEnums();
        loadAdminSelects();

        document.getElementById('save-visit-btn').addEventListener('click', submitForm);
        document.getElementById('set-location-btn').addEventListener('click', setCurrentLocation);
    });
</script>
@endsection
