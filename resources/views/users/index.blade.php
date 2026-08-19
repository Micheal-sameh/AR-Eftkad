@extends('layouts.app')

@php($activeNav = 'users')

@section('title', 'Eftkad - Users')
@section('page-title', 'إدارة المستخدمين')
@section('page-title-mobile', 'إدارة المستخدمين')

@section('content')
<div class="-mt-4 mb-2">
    <p class="font-body-md text-body-md text-on-surface-variant">عرض المستخدمين وتحديد نوعهم (أب كاهن / خادم).</p>
</div>

<!-- Search Bar -->
<div class="relative mb-2 max-w-md">
    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        <span class="material-symbols-outlined text-outline">search</span>
    </div>
    <input id="users-search" class="block w-full pl-3 pr-10 py-3 border border-outline-variant rounded-lg leading-5 bg-surface-container-lowest placeholder-outline focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary font-body-md text-body-md sm:text-sm transition-colors" placeholder="ابحث بالاسم أو كود العضوية..." type="text" />
</div>

<div id="users-loading" class="text-center py-12 text-on-surface-variant">Loading...</div>
<div id="users-empty" class="hidden text-center py-12 text-on-surface-variant">No users found.</div>
<div id="users-error" class="hidden text-center py-12 text-error">Failed to load users.</div>

<div class="bg-surface-container-lowest rounded-xl shadow-[0px_4px_20px_rgba(0,0,0,0.05)] border border-surface-container overflow-hidden">
    <ul class="divide-y divide-surface-container" id="users-list"></ul>
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
            return '<span class="px-2 py-1 rounded-md bg-error-container text-on-error-container font-label-sm text-label-sm font-bold">بانتظار التحديد</span>';
        }
        return `<span class="px-2 py-1 rounded-md bg-primary-container text-on-primary-container font-label-sm text-label-sm font-bold">${escapeHtml(u.type.name)}</span>`;
    }

    function renderRow(u) {
        return `<li class="p-4 flex items-center justify-between gap-4 hover:bg-surface-container-low transition-colors">
            <a href="/users/${u.id}" class="flex-grow min-w-0">
                <p class="font-body-md text-body-md text-on-surface font-medium truncate">${escapeHtml(u.name)}</p>
                <p class="font-label-sm text-label-sm text-on-surface-variant">${escapeHtml(u.membership_code || '—')}</p>
            </a>
            <div class="flex items-center gap-3 flex-shrink-0">
                ${typeBadge(u)}
                <a href="/users/${u.id}/edit" class="p-2 rounded-full hover:bg-surface-container text-on-surface-variant hover:text-primary transition-colors" title="تعديل النوع">
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
