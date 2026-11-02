@extends('layouts.app')

@php($uiSections = ['common', 'users'])

@php($activeNav = 'users')

@section('title', __('ui.users.title') . ' - Eftkad')
@section('page-title', __('ui.users.title'))
@section('page-title-mobile', __('ui.users.title'))

@section('content')
<div class="mt-8 -mb-3 md:-mt-4 md:mb-2">
    <p class="font-body-md text-body-md text-on-surface-variant">{{ __('ui.users.subtitle') }}</p>
</div>

<!-- Search Bar -->
<div class="relative mb-2 max-w-md">
    <div class="absolute inset-y-0 rtl:right-0 ltr:left-0 rtl:pr-3 ltr:pl-3 flex items-center pointer-events-none">
        <span class="material-symbols-outlined text-outline">search</span>
    </div>
    <input id="users-search" class="block w-full rtl:pl-3 ltr:pr-3 rtl:pr-10 ltr:pl-10 search-input py-3.5 border border-outline-variant/70 rounded-xl leading-5 bg-surface-container-lowest placeholder-outline focus:outline-none focus:ring-2 focus:ring-primary-container/40 focus:border-primary-container font-body-md text-body-md transition-colors" placeholder="{{ __('ui.users.search_placeholder') }}" type="text" />
</div>

<div id="users-loading" class="state-box" role="status"><div class="spinner"></div><p class="font-body-md">{{ __('ui.users.loading') }}</p></div>
<div id="users-empty" class="hidden state-box">
    <div class="state-icon"><span class="material-symbols-outlined">search</span></div>
    <p class="font-title-md text-[18px] text-on-surface">{{ __('ui.users.empty') }}</p>
</div>
<div id="users-error" class="hidden state-box is-error" role="alert">
    <div class="state-icon"><span class="material-symbols-outlined">error</span></div>
    <p class="font-body-md text-error">{{ __('ui.users.error') }}</p>
</div>

<div class="bg-surface-container-lowest rounded-2xl card-shadow border border-outline-variant/70 overflow-hidden">
    <ul class="rise divide-y divide-surface-container" id="users-list"></ul>
</div>
@endsection

@section('scripts')
<script>
    let allUsers = [];

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function typeBadge(u) {
        if (!u.type) {
            return '<span class="pill pill-error">' + UI_TEXT.users.pending_type + '</span>';
        }
        return `<span class="pill pill-primary">${escapeHtml(u.type.name)}</span>`;
    }

    function renderRow(u) {
        return `<li class="p-3 sm:p-4 flex items-center justify-between gap-2 sm:gap-4 hover:bg-surface-container-low transition-colors">
            <a href="/users/${u.id}" class="flex-grow min-w-0 flex items-center gap-3">
                <span class="avatar" aria-hidden="true">${escapeHtml((u.name || '?').trim().charAt(0).toUpperCase())}</span>
                <span class="min-w-0 flex-1">
                    <p class="font-body-md text-body-md text-on-surface font-semibold truncate">${escapeHtml(u.name)}</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant truncate">${escapeHtml(u.membership_code || UI_TEXT.common.dash)}</p>
                </span>
            </a>
            <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                ${typeBadge(u)}
                <a href="/users/${u.id}/edit" class="pressable p-2 rounded-full hover:bg-primary-fixed/60 text-on-surface-variant hover:text-primary transition-colors" title="${UI_TEXT.users.edit_type_title}">
                    <span class="material-symbols-outlined text-[20px]">edit</span>
                </a>
            </div>
        </li>`;
    }

    function applySearch() {
        const query = document.getElementById('users-search').value.trim().toLowerCase();
        const filtered = allUsers.filter(function (u) {
            if (!query) return true;
            return (u.name || '').toLowerCase().includes(query) || (u.membership_code || '').toLowerCase().includes(query);
        });

        document.getElementById('users-list').innerHTML = filtered.map(renderRow).join('');
        document.getElementById('users-empty').classList.toggle('hidden', filtered.length > 0);
    }

    async function loadUsers() {
        const loading = document.getElementById('users-loading');
        const errorBox = document.getElementById('users-error');
        loading.classList.remove('hidden');
        errorBox.classList.add('hidden');

        const res = await Eftkad.api('/users');

        loading.classList.add('hidden');

        if (!res.ok || !res.data) {
            errorBox.classList.remove('hidden');
            return;
        }

        allUsers = res.data.data || [];
        applySearch();
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;
        loadUsers();
        document.getElementById('users-search').addEventListener('input', applySearch);
    });
</script>
@endsection
