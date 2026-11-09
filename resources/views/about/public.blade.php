@extends('layouts.guest')

@php($uiSections = ['common'])

@section('title', __('ui.fg.about.title') . ' - Eftkad')

@section('content')
@include('about.rich-css')
<div class="signin-heading">
    <div>{{ __('ui.fg.about.title') }}</div>
</div>

<div id="a-error" class="inline-error hidden" style="margin-bottom: 1rem;">
    <x-icon name="alert" :size="16" />
    <span>{{ __('ui.fg.about.error') }}</span>
</div>
<div class="page-title" id="a-title" style="font-size:1.1rem"></div>
<div id="a-body" class="rich" style="margin-top:.6rem"></div>
<p id="a-empty" class="hidden" style="color:var(--ink-faint)">{{ __('ui.fg.about.empty') }}</p>

<a class="text-link" id="a-back" href="/login" style="display:block;margin-top:1.2rem">{{ __('ui.fg.about.back_to_login') }}</a>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async function () {
        const $ = function (id) { return document.getElementById(id); };
        // Opened from the app's lock screen (?app=1): the popup has its own close button.
        if (new URLSearchParams(location.search).get('app')) $('a-back').classList.add('hidden');
        const res = await Eftkad.api('/about');
        if (!res.ok || !res.data) { $('a-error').classList.remove('hidden'); return; }
        const c = res.data.data || {};
        const lang = Eftkad.locale() === 'en' ? 'en' : 'ar';
        const other = lang === 'en' ? 'ar' : 'en';
        const title = c['title_' + lang] || c['title_' + other] || '';
        const body = c['body_' + lang] || c['body_' + other] || '';
        $('a-title').textContent = title;
        $('a-body').innerHTML = body; // sanitized by the server on save
        $('a-title').classList.toggle('hidden', !title);
        $('a-empty').classList.toggle('hidden', !!(title || body));
    });
</script>
@endsection
