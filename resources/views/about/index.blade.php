@extends('layouts.app')

@php($uiSections = ['common'])

@php($activeNav = 'about')

@section('title', __('ui.fg.about.title') . ' - Eftkad')

@section('content')
<style>
    .rich { line-height: 1.8; overflow-wrap: anywhere; }
    .rich img { max-width: 100%; height: auto; border-radius: .6rem; }
    .rich h1, .rich h2, .rich h3, .rich h4 { margin: 1rem 0 .4rem; line-height: 1.4; }
    .rich ul, .rich ol { padding-inline-start: 1.4rem; }
    .rich ul { list-style: disc; } .rich ol { list-style: decimal; }
    .rich blockquote { margin: .8rem 0; padding: .2rem .9rem; border-inline-start: 3px solid var(--border); color: var(--ink-soft); }
    .rich a { color: var(--primary); text-decoration: underline; }
    .rich p, .rich div { min-height: 1em; }
    .rte-bar { display: flex; flex-wrap: wrap; gap: .25rem; padding: .4rem; border: 1px solid var(--border); border-bottom: 0; border-radius: .8rem .8rem 0 0; background: var(--surface-soft); }
    .rte-bar button { min-width: 2.1rem; height: 2.1rem; padding: 0 .5rem; border-radius: .5rem; color: var(--ink); font-size: .8rem; }
    .rte-bar button:hover { background: var(--surface-raised); }
    .rte-area { min-height: 14rem; max-height: 32rem; overflow: auto; padding: .8rem; border: 1px solid var(--border); border-radius: 0 0 .8rem .8rem; background: var(--surface-raised); outline: none; }
    .rte-area:focus { border-color: color-mix(in srgb, var(--primary) 55%, var(--border)); }
