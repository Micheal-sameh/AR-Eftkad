@extends('layouts.app')

@php($uiSections = ['common', 'users'])

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
    </div>

    <div class="search-bar">
        <x-icon name="search" :size="19" />
        <input id="users-search" aria-label="{{ __('ui.fg.users.search') }}" placeholder="{{ __('ui.fg.users.search') }}" />
        <kbd>⌘ K</kbd>
    </div>

    <div id="users-loading" class="is-loading-block"><span class="spinner"></span></div>
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
            <div class="modal-actions">
                <button type="button" class="button button-ghost" id="modal-cancel"><span>{{ __('ui.fg.users.cancel') }}</span></button>
                <button type="button" class="button button-primary" id="modal-save">
                    <x-icon name="check" :size="18" />
                    <span id="modal-save-label">{{ __('ui.fg.users.save_changes') }}</span>
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

    function renderRow(u) {
        return `<button type="button" class="user-row" data-id="${u.id}">
            <span class="user-cell"><span class="person-avatar small">${Eftkad.esc(Eftkad.initials(u.name))}</span><strong>${Eftkad.esc(u.name)}</strong></span>
            <span dir="ltr" style="justify-self:start">${Eftkad.esc(u.membership_code || UI_TEXT.common.dash)}</span>
            <span>${typeBadge(u)}</span>
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

        $('user-modal').classList.remove('hidden');
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

    // The role saved in the browser can be stale (it was set at login), so re-read it from the account.
    async function refreshMyRole() {
        const res = await Eftkad.api('/user');
        const me = res && res.ok !== false && res.data && (res.data.data || res.data);
        const u = Eftkad.user();
        if (me && u) {
            u.role = me.role;
            try { localStorage.setItem(Eftkad.USER_KEY, JSON.stringify(u)); } catch (e) {}
        }
    }

    document.addEventListener('DOMContentLoaded', async function () {
        if (!Eftkad.requireAuth()) return;
        await refreshMyRole();
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
    });
</script>
@endsection
