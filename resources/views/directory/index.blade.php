@extends('layouts.app')

@php($uiSections = [])

@php($activeNav = 'directory')

@section('title', __('ui.directory.title') . ' - Eftkad')
@section('page-title', __('ui.directory.title'))
@section('page-title-mobile', __('ui.directory.title'))

@section('content')
<div class="mt-8 -mb-3 md:-mt-4 md:mb-2">
    <p class="font-body-md text-body-md text-on-surface-variant">{{ __('ui.directory.subtitle') }}</p>
</div>

<!-- Search Bar -->
<div class="relative mb-2 max-w-md">
    <div class="absolute inset-y-0 rtl:right-0 ltr:left-0 rtl:pr-3 ltr:pl-3 flex items-center pointer-events-none">
        <span class="material-symbols-outlined text-outline">search</span>
    </div>
    <input id="directory-search" class="block w-full rtl:pl-3 ltr:pr-3 rtl:pr-10 ltr:pl-10 search-input py-3.5 border border-outline-variant/70 rounded-xl leading-5 bg-surface-container-lowest placeholder-outline focus:outline-none focus:ring-2 focus:ring-primary-container/40 focus:border-primary-container font-body-md text-body-md transition-colors" placeholder="{{ __('ui.directory.search_placeholder') }}" type="text" />
</div>

<div id="directory-loading" class="state-box" role="status"><div class="spinner"></div><p class="font-body-md">{{ __('ui.directory.loading') }}</p></div>
<div id="directory-error" class="hidden state-box is-error" role="alert">
    <div class="state-icon"><span class="material-symbols-outlined">error</span></div>
    <p class="font-body-md text-error">{{ __('ui.directory.error') }}</p>
</div>

<div id="directory-lists" class="hidden grid grid-cols-1 md:grid-cols-2 gap-container-margin">
    <!-- Fathers List -->
    <div class="bg-surface-container-lowest rounded-2xl card-shadow border border-outline-variant/70 p-4 md:p-6">
        <h3 class="font-title-md text-[17px] font-bold text-on-surface mb-3 flex items-center gap-2">
            <span class="flex items-center justify-center w-9 h-9 rounded-full bg-primary-fixed text-primary"><span class="material-symbols-outlined text-[20px]">person</span></span>
            {{ __('ui.directory.fathers_list') }}
        </h3>
        <ul class="divide-y divide-surface-container" id="fathers-list"></ul>
        <p class="hidden text-on-surface-variant italic font-body-md text-sm py-3" id="fathers-empty">{{ __('ui.directory.no_results') }}</p>
    </div>
    <!-- Servants List -->
    <div class="bg-surface-container-lowest rounded-2xl card-shadow border border-outline-variant/70 p-4 md:p-6">
        <h3 class="font-title-md text-[17px] font-bold text-on-surface mb-3 flex items-center gap-2">
            <span class="flex items-center justify-center w-9 h-9 rounded-full bg-primary-fixed text-primary"><span class="material-symbols-outlined text-[20px]">group</span></span>
            {{ __('ui.directory.servants_list') }}
        </h3>
        <ul class="divide-y divide-surface-container" id="servants-list"></ul>
        <p class="hidden text-on-surface-variant italic font-body-md text-sm py-3" id="servants-empty">{{ __('ui.directory.no_results') }}</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let fathers = [];
    let servants = [];

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function renderList(containerId, emptyId, people, query) {
        const list = document.getElementById(containerId);
        const empty = document.getElementById(emptyId);
        const filtered = people.filter(function (p) {
            if (!query) return true;
            const q = query.toLowerCase();
            return (p.name || '').toLowerCase().includes(q) || (p.membership_code || '').toLowerCase().includes(q);
        });

        list.innerHTML = filtered.map(function (p) {
            return `<li class="py-2.5 flex justify-between items-center gap-3 hover:bg-surface-container-low transition-colors px-2 rounded-xl -mx-2">
                <span class="flex items-center gap-3 min-w-0">
                    <span class="avatar" aria-hidden="true">${escapeHtml((p.name || '?').trim().charAt(0).toUpperCase())}</span>
                    <span class="font-body-md text-body-md text-on-background font-semibold truncate">${escapeHtml(p.name)}</span>
                </span>
                <span class="pill pill-muted flex-shrink-0">${escapeHtml(p.membership_code)}</span>
            </li>`;
        }).join('');

        empty.classList.toggle('hidden', filtered.length > 0);
    }

    function applySearch() {
        const query = document.getElementById('directory-search').value.trim();
        renderList('fathers-list', 'fathers-empty', fathers, query);
        renderList('servants-list', 'servants-empty', servants, query);
    }

    async function loadDirectory() {
        const loading = document.getElementById('directory-loading');
        const errorBox = document.getElementById('directory-error');
        const lists = document.getElementById('directory-lists');

        const res = await Eftkad.cachedApi('/settings/filters');

        loading.classList.add('hidden');

        if (!res.ok || !res.data) {
            errorBox.classList.remove('hidden');
            return;
        }

        fathers = (res.data.data && res.data.data.fathers) || [];
        servants = (res.data.data && res.data.data.servants) || [];

        lists.classList.remove('hidden');
        applySearch();
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;
        loadDirectory();
        document.getElementById('directory-search').addEventListener('input', applySearch);
    });
</script>
@endsection
