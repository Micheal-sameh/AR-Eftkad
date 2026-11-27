@extends('layouts.app')

@php($uiSections = [])

@php($activeNav = 'church-week')

@section('title', __('ui.fg.church_week.title') . ' - Eftkad')

@section('content')
<style>
    /* The page fits the screen exactly so only the embedded site scrolls (no page scroll around it). */
    .church-week-page { box-sizing: border-box; display: flex; flex-direction: column; height: calc(100vh - 5rem); height: calc(100dvh - 5rem); }
    .church-week-page .page-heading { flex: none; align-items: center; }
    .church-week-page .page-heading > div:first-child { min-width: 0; }
    .cw-actions { display: flex; gap: .5rem; flex: none; }
    .cw-frame-wrap { flex: 1; min-height: 0; display: flex; flex-direction: column; overflow: hidden; border: 1px solid var(--border); border-radius: .8rem; background: var(--surface-raised); }
    .cw-toolbar { flex: none; display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: .3rem .4rem; border-bottom: 1px solid var(--border); background: var(--surface-soft); }
    .cw-zoom { display: flex; align-items: center; gap: .15rem; }
    .cw-toolbar .icon-button { width: 2.2rem; height: 2.2rem; color: var(--ink); }
    .cw-toolbar .icon-button:hover:not(:disabled) { background: var(--surface-raised); }
    .cw-toolbar .icon-button:disabled { opacity: .35; }
    .cw-zoom-level { min-width: 3.4rem; height: 2.2rem; border-radius: .6rem; font-size: .8rem; font-weight: 650; color: var(--ink-soft); font-variant-numeric: tabular-nums; }
    .cw-zoom-level:hover { background: var(--surface-raised); }
    /* Zoom = render the frame larger/smaller than the box, then scale it back to fit (the site is cross-origin, so its own text can't be resized). */
    .cw-viewport { position: relative; flex: 1; min-height: 0; overflow: hidden; background: #fff; }
    .church-week-frame { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; transform-origin: 0 0; }
    /* Full screen: cover the whole app, bars included. */
    .cw-frame-wrap.is-expanded { position: fixed; inset: 0; z-index: 100; border: 0; border-radius: 0; padding: env(safe-area-inset-top, 0px) env(safe-area-inset-right, 0px) env(safe-area-inset-bottom, 0px) env(safe-area-inset-left, 0px); }
    body.cw-no-scroll { overflow: hidden; }
    @media (min-width: 821px) { .church-week-page { padding-bottom: 1.5rem; } }
    @media (max-width: 820px) {
        .church-week-page { height: calc(100vh - 4.3rem - env(safe-area-inset-top, 0px)); height: calc(100dvh - 4.3rem - env(safe-area-inset-top, 0px)); padding-top: 1rem; }
        .church-week-page .page-heading { gap: .75rem; }
        .church-week-page .page-heading p { display: none; }
        .church-week-page .page-title { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        /* Icon-only buttons on phones so the heading fits on one line. */
        .cw-actions .button { width: 2.7rem; padding: 0; }
        .cw-actions .button span { display: none; }
        .cw-frame-wrap { border-radius: .6rem; }
    }
</style>
<div class="page-content church-week-page">
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ __('ui.fg.church_week.eyebrow') }}</div>
            <div class="page-title">{{ __('ui.fg.church_week.title') }}</div>
            <p>{{ __('ui.fg.church_week.subtitle') }}</p>
        </div>
        <div class="cw-actions">
            <a class="button button-secondary" href="https://www.avarewase.org/weekly/" target="_blank" rel="noopener" aria-label="{{ __('ui.fg.church_week.open') }}">
                <x-icon name="external" :size="16" /><span>{{ __('ui.fg.church_week.open') }}</span>
            </a>
            <button type="button" class="button button-primary" id="cw-share" aria-label="{{ __('ui.fg.church_week.share') }}">
                <x-icon name="share" :size="16" /><span>{{ __('ui.fg.church_week.share') }}</span>
            </button>
        </div>
    </div>

    <div class="cw-frame-wrap" id="cw-wrap">
        <div class="cw-toolbar">
            <div class="cw-zoom" dir="ltr">
                <button type="button" class="icon-button" id="cw-zoom-out" aria-label="{{ __('ui.fg.church_week.zoom_out') }}" title="{{ __('ui.fg.church_week.zoom_out') }}"><x-icon name="minus" :size="18" /></button>
                <button type="button" class="cw-zoom-level" id="cw-zoom-level" aria-label="{{ __('ui.fg.church_week.zoom_reset') }}" title="{{ __('ui.fg.church_week.zoom_reset') }}">100%</button>
                <button type="button" class="icon-button" id="cw-zoom-in" aria-label="{{ __('ui.fg.church_week.zoom_in') }}" title="{{ __('ui.fg.church_week.zoom_in') }}"><x-icon name="plus" :size="18" /></button>
            </div>
            <button type="button" class="icon-button" id="cw-expand" aria-label="{{ __('ui.fg.church_week.fullscreen') }}" title="{{ __('ui.fg.church_week.fullscreen') }}"><x-icon name="expand" :size="18" /></button>
        </div>
        <div class="cw-viewport">
            <iframe class="church-week-frame" id="cw-frame" src="https://www.avarewase.org/weekly/" title="{{ __('ui.fg.church_week.title') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Share the church's public weekly page (works for people without an Eftkad account):
    // the phone's share menu when there is one, otherwise copy the link.
    document.getElementById('cw-share').addEventListener('click', async function () {
        const url = 'https://www.avarewase.org/weekly/';
        const text = {!! json_encode(__('ui.fg.church_week.share_text'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!};
        try {
            if (navigator.share) { await navigator.share({ title: text, text: text, url: url }); return; }
            await navigator.clipboard.writeText(url);
            Eftkad.toast({!! json_encode(__('ui.fg.church_week.copied'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!});
        } catch (e) {}
    });

    // Zoom the embedded site in/out (remembered on this device) and a full-screen toggle.
    (function () {
        const $ = function (id) { return document.getElementById(id); };
        const MIN = 50, MAX = 200, STEP = 10, KEY = 'eftkad_church_week_zoom';
        const ICONS = { expand: Eftkad.icon('expand', 18), shrink: Eftkad.icon('shrink', 18) };
        const LABELS = {!! json_encode(['fullscreen' => __('ui.fg.church_week.fullscreen'), 'exit' => __('ui.fg.church_week.exit_fullscreen')], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!};
        let zoom = 100;
        try { zoom = Math.min(MAX, Math.max(MIN, parseInt(localStorage.getItem(KEY), 10) || 100)); } catch (e) {}

        function applyZoom() {
            const z = zoom / 100;
            const f = $('cw-frame');
            f.style.width = (100 / z) + '%';
            f.style.height = (100 / z) + '%';
            f.style.transform = z === 1 ? '' : 'scale(' + z + ')';
            $('cw-zoom-level').textContent = zoom + '%';
            $('cw-zoom-out').disabled = zoom <= MIN;
            $('cw-zoom-in').disabled = zoom >= MAX;
            try { localStorage.setItem(KEY, String(zoom)); } catch (e) {}
        }
        function setZoom(z) { zoom = Math.min(MAX, Math.max(MIN, z)); applyZoom(); }

        function setExpanded(on) {
            $('cw-wrap').classList.toggle('is-expanded', on);
            document.body.classList.toggle('cw-no-scroll', on);
            const b = $('cw-expand');
            b.innerHTML = on ? ICONS.shrink : ICONS.expand;
            b.setAttribute('aria-label', on ? LABELS.exit : LABELS.fullscreen);
            b.title = on ? LABELS.exit : LABELS.fullscreen;
        }

        $('cw-zoom-out').addEventListener('click', function () { setZoom(zoom - STEP); });
        $('cw-zoom-in').addEventListener('click', function () { setZoom(zoom + STEP); });
        $('cw-zoom-level').addEventListener('click', function () { setZoom(100); });
        $('cw-expand').addEventListener('click', function () { setExpanded(!$('cw-wrap').classList.contains('is-expanded')); });
        document.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape' && $('cw-wrap').classList.contains('is-expanded')) setExpanded(false);
        });
        applyZoom();
    })();
</script>
@endsection
