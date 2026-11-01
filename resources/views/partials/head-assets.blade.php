<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
<link rel="manifest" href="/manifest.webmanifest" />
<meta name="theme-color" content="#1976d2" />
<meta name="mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
<meta name="apple-mobile-web-app-title" content="إفتقاد" />
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png?v=2" />
<link rel="icon" href="/icons/icon-192.png?v=2" />
{{-- Only ship the UI_TEXT sections a page's JS reads; pages declare them with @php($uiSections = [...]). --}}
<script>window.UI_TEXT = @json(isset($uiSections) ? \Illuminate\Support\Arr::only(__('ui'), $uiSections) : __('ui'));</script>
@vite(['resources/css/app.css'])
