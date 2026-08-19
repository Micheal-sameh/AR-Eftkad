@extends('layouts.app')

@php($activeNav = 'visits')

@section('title', 'Visit Details - Eftkad')
@section('page-title', 'تفاصيل الزيارة')
@section('page-title-mobile', 'تفاصيل الزيارة')

@section('content')
<div class="hidden md:flex justify-between items-center mb-2 -mt-4 border-b border-outline-variant pb-4">
    <button type="button" onclick="history.back()" class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined">arrow_forward</span>
        <span>العودة</span>
    </button>
</div>
<div class="md:hidden flex items-center gap-2 -mt-4 mb-2">
    <button type="button" onclick="history.back()" class="p-2 hover:bg-surface-container rounded-full transition-colors text-on-surface-variant">
        <span class="material-symbols-outlined">arrow_forward</span>
    </button>
</div>

<div id="detail-loading" class="text-center py-12 text-on-surface-variant">Loading...</div>
<div id="detail-error" class="hidden text-center py-12 text-error">Failed to load visit details.</div>

<div id="detail-content" class="hidden space-y-6">
    <!-- Identity & Status Card -->
    <section class="bg-surface-container-lowest rounded-xl p-card-padding shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-surface-variant relative overflow-hidden">
        <div class="absolute top-0 right-0 w-2 h-full bg-primary-container"></div>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-surface-variant pb-4 mb-4">
            <div>
                <h2 class="font-title-md text-title-md text-on-surface mb-1" id="d-membership_code">—</h2>
                <div class="flex items-center gap-2 text-on-surface-variant font-label-sm text-label-sm">
                    <span class="material-symbols-outlined" style="font-size: 16px;">calendar_today</span>
                    <span id="d-date">—</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2" id="d-badges"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-label-sm text-label-sm text-outline mb-2">الاحتياجات الرعوية</h3>
                <div class="flex flex-wrap gap-2" id="d-needs">
                    <span class="text-on-surface-variant italic font-body-md text-body-md">لا توجد احتياجات مسجلة</span>
                </div>
            </div>
            <div>
                <h3 class="font-label-sm text-label-sm text-outline mb-2">وسائل التواصل</h3>
                <div class="flex flex-wrap gap-2" id="d-comm">
                    <span class="text-on-surface-variant italic font-body-md text-body-md">لا توجد بيانات</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Address -->
    <section class="bg-surface-container-lowest rounded-xl p-card-padding shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-surface-variant">
        <h3 class="font-title-md text-title-md text-on-surface mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">location_on</span>
            الموقع والعنوان
        </h3>
        <div class="space-y-4">
            <div class="flex items-start gap-4">
                <span class="material-symbols-outlined text-outline mt-1">map</span>
                <div>
                    <p class="font-body-md text-body-md text-on-surface" id="d-address">—</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant" id="d-location">—</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Responsibility -->
    <section class="bg-surface-container-lowest rounded-xl p-card-padding shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-surface-variant">
        <h3 class="font-title-md text-title-md text-on-surface mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">groups</span>
            المسئولية الرعوية
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined">person</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-outline">الأب الكاهن</p>
                    <span class="font-body-md text-body-md text-on-surface" id="d-father">—</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined">person</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-outline">الخادم المسئول</p>
                    <span class="font-body-md text-body-md text-on-surface" id="d-servant">—</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Attendance & confession -->
    <section class="bg-surface-container-lowest rounded-xl p-card-padding shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-surface-variant">
        <h3 class="font-title-md text-title-md text-on-surface mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">church</span>
            الحضور والاعتراف
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-body-md text-body-md text-on-surface">
            <p>حضور القداسات: <span class="text-on-surface-variant" id="d-mass">—</span></p>
            <p>حضور الاجتماعات: <span class="text-on-surface-variant" id="d-attend_meetings">—</span></p>
            <p>اعتراف الأب: <span class="text-on-surface-variant" id="d-father_confession">—</span></p>
            <p>اعتراف الأم: <span class="text-on-surface-variant" id="d-mother_confession">—</span></p>
            <p>اعتراف الأبناء: <span class="text-on-surface-variant" id="d-children_confession">—</span></p>
            <p>يحتاج افتقاد من الاجتماع: <span class="text-on-surface-variant" id="d-need_eftkad_from_meeting">—</span></p>
            <p>يحتاج افتقاد من الأب الكاهن: <span class="text-on-surface-variant" id="d-need_eftkad_by_father">—</span></p>
        </div>
    </section>

    <!-- Visit Notes -->
    <section class="bg-surface-container-lowest rounded-xl p-card-padding shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-surface-variant">
        <h3 class="font-title-md text-title-md text-on-surface mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">sticky_note_2</span>
            ملاحظات الزيارة
        </h3>
        <div class="bg-surface-container-low rounded-lg p-4 text-on-surface-variant font-body-md text-body-md leading-relaxed border border-outline-variant border-opacity-50" id="d-notes">
            لا توجد ملاحظات
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
    const VISIT_ID = {{ (int) $id }};
    const memberNames = {};

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function yesNo(boolResource) {
        if (!boolResource) return '—';
        return boolResource.value === 1 ? 'نعم' : 'لا';
    }

    function textYesNo(value) {
        return value === '1' || value === 1 ? 'نعم' : 'لا';
    }

    async function loadDirectory() {
        const res = await Eftkad.api('/settings/filters');
        if (!res.ok || !res.data) return;
        const fathers = (res.data.data && res.data.data.fathers) || [];
        const servants = (res.data.data && res.data.data.servants) || [];
        fathers.concat(servants).forEach(function (p) {
            memberNames[p.membership_code] = p.name;
        });
    }

    function renderDetail(e) {
        document.getElementById('d-membership_code').textContent = e.membership_code || '—';
        document.getElementById('d-date').textContent = e.date || '—';

        const badges = [];
        if (e.type) {
            badges.push(`<span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-secondary-container text-on-secondary-container font-label-sm text-label-sm">
                <span class="material-symbols-outlined" style="font-size: 14px;">${e.type.value === 2 ? 'home' : 'call'}</span> ${escapeHtml(e.type.name)}
            </span>`);
        }
        document.getElementById('d-badges').innerHTML = badges.join('');

        const needs = e.needs || [];
        document.getElementById('d-needs').innerHTML = needs.length
            ? needs.map(function (n) {
                return `<span class="px-3 py-1.5 border border-outline-variant rounded-lg text-on-surface-variant font-body-md text-body-md">${escapeHtml(n.name)}</span>`;
            }).join('')
            : '<span class="text-on-surface-variant italic font-body-md text-body-md">لا توجد احتياجات مسجلة</span>';

        const comm = e.communication_means || [];
        document.getElementById('d-comm').innerHTML = comm.length
            ? comm.map(function (c) {
                return `<span class="px-3 py-1.5 border border-outline-variant rounded-lg text-on-surface-variant font-body-md text-body-md">${escapeHtml(c.name)}</span>`;
            }).join('')
            : '<span class="text-on-surface-variant italic font-body-md text-body-md">لا توجد بيانات</span>';

        document.getElementById('d-address').textContent = e.correspondence_address || '—';
        document.getElementById('d-location').textContent = e.location || '';

        const fatherCode = e.father_membership_code;
        const servantCode = e.servant_membership_code;
        document.getElementById('d-father').textContent = fatherCode
            ? (memberNames[fatherCode] ? memberNames[fatherCode] + ' (' + fatherCode + ')' : fatherCode)
            : '—';
        document.getElementById('d-servant').textContent = servantCode
            ? (memberNames[servantCode] ? memberNames[servantCode] + ' (' + servantCode + ')' : servantCode)
            : '—';

        document.getElementById('d-mass').textContent = e.mass_attendence ? e.mass_attendence.name : '—';
        document.getElementById('d-attend_meetings').textContent = yesNo(e.attend_meetings);
        document.getElementById('d-father_confession').textContent = textYesNo(e.father_confession);
        document.getElementById('d-mother_confession').textContent = textYesNo(e.mother_confession);
        document.getElementById('d-children_confession').textContent = textYesNo(e.children_confession);
        document.getElementById('d-need_eftkad_from_meeting').textContent = textYesNo(e.need_eftkad_from_meeting);
        document.getElementById('d-need_eftkad_by_father').textContent = yesNo(e.need_eftkad_by_father);

        document.getElementById('d-notes').textContent = e.general_notes || 'لا توجد ملاحظات';
    }

    async function loadDetail() {
        const loading = document.getElementById('detail-loading');
        const errorBox = document.getElementById('detail-error');
        const content = document.getElementById('detail-content');

        await loadDirectory();

        const res = await Eftkad.api('/eftkads/detail/' + VISIT_ID);

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
