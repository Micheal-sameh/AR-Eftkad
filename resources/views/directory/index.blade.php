@extends('layouts.app')

@php($activeNav = 'directory')

@section('title', 'Eftkad - Reference Directory')
@section('page-title', 'دليل الخدمة')
@section('page-title-mobile', 'دليل الخدمة')

@section('content')
<div class="-mt-4 mb-2">
    <p class="font-body-md text-body-md text-on-surface-variant">بحث سريع عن الأباء والخدام.</p>
</div>

<!-- Search Bar -->
<div class="relative mb-2 max-w-md">
    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        <span class="material-symbols-outlined text-outline">search</span>
    </div>
    <input id="directory-search" class="block w-full pl-3 pr-10 py-3 border border-outline-variant rounded-lg leading-5 bg-surface-container-lowest placeholder-outline focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary font-body-md text-body-md sm:text-sm transition-colors" placeholder="ابحث بالاسم أو الكود..." type="text" />
</div>

<div id="directory-loading" class="text-center py-8 text-on-surface-variant">Loading...</div>
<div id="directory-error" class="hidden text-center py-8 text-error">Failed to load directory.</div>

<div id="directory-lists" class="hidden grid grid-cols-1 md:grid-cols-2 gap-container-margin">
    <!-- Fathers List -->
    <div class="bg-surface-container-lowest rounded-xl shadow-[0px_4px_20px_rgba(0,0,0,0.05)] border border-surface-container p-card-padding">
        <h3 class="font-title-md text-title-md text-primary mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined">person</span>
            قائمة الآباء
        </h3>
        <ul class="divide-y divide-surface-container" id="fathers-list"></ul>
        <p class="hidden text-on-surface-variant italic font-body-md text-sm py-3" id="fathers-empty">لا توجد نتائج</p>
    </div>
    <!-- Servants List -->
    <div class="bg-surface-container-lowest rounded-xl shadow-[0px_4px_20px_rgba(0,0,0,0.05)] border border-surface-container p-card-padding">
        <h3 class="font-title-md text-title-md text-primary mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined">group</span>
            قائمة الخدام
        </h3>
        <ul class="divide-y divide-surface-container" id="servants-list"></ul>
        <p class="hidden text-on-surface-variant italic font-body-md text-sm py-3" id="servants-empty">لا توجد نتائج</p>
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
            return `<li class="py-3 flex justify-between items-center group cursor-pointer hover:bg-surface-container-low transition-colors px-2 rounded-md -mx-2">
                <span class="font-body-md text-body-md text-on-background font-medium">${escapeHtml(p.name)}</span>
                <span class="font-label-sm text-label-sm bg-surface-variant text-on-surface-variant px-2 py-1 rounded">${escapeHtml(p.membership_code)}</span>
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

        const res = await Eftkad.api('/settings/filters');

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
