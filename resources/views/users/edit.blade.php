@extends('layouts.app')

@php($activeNav = 'users')

@section('title', __('ui.user_edit.title'))
@section('page-title', __('ui.user_edit.heading'))
@section('page-title-mobile', __('ui.user_edit.heading'))

@section('content')
<div class="md:max-w-2xl">
<div class="hidden md:flex justify-between items-center mb-2 -mt-4 border-b border-outline-variant pb-4">
    <button type="button" onclick="history.back()" class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined">{{ app()->getLocale() === 'ar' ? 'arrow_forward' : 'arrow_back' }}</span>
        <span>{{ __('ui.common.back') }}</span>
    </button>
</div>

<div id="form-error" class="hidden mb-2 px-4 py-3 rounded-lg bg-error-container text-on-error-container font-body-md text-sm flex items-center gap-2">
    <span class="material-symbols-outlined" style="font-size: 20px;">error</span>
    <span id="form-error-text">{{ __('ui.user_edit.generic_error') }}</span>
</div>

<div id="edit-loading" class="text-center py-12 text-on-surface-variant">{{ __('ui.user_edit.loading') }}</div>
<div id="edit-load-error" class="hidden text-center py-12 text-error">{{ __('ui.user_edit.error') }}</div>

<form id="type-form" class="hidden space-y-6 pb-24">
    <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] rtl:border-r-4 ltr:border-l-4 border-primary p-4 md:p-5 space-y-4">
        <div>
            <p class="font-title-md text-base md:text-lg text-on-surface" id="f-name">—</p>
            <p class="font-label-sm text-label-sm text-on-surface-variant" id="f-membership_code">—</p>
        </div>
        <div>
            <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2">{{ __('ui.user_edit.type_label') }}</label>
            <div class="flex flex-wrap gap-4" id="type-options"></div>
            <p class="field-error" id="error-type"></p>
        </div>
    </div>

    <div class="safe-bottom fixed bottom-16 md:bottom-0 left-0 w-full md:rtl:w-[calc(100%-16rem)] md:ltr:w-[calc(100%-16rem)] bg-surface/90 backdrop-blur-sm border-t border-outline-variant p-4 z-40 flex justify-center shadow-[0_-10px_40px_rgba(0,0,0,0.05)] md:shadow-none">
        <button type="button" id="save-type-btn" onclick="submitForm()" class="w-full max-w-sm bg-primary text-on-primary rounded-lg py-4 px-6 font-title-md text-title-md shadow-lg hover:shadow-xl hover:bg-surface-tint transition-all active:scale-95 flex items-center justify-center gap-2">
            <span class="material-symbols-outlined">save</span>
            <span id="save-type-label">{{ __('ui.user_edit.save_button') }}</span>
        </button>
    </div>
</form>
</div>
@endsection

@section('scripts')
<script>
    const USER_ID = {{ (int) $id }};

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function renderTypeOptions(options, currentValue) {
        const container = document.getElementById('type-options');
        container.innerHTML = options.map(function (o) {
            const checked = String(o.value) === String(currentValue) ? 'checked' : '';
            return `<label class="flex-1 min-w-[45%] sm:min-w-0 cursor-pointer">
                <input ${checked} class="peer sr-only" name="user_type" type="radio" value="${o.value}" />
                <div class="w-full text-center px-4 py-3 border border-outline-variant rounded-lg peer-checked:bg-primary-container peer-checked:border-primary-container peer-checked:text-on-primary-container transition-all">
                    ${escapeHtml(o.name)}
                </div>
            </label>`;
        }).join('');
    }

    function clearErrors() {
        document.getElementById('form-error').classList.add('hidden');
        document.querySelectorAll('.field-error').forEach(function (el) {
            el.textContent = '';
        });
    }

    async function loadTypeOptions(currentValue) {
        const res = await Eftkad.api('/settings/enums');
        if (!res.ok || !res.data) return;
        renderTypeOptions((res.data.data && res.data.data.user_type) || [], currentValue);
    }

    async function loadUser() {
        const loading = document.getElementById('edit-loading');
        const errorBox = document.getElementById('edit-load-error');
        const form = document.getElementById('type-form');

        const res = await Eftkad.api('/users/' + USER_ID);

        loading.classList.add('hidden');

        if (!res.ok || !res.data || !res.data.data) {
            errorBox.classList.remove('hidden');
            return;
        }

        const user = res.data.data;
        document.getElementById('f-name').textContent = user.name || UI_TEXT.common.dash;
        document.getElementById('f-membership_code').textContent = user.membership_code || UI_TEXT.common.dash;

        await loadTypeOptions(user.type ? user.type.value : null);
        form.classList.remove('hidden');
    }

    async function submitForm() {
        clearErrors();

        const saveBtn = document.getElementById('save-type-btn');
        const saveLabel = document.getElementById('save-type-label');
        saveBtn.disabled = true;
        saveLabel.textContent = UI_TEXT.common.saving;

        const typeEl = document.querySelector('input[name="user_type"]:checked');
        const payload = { type: typeEl ? parseInt(typeEl.value, 10) : null };

        const res = await Eftkad.api('/users/' + USER_ID + '/type', { method: 'PATCH', body: payload });

        saveBtn.disabled = false;
        saveLabel.textContent = UI_TEXT.user_edit.save_button;

        if (res.ok) {
            window.location.href = '/users/' + USER_ID;
            return;
        }

        if (res.status === 422 && res.data && res.data.errors) {
            const errors = res.data.errors;
            const el = document.getElementById('error-type');
            if (el && errors.type) el.textContent = errors.type[0];
            document.getElementById('form-error-text').textContent = UI_TEXT.common.review_fields;
            document.getElementById('form-error').classList.remove('hidden');
            return;
        }

        document.getElementById('form-error-text').textContent = (res.data && res.data.message) || UI_TEXT.user_edit.save_error;
        document.getElementById('form-error').classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;
        loadUser();
    });
</script>
@endsection
