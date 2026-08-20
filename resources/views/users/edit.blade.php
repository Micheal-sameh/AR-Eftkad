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

<form id="type-form" class="hidden space-y-6">
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
        <div id="role-section" class="hidden">
            <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2">{{ __('ui.user_edit.role_label') }}</label>
            <div class="flex flex-wrap gap-4" id="role-options"></div>
            <p class="field-error" id="error-role"></p>
        </div>
    </div>

    <div class="flex justify-center pt-2">
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

    function renderRoleOptions(options, currentValue) {
        const container = document.getElementById('role-options');
        container.innerHTML = options.map(function (o) {
            const checked = String(o.value) === String(currentValue) ? 'checked' : '';
            return `<label class="flex-1 min-w-[45%] sm:min-w-0 cursor-pointer">
                <input ${checked} class="peer sr-only" name="user_role" type="radio" value="${o.value}" />
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

    async function loadTypeOptions(currentValue, currentRole) {
        const res = await Eftkad.api('/settings/enums');
        if (!res.ok || !res.data) return;
        renderTypeOptions((res.data.data && res.data.data.user_type) || [], currentValue);

        const currentUser = Eftkad.user();
        const isSuperadmin = currentUser && currentUser.role && currentUser.role.value === 'superadmin';
        if (isSuperadmin) {
            document.getElementById('role-section').classList.remove('hidden');
            renderRoleOptions((res.data.data && res.data.data.user_role) || [], currentRole);
        }
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

        await loadTypeOptions(user.type ? user.type.value : null, user.role ? user.role.value : null);
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

        if (!res.ok) {
            saveBtn.disabled = false;
            saveLabel.textContent = UI_TEXT.user_edit.save_button;

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
            return;
        }

        const roleSection = document.getElementById('role-section');
        const roleEl = document.querySelector('input[name="user_role"]:checked');
        if (!roleSection.classList.contains('hidden') && roleEl) {
            const roleRes = await Eftkad.api('/users/' + USER_ID + '/role', { method: 'PATCH', body: { role: roleEl.value } });

            saveBtn.disabled = false;
            saveLabel.textContent = UI_TEXT.user_edit.save_button;

            if (!roleRes.ok) {
                if (roleRes.status === 422 && roleRes.data && roleRes.data.errors) {
                    const errors = roleRes.data.errors;
                    const el = document.getElementById('error-role');
                    if (el && errors.role) el.textContent = errors.role[0];
                    document.getElementById('form-error-text').textContent = UI_TEXT.common.review_fields;
                    document.getElementById('form-error').classList.remove('hidden');
                    return;
                }

                document.getElementById('form-error-text').textContent = (roleRes.data && roleRes.data.message) || UI_TEXT.user_edit.save_error;
                document.getElementById('form-error').classList.remove('hidden');
                return;
            }

            window.location.href = '/users/' + USER_ID;
            return;
        }

        saveBtn.disabled = false;
        saveLabel.textContent = UI_TEXT.user_edit.save_button;
        window.location.href = '/users/' + USER_ID;
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;
        loadUser();
    });
</script>
@endsection