</style>
<div class="page-content form-page">
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ __('ui.fg.about.eyebrow') }}</div>
            <div class="page-title">{{ __('ui.fg.about.title') }}</div>
            <p>{{ __('ui.fg.about.subtitle') }}</p>
        </div>
        <button type="button" class="button button-secondary hidden" id="a-edit-btn">
            <x-icon name="edit" :size="16" /><span>{{ __('ui.fg.about.edit') }}</span>
        </button>
    </div>

    <div id="a-error" class="state-note is-error hidden">{{ __('ui.fg.about.error') }}</div>

    <div class="form-layout">
        {{-- Read view --}}
        <section class="form-section" id="a-view">
            <div class="page-title" id="a-title" style="font-size:1.2rem"></div>
            <div id="a-body" class="rich" style="margin-top:.6rem"></div>
            <p id="a-empty" class="hidden" style="color:var(--ink-faint)">{{ __('ui.fg.about.empty') }}</p>
        </section>

        {{-- Owner edit form --}}
        <section class="form-section hidden" id="a-form">
            @foreach (['ar', 'en'] as $lang)
                <label class="field" style="margin-bottom:.9rem">
                    <span class="field-label">{{ __('ui.fg.about.title_' . $lang) }}</span>
                    <input type="text" id="f-title_{{ $lang }}" class="input" maxlength="150" dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}" style="width:100%">
                </label>

                <div class="field-label">{{ __('ui.fg.about.body_' . $lang) }}</div>
                <div class="rte" style="margin-bottom:1.1rem" data-lang="{{ $lang }}">
                    <div class="rte-bar" role="toolbar">
                        <button type="button" data-cmd="bold" title="{{ __('ui.fg.about.bold') }}"><b>B</b></button>
                        <button type="button" data-cmd="italic" title="{{ __('ui.fg.about.italic') }}"><i>I</i></button>
                        <button type="button" data-cmd="underline" title="{{ __('ui.fg.about.underline') }}"><u>U</u></button>
                        <button type="button" data-cmd="formatBlock" data-val="h2" title="{{ __('ui.fg.about.heading') }}">H1</button>
                        <button type="button" data-cmd="formatBlock" data-val="h3" title="{{ __('ui.fg.about.subheading') }}">H2</button>
                        <button type="button" data-cmd="formatBlock" data-val="p" title="{{ __('ui.fg.about.paragraph') }}">¶</button>
                        <button type="button" data-cmd="justifyRight" title="{{ __('ui.fg.about.align_right') }}">⇥</button>
                        <button type="button" data-cmd="justifyCenter" title="{{ __('ui.fg.about.align_center') }}">≡</button>
                        <button type="button" data-cmd="justifyLeft" title="{{ __('ui.fg.about.align_left') }}">⇤</button>
                        <button type="button" data-cmd="insertUnorderedList" title="{{ __('ui.fg.about.bullets') }}">•</button>
                        <button type="button" data-cmd="insertOrderedList" title="{{ __('ui.fg.about.numbers') }}">1.</button>
                        <button type="button" data-cmd="link" title="{{ __('ui.fg.about.link') }}">🔗</button>
                        <button type="button" data-cmd="image" title="{{ __('ui.fg.about.image') }}">🖼</button>
                        <button type="button" data-cmd="removeFormat" title="{{ __('ui.fg.about.clear') }}">⌫</button>
                    </div>
                    <div class="rte-area rich" id="f-body_{{ $lang }}" contenteditable="true" dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}"></div>
                </div>
            @endforeach
            <input type="file" id="a-file" accept="image/png,image/jpeg,image/gif,image/webp" class="hidden">
            <div style="display:flex;gap:.6rem">
                <button type="button" class="button button-primary" id="a-save"><x-icon name="check" :size="18" /><span>{{ __('ui.fg.about.save') }}</span></button>
                <button type="button" class="button button-ghost" id="a-cancel"><span>{{ __('ui.fg.about.cancel') }}</span></button>
            </div>
        </section>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const $ = function (id) { return document.getElementById(id); };
    const A = UI_TEXT.fg.about;
    const TITLES = ['title_ar', 'title_en'];
    const BODIES = ['body_ar', 'body_en'];
    let content = {};
    let activeEditor = null;
    let savedRange = null;

    function isOwner() {
        const u = Eftkad.user();
        return !!(u && u.role && u.role.value === 'owner');
    }

    function render() {
        const lang = Eftkad.locale() === 'en' ? 'en' : 'ar';
        // Fall back to the other language when this one has not been written yet.
        const other = lang === 'en' ? 'ar' : 'en';
        const title = content['title_' + lang] || content['title_' + other] || '';
        const body = content['body_' + lang] || content['body_' + other] || '';
        $('a-title').textContent = title;
        $('a-body').innerHTML = body; // sanitized by the server on save
        $('a-title').classList.toggle('hidden', !title);
        $('a-empty').classList.toggle('hidden', !!(title || body));
    }

    function showForm(on) {
        $('a-form').classList.toggle('hidden', !on);
        $('a-view').classList.toggle('hidden', on);
        $('a-edit-btn').classList.toggle('hidden', on || !isOwner());
        if (on) {
            TITLES.forEach(function (f) { $('f-' + f).value = content[f] || ''; });
            BODIES.forEach(function (f) { $('f-' + f).innerHTML = content[f] || ''; });
        }
    }

    async function load() {
        const res = await Eftkad.api('/about');
        if (!res.ok || !res.data) { $('a-error').classList.remove('hidden'); return; }
        content = res.data.data || {};
        render();
    }

    // The toolbar buttons steal focus, so remember where the caret was inside the editor.
    function rememberSelection() {
        const sel = window.getSelection();
        if (sel.rangeCount && activeEditor && activeEditor.contains(sel.anchorNode)) savedRange = sel.getRangeAt(0).cloneRange();
    }
    function restoreSelection() {
        if (!activeEditor) return;
        activeEditor.focus();
        if (savedRange) { const sel = window.getSelection(); sel.removeAllRanges(); sel.addRange(savedRange); }
    }

    async function uploadImage(file) {
        const fd = new FormData();
        fd.append('image', file);
        const res = await Eftkad.api('/about/image', { method: 'POST', body: fd });
        if (!res.ok || !res.data || !res.data.url) { if (Eftkad.toast) Eftkad.toast(A.upload_failed); return; }
        restoreSelection();
        document.execCommand('insertImage', false, res.data.url);
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!Eftkad.requireAuth()) return;
        load();
        $('a-edit-btn').classList.toggle('hidden', !isOwner());
        Eftkad.syncUser().then(function () { if ($('a-form').classList.contains('hidden')) $('a-edit-btn').classList.toggle('hidden', !isOwner()); });

        document.execCommand('defaultParagraphSeparator', false, 'p');

        document.querySelectorAll('.rte-area').forEach(function (ed) {
            ed.addEventListener('focus', function () { activeEditor = ed; });
            ['keyup', 'mouseup', 'input'].forEach(function (ev) { ed.addEventListener(ev, function () { activeEditor = ed; rememberSelection(); }); });
            // Paste as plain text so Word/web styles do not leak in.
            ed.addEventListener('paste', function (e) {
                e.preventDefault();
                document.execCommand('insertText', false, (e.clipboardData || window.clipboardData).getData('text/plain'));
            });
        });

        document.querySelectorAll('.rte-bar').forEach(function (bar) {
            // mousedown + preventDefault keeps the editor selection alive when a button is pressed.
            bar.addEventListener('mousedown', function (e) { if (e.target.closest('button')) e.preventDefault(); });
            bar.addEventListener('click', function (e) {
                const b = e.target.closest('button');
                if (!b) return;
                activeEditor = bar.parentNode.querySelector('.rte-area');
                restoreSelection();
                const cmd = b.dataset.cmd;
                if (cmd === 'link') {
                    const url = window.prompt(A.link_prompt, 'https://');
                    if (url) document.execCommand('createLink', false, url);
                } else if (cmd === 'image') {
                    $('a-file').click();
                } else if (cmd === 'formatBlock') {
                    document.execCommand('formatBlock', false, b.dataset.val);
                } else {
                    document.execCommand(cmd, false, null);
                }
                rememberSelection();
            });
        });

        $('a-file').addEventListener('change', function () {
            const f = this.files && this.files[0];
            this.value = '';
            if (f) uploadImage(f);
        });

        $('a-edit-btn').addEventListener('click', function () { showForm(true); });
        $('a-cancel').addEventListener('click', function () { showForm(false); });
        $('a-save').addEventListener('click', async function () {
            const body = {};
            TITLES.forEach(function (f) { body[f] = $('f-' + f).value; });
            BODIES.forEach(function (f) { body[f] = $('f-' + f).innerHTML; });
            $('a-save').disabled = true;
            const res = await Eftkad.api('/about', { method: 'PUT', body: body });
            $('a-save').disabled = false;
            if (!res.ok) { if (Eftkad.toast) Eftkad.toast(A.save_failed); return; }
            content = res.data.data || body;
            render();
            showForm(false);
            if (Eftkad.toast) Eftkad.toast(A.saved);
        });
    });
</script>
@endsection
