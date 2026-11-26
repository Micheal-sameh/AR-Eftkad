@extends('layouts.app')

@php($uiSections = ['common', 'users', 'user_edit'])

@php($activeNav = 'users')

@section('title', __('ui.fg.users.title') . ' - Eftkad')

@section('content')
<div class="page-content">
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ __('ui.fg.users.eyebrow') }}</div>
            <div class="page-title">{{ __('ui.fg.users.title') }}</div>
            <p>{{ __('ui.fg.users.subtitle') }}</p>
        </div>
        <button type="button" class="button button-primary" id="add-user-btn">
            <x-icon name="plus" :size="18" />
            <span>{{ __('ui.fg.users.add') }}</span>
        </button>
    </div>

    <div class="search-bar">
        <x-icon name="search" :size="19" />
        <input id="users-search" aria-label="{{ __('ui.fg.users.search') }}" placeholder="{{ __('ui.fg.users.search') }}" />
        <kbd>⌘ K</kbd>
    </div>

    <div id="users-loading" aria-label="Loading" style="display:grid;gap:.6rem">@include('partials.skeleton-people', ['count' => 6])</div>
    <div id="users-error" class="state-note is-error hidden">{{ __('ui.users.error') }}</div>
    <div id="users-empty" class="state-note hidden">{{ __('ui.users.empty') }}</div>

    <div class="users-table hidden" id="users-table">
        <div class="users-table-head">
            <span>{{ __('ui.fg.users.col_user') }}</span>
            <span>{{ __('ui.fg.users.col_code') }}</span>
            <span>{{ __('ui.fg.users.col_type') }}</span>
            <span></span>
        </div>
        <div id="users-list"></div>
    </div>

    <div class="modal-wrap hidden" id="user-modal">
        <div class="modal-backdrop" id="modal-backdrop"></div>
        <div class="modal">
            <div class="section-header">
                <div class="section-title">{{ __('ui.fg.users.edit_type') }}</div>
                <button type="button" class="icon-button" id="modal-close" aria-label="Close"><x-icon name="close" /></button>
            </div>
            <div class="modal-profile">
                <div class="person-avatar large" id="m-initials">··</div>
                <div><strong id="m-name">—</strong><span id="m-code">—</span></div>
            </div>
            <div class="inline-error hidden" id="m-error" style="margin-bottom:.85rem"><x-icon name="alert" :size="16" /><span id="m-error-text"></span></div>
            <label class="field">
                <span class="field-label">{{ __('ui.fg.users.type') }}</span>
                <span class="input-shell"><select id="m-type"></select><x-icon name="chevron" :size="16" /></span>
            </label>
            <label class="field hidden" id="m-role-field">
                <span class="field-label">{{ __('ui.fg.users.role') }}</span>
                <span class="input-shell"><select id="m-role"></select><x-icon name="chevron" :size="16" /></span>
            </label>
            <p class="hidden" id="m-delete-hint" style="margin: .8rem 0 0; color: var(--ink-soft); font-size: .72rem">{{ __('ui.fg.users.delete_hint') }}</p>
            <div class="modal-actions">
                {{-- Two taps: the first arms it (label changes), the second deletes. --}}
                <button type="button" class="button button-danger hidden" id="modal-delete" style="margin-inline-end: auto">
                    <span id="modal-delete-label">{{ __('ui.fg.users.delete') }}</span>
                </button>
                <button type="button" class="button button-ghost" id="modal-cancel"><span>{{ __('ui.fg.users.cancel') }}</span></button>
                <button type="button" class="button button-primary" id="modal-save">
                    <x-icon name="check" :size="18" />
                    <span id="modal-save-label">{{ __('ui.fg.users.save_changes') }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Add a father/servant before their first sign-in; SSO links the account by membership code. --}}
    <div class="modal-wrap hidden" id="add-modal">
        <div class="modal-backdrop" data-add-close></div>
        <div class="modal">
            <div class="section-header">
                <div class="section-title">{{ __('ui.fg.users.add_title') }}</div>
                <button type="button" class="icon-button" data-add-close aria-label="Close"><x-icon name="close" /></button>
            </div>
            <div class="inline-error hidden" id="a-error" style="margin-bottom:.85rem"><x-icon name="alert" :size="16" /><span id="a-error-text"></span></div>
            <div class="sheet-fields">
                <label class="field">
                    <span class="field-label">{{ __('ui.fg.users.name') }} *</span>
                    <span class="input-shell"><input id="a-name" type="text" maxlength="255" autocomplete="off" /></span>
                </label>
                <label class="field">
                    <span class="field-label">{{ __('ui.fg.users.col_code') }} *</span>
                    <span class="input-shell"><input id="a-code" type="text" maxlength="50" dir="ltr" placeholder="E1C1FxxxNRx" style="text-transform: uppercase" autocomplete="off" /></span>
                </label>
                <label class="field">
                    <span class="field-label">{{ __('ui.fg.users.type') }} *</span>
                    <span class="input-shell"><select id="a-type"></select><x-icon name="chevron" :size="16" /></span>
                </label>
                <label class="field">
                    <span class="field-label">{{ __('ui.fg.users.phone') }}</span>
                    <span class="input-shell"><input id="a-phone" type="tel" maxlength="30" dir="ltr" autocomplete="off" /></span>
                </label>
            </div>
            <p style="margin: .8rem 0 0; color: var(--ink-soft); font-size: .72rem">{{ __('ui.fg.users.add_hint') }}</p>
            <div class="modal-actions">
                <button type="button" class="button button-ghost" data-add-close><span>{{ __('ui.fg.users.cancel') }}</span></button>
                <button type="button" class="button button-primary" id="add-save">
                    <x-icon name="check" :size="18" />
                    <span id="add-save-label">{{ __('ui.fg.users.create') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const $ = function (id) { return document.getElementById(id); };
    let allUsers = [];

    function typeBadge(u) {
        if (!u.type) return '<span class="badge badge-gold">' + Eftkad.esc(UI_TEXT.users.pending_type) + '</span>';
        return '<span class="badge ' + (u.type.value === 1 ? 'badge-blue' : 'badge-green') + '">' + Eftkad.esc(u.type.name) + '</span>';
    }

    // Shown for anything above a plain user, so admins and the owner are visible in the list.
    function roleBadge(u) {
        if (!u.role || u.role.value === 'user') return '';
        return '<span class="badge badge-gold">' + Eftkad.esc(u.role.name) + '</span>';
    }

    function renderRow(u) {
        return `<button type="button" class="user-row" data-id="${u.id}">
            <span class="user-cell"><span class="person-avatar small">${Eftkad.esc(Eftkad.initials(u.name))}</span><strong>${Eftkad.esc(u.name)}</strong></span>
            <span dir="ltr" style="justify-self:start">${Eftkad.esc(u.membership_code || UI_TEXT.common.dash)}</span>
            <span style="display:flex;flex-wrap:wrap;gap:.3rem">${typeBadge(u)}${roleBadge(u)}</span>
            ${Eftkad.icon('chevron', 18)}
        </button>`;
    }

    function applySearch() {
        const q = $('users-search').value.trim().toLowerCase();
        const filtered = allUsers.filter(function (u) {
            return !q || (u.name || '').toLowerCase().includes(q) || (u.membership_code || '').toLowerCase().includes(q);
        });
        $('users-list').innerHTML = filtered.map(renderRow).join('');
        $('users-table').classList.toggle('hidden', filtered.length === 0);
        $('users-empty').classList.toggle('hidden', filtered.length > 0);
    }

    async function loadUsers() {
        const res = await Eftkad.api('/users');
        $('users-loading').classList.add('hidden');

        if (!res.ok || !res.data) {
            $('users-error').classList.remove('hidden');
            return;
        }
        allUsers = res.data.data || [];
        applySearch();
    }

    let enums = {};
    let editing = null;

    function optionsHtml(list, current) {
        return (list || []).map(function (o) {
            return '<option value="' + Eftkad.esc(o.value) + '"' + (String(o.value) === String(current) ? ' selected' : '') + '>' + Eftkad.esc(o.name) + '</option>';
        }).join('');
    }

    const RANKS = { user: 0, admin: 1, superadmin: 2, owner: 3 };

    function myRole() {
        const me = Eftkad.user();
        return me && me.role ? me.role.value : 'user';
    }

    // Mirrors the API: the owner manages everyone, anyone else only users ranked below them.
    function canManage(u) {
        const mine = myRole();
        return mine === 'owner' || (RANKS[u.role ? u.role.value : 'user'] || 0) < (RANKS[mine] || 0);
    }

    function openModal(u) {
        editing = u;
        $('m-initials').textContent = Eftkad.initials(u.name);
        $('m-name').textContent = u.name || UI_TEXT.common.dash;
        $('m-code').textContent = u.membership_code || UI_TEXT.common.dash;
        $('m-error').classList.add('hidden');
        const mine = myRole();
        // Admins only assign/edit servants; superadmin and owner pick any type.
        const types = (enums.user_type || []).filter(function (t) { return mine !== 'admin' || t.value === 2; });
        $('m-type').innerHTML = optionsHtml(types, u.type ? u.type.value : '');
        if (!u.type) $('m-type').insertAdjacentHTML('afterbegin', '<option value="" selected disabled>' + Eftkad.esc(UI_TEXT.users.pending_type) + '</option>');

        // Superadmins hand out roles below their own; the owner hands out any.
        const canAssignRole = mine === 'superadmin' || mine === 'owner';
        const roles = (enums.user_role || []).filter(function (r) { return mine === 'owner' || RANKS[r.value] < RANKS[mine]; });
        $('m-role-field').classList.toggle('hidden', !canAssignRole);
        if (canAssignRole) $('m-role').innerHTML = optionsHtml(roles, u.role ? u.role.value : '');

        // Nobody deletes themselves (the API enforces this too).
        const me = Eftkad.user();
        const canDelete = !(me && String(me.id) === String(u.id));
        $('modal-delete').classList.toggle('hidden', !canDelete);
        $('m-delete-hint').classList.toggle('hidden', !canDelete);
        disarmDelete();

        $('user-modal').classList.remove('hidden');
    }

    let deleteArmed = false;
    function disarmDelete() {
        deleteArmed = false;
        $('modal-delete').disabled = false;
        $('modal-delete-label').textContent = UI_TEXT.fg.users.delete;
    }

    async function deleteUser() {
        if (!editing) return;
        if (!deleteArmed) {
            deleteArmed = true;
            $('modal-delete-label').textContent = UI_TEXT.fg.users.delete_confirm;
            return;
        }
        $('modal-delete').disabled = true;
        $('m-error').classList.add('hidden');
        const res = await Eftkad.api('/users/' + editing.id, { method: 'DELETE' });
        if (!res.ok) {
            modalError(res, UI_TEXT.user_edit.save_error);
            disarmDelete();
            return;
        }
        closeModal();
        clearPeopleCache();
        Eftkad.toast(UI_TEXT.fg.users.deleted);
        loadUsers();
    }

    // The father/servant lists are cached in the browser; drop them so other pages pick up the change.
    function clearPeopleCache() {
        try {
            Object.keys(localStorage).filter(function (k) { return k.indexOf(Eftkad.CACHE_PREFIX) === 0 && k.indexOf('/settings/filters') !== -1; })
                .forEach(function (k) { localStorage.removeItem(k); });
        } catch (e) {}
    }

    function closeModal() {
        $('user-modal').classList.add('hidden');
        editing = null;
    }

    function modalError(res, fallback) {
        const errs = res.data && res.data.errors;
        $('m-error-text').textContent = (errs && Object.values(errs)[0] && Object.values(errs)[0][0]) || (res.data && res.data.message) || fallback;
        $('m-error').classList.remove('hidden');
    }

    async function saveModal() {
        if (!editing) return;
        const btn = $('modal-save');
        const label = $('modal-save-label');
        btn.disabled = true;
        label.textContent = UI_TEXT.common.saving;
        $('m-error').classList.add('hidden');

        const typeVal = $('m-type').value;
        if (typeVal) {
            const res = await Eftkad.api('/users/' + editing.id + '/type', { method: 'PATCH', body: { type: parseInt(typeVal, 10) } });
            if (!res.ok) { modalError(res, UI_TEXT.user_edit.save_error); btn.disabled = false; label.textContent = UI_TEXT.fg.users.save_changes; return; }
        }
        if (!$('m-role-field').classList.contains('hidden') && $('m-role').value) {
            const res = await Eftkad.api('/users/' + editing.id + '/role', { method: 'PATCH', body: { role: $('m-role').value } });
            if (!res.ok) { modalError(res, UI_TEXT.user_edit.save_error); btn.disabled = false; label.textContent = UI_TEXT.fg.users.save_changes; return; }
        }

        btn.disabled = false;
        label.textContent = UI_TEXT.fg.users.save_changes;
        closeModal();
        loadUsers();
    }

    function openAddModal() {
        ['a-name', 'a-code', 'a-phone'].forEach(function (id) { $(id).value = ''; });
        $('a-type').innerHTML = '<option value="" selected disabled>' + Eftkad.esc(UI_TEXT.users.pending_type) + '</option>' + optionsHtml(enums.user_type, '');
        $('a-error').classList.add('hidden');
        $('add-modal').classList.remove('hidden');
        $('a-name').focus();
    }

    async function saveAdd() {
        const btn = $('add-save');
        const label = $('add-save-label');
        $('a-error').classList.add('hidden');
        btn.disabled = true;
        label.textContent = UI_TEXT.common.saving;

        const body = {
            name: $('a-name').value.trim(),
            membership_code: $('a-code').value.trim().toUpperCase(),
            type: $('a-type').value ? parseInt($('a-type').value, 10) : null,
        };
        const phone = $('a-phone').value.trim();
        if (phone) body.phone = phone;

        const res = await Eftkad.api('/users', { method: 'POST', body: body });
        btn.disabled = false;
        label.textContent = UI_TEXT.fg.users.create;

        if (!res.ok) {
            const errs = res.data && res.data.errors;
            $('a-error-text').textContent = (errs && Object.values(errs)[0] && Object.values(errs)[0][0]) || (res.data && res.data.message) || UI_TEXT.user_edit.save_error;
            $('a-error').classList.remove('hidden');
            return;
        }
        $('add-modal').classList.add('hidden');
        // The new person must show up in the father/servant pickers on other pages.
        clearPeopleCache();
        Eftkad.toast(UI_TEXT.fg.users.added);
        loadUsers();
    }

    document.addEventListener('DOMContentLoaded', async function () {
        if (!Eftkad.requireAuth()) return;
        // The cached user is refreshed once per app open (see Eftkad.syncUser), not on every page.
        await Eftkad.syncUser();
        if (myRole() === 'user') { window.location.replace('/visits'); return; }
        loadUsers();
        Eftkad.cachedApi('/settings/enums').then(function (res) {
            if (res.ok && res.data) enums = res.data.data || {};
        });
        $('users-search').addEventListener('input', applySearch);
        $('users-list').addEventListener('click', function (e) {
            const row = e.target.closest('.user-row');
            if (!row) return;
            const u = allUsers.find(function (x) { return String(x.id) === row.dataset.id; });
            if (u && canManage(u)) openModal(u);
        });
        $('modal-backdrop').addEventListener('click', closeModal);
        $('modal-close').addEventListener('click', closeModal);
        $('modal-cancel').addEventListener('click', closeModal);
        $('modal-save').addEventListener('click', saveModal);
        $('modal-delete').addEventListener('click', deleteUser);
        $('add-user-btn').addEventListener('click', openAddModal);
        $('add-save').addEventListener('click', saveAdd);
        document.querySelectorAll('[data-add-close]').forEach(function (el) {
            el.addEventListener('click', function () { $('add-modal').classList.add('hidden'); });
        });
    });
</script>
@endsection
