@extends('layouts.app')

@php($activeNav = 'users')

@section('title', __('ui.user_detail.title') . ' - Eftkad')
@section('page-title', __('ui.user_detail.title'))
@section('page-title-mobile', __('ui.user_detail.title'))

@section('content')
<div class="md:max-w-2xl">
<div class="hidden md:flex justify-between items-center mb-2 -mt-4 border-b border-outline-variant pb-4">
    <button type="button" onclick="history.back()" class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined">{{ app()->getLocale() === 'ar' ? 'arrow_forward' : 'arrow_back' }}</span>
        <span>{{ __('ui.common.back') }}</span>
    </button>
</div>
<div class="md:hidden flex items-center gap-2 -mt-4 mb-2">
    <button type="button" onclick="history.back()" class="p-2 hover:bg-surface-container rounded-full transition-colors text-on-surface-variant">
        <span class="material-symbols-outlined">{{ app()->getLocale() === 'ar' ? 'arrow_forward' : 'arrow_back' }}</span>
    </button>
</div>

<div id="detail-loading" class="text-center py-12 text-on-surface-variant">{{ __('ui.user_detail.loading') }}</div>
<div id="detail-error" class="hidden text-center py-12 text-error">{{ __('ui.user_detail.error') }}</div>

<div id="detail-content" class="hidden space-y-6">
    <section class="bg-surface-container-lowest rounded-xl p-4 md:p-6 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-surface-variant relative overflow-hidden">
        <div class="absolute top-0 rtl:right-0 ltr:left-0 w-2 h-full bg-primary-container"></div>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-surface-variant pb-4 mb-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-surface-container-high flex items-center justify-center text-primary flex-shrink-0">
                    <span class="material-symbols-outlined text-[28px]">person</span>
                </div>
                <div>
                    <h2 class="font-title-md text-lg md:text-xl text-on-surface mb-1" id="d-name">—</h2>
                    <p class="font-label-sm text-label-sm text-on-surface-variant" id="d-membership_code">—</p>
                </div>
            </div>
            <div id="d-type-badge"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-body-md text-body-md text-on-surface">
            <p class="flex items-center gap-2">
                <span class="material-symbols-outlined text-outline">mail</span>
                <span id="d-email" class="text-on-surface-variant">—</span>
            </p>
            <p class="flex items-center gap-2">
                <span class="material-symbols-outlined text-outline">call</span>
                <span id="d-phone" class="text-on-surface-variant">—</span>
            </p>
        </div>
    </section>

    <div class="flex justify-end">
        <a id="edit-link" href="#" class="inline-flex items-center gap-2 bg-primary text-on-primary rounded-lg px-6 py-3 font-title-md text-title-md shadow-md hover:shadow-lg hover:bg-surface-tint transition-all">
            <span class="material-symbols-outlined">edit</span>
            {{ __('ui.user_detail.edit_button') }}
        </a>
    </div>
</div>
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

    function renderDetail(u) {
        document.getElementById('d-name').textContent = u.name || UI_TEXT.common.dash;
        document.getElementById('d-membership_code').textContent = u.membership_code || UI_TEXT.common.dash;
        document.getElementById('d-email').textContent = u.email || UI_TEXT.common.dash;
        document.getElementById('d-phone').textContent = u.phone || UI_TEXT.common.dash;

        const badge = u.type
            ? `<span class="px-3 py-1.5 rounded-md bg-primary-container text-on-primary-container font-label-sm text-label-sm font-bold">${escapeHtml(u.type.name)}</span>`
            : '<span class="px-3 py-1.5 rounded-md bg-error-container text-on-error-container font-label-sm text-label-sm font-bold">' + UI_TEXT.user_detail.pending_type + '</span>';
        document.getElementById('d-type-badge').innerHTML = badge;

        document.getElementById('edit-link').href = '/users/' + USER_ID + '/edit';
    }

    async function loadDetail() {
        const loading = document.getElementById('detail-loading');
        const errorBox = document.getElementById('detail-error');
        const content = document.getElementById('detail-content');

        const res = await Eftkad.api('/users/' + USER_ID);

        loading.classList.add('hidden');

        if (!res.ok || !res.data || !res.data.data) {
            errorBox.classList.remove('hidden');
            return;
        }

        renderDetail(res.data.data);
        content.classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;
        loadDetail();
    });
</script>
@endsection
