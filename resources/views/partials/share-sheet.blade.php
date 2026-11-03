{{-- Share-a-visit popup. Usage: ShareSheet.open(visitId). Needs the layout's Eftkad helpers. --}}
<div class="sheet-wrap" id="share-sheet">
    <div class="sheet-backdrop" id="share-backdrop"></div>
    <div class="sheet">
        <div class="sheet-handle"></div>
        <div class="section-header">
            <div class="section-title">{{ __('ui.visit_detail.share_title') }}</div>
            <button type="button" class="icon-button" id="share-close" aria-label="Close"><x-icon name="close" /></button>
        </div>
        <p class="section-copy" style="margin-top:0">{{ __('ui.visit_detail.share_hint') }}</p>
        <div class="search-bar" style="margin-bottom:.8rem">
            <x-icon name="search" :size="19" />
            <input id="share-search" aria-label="{{ __('ui.visit_detail.share_search') }}" placeholder="{{ __('ui.visit_detail.share_search') }}" autocomplete="off" />
        </div>
        <div id="share-loading" class="is-loading-block"><span class="spinner"></span></div>
        <div id="share-people" style="display:grid;gap:.4rem;max-height:46vh;overflow:auto"></div>
        <div class="feed-error hidden" id="share-error" role="alert" style="margin-top:.8rem"><div><strong>{{ __('ui.visit_detail.share_error') }}</strong></div></div>
        <div class="sheet-actions">
            <button type="button" class="button button-ghost" id="share-link"><x-icon name="share" :size="18" /><span>{{ __('ui.visit_detail.share_link') }}</span></button>
            <button type="button" class="button button-primary" id="share-save"><span>{{ __('ui.visit_detail.share_save') }}</span></button>
        </div>
    </div>
</div>
<script>
    window.ShareSheet = (function () {
        const T = {!! json_encode([
            'done' => __('ui.visit_detail.share_done'),
            'error' => __('ui.visit_detail.share_error'),
            'copied' => __('ui.visit_detail.share_copied'),
            'text' => __('ui.visit_detail.share_text'),
            'assigned' => __('ui.visit_detail.share_assigned'),
            'father' => __('ui.fg.visits.father'),
            'servant' => __('ui.fg.visits.servant'),
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!};
        const $ = function (id) { return document.getElementById(id); };
        let visitId = null;
        let people = [];            // [{name, membership_code, role}]
        let assigned = [];          // father/servant already on the visit: nothing to share
        let original = [];          // shared when the popup opened
        let selected = new Set();   // shared after Save

        function render() {
            const q = $('share-search').value.trim().toLowerCase();
            $('share-people').innerHTML = people
                .filter(function (p) { return !q || (p.name + ' ' + p.membership_code).toLowerCase().includes(q); })
                .map(function (p) {
                    const code = p.membership_code;
                    const locked = assigned.indexOf(code) !== -1;
                    const on = locked || selected.has(code);
                    return '<button type="button" class="chip' + (on ? ' selected' : '') + '" data-code="' + Eftkad.esc(code) + '"' + (locked ? ' disabled' : '') +
                        ' style="justify-content:space-between;width:100%;min-height:2.6rem;padding:.4rem .9rem">' +
                        '<span>' + Eftkad.esc(p.name) + ' <small>(' + Eftkad.esc(code) + ' · ' + Eftkad.esc(p.role) + ')</small></span>' +
                        '<span>' + (locked ? Eftkad.esc(T.assigned) : (on ? Eftkad.icon('check', 16) : '')) + '</span></button>';
                }).join('');
        }

        async function open(id) {
            visitId = id;
            $('share-search').value = '';
            $('share-error').classList.add('hidden');
            $('share-people').innerHTML = '';
            $('share-loading').classList.remove('hidden');
            $('share-sheet').classList.add('open');

            const [dir, info] = await Promise.all([Eftkad.cachedApi('/settings/filters'), Eftkad.api('/eftkads/detail/' + id + '/share')]);
            $('share-loading').classList.add('hidden');
            if (!dir.ok || !info.ok || !info.data || !info.data.data) { $('share-error').classList.remove('hidden'); return; }

            const d = dir.data.data || {};
            people = (d.fathers || []).map(function (p) { return Object.assign({ role: T.father }, p); })
                .concat((d.servants || []).map(function (p) { return Object.assign({ role: T.servant }, p); }));
            assigned = info.data.data.assigned || [];
            original = info.data.data.shared_with || [];
            selected = new Set(original);
            render();
        }

        function close() { $('share-sheet').classList.remove('open'); }

        async function save() {
            const add = Array.from(selected).filter(function (c) { return original.indexOf(c) === -1; });
            const remove = original.filter(function (c) { return !selected.has(c); });
            $('share-save').disabled = true;
            let ok = true;
            if (add.length) ok = (await Eftkad.api('/eftkads/detail/' + visitId + '/share', { method: 'POST', body: { membership_codes: add } })).ok;
            for (const c of remove) {
                if (!ok) break;
                ok = (await Eftkad.api('/eftkads/detail/' + visitId + '/share/' + encodeURIComponent(c), { method: 'DELETE' })).ok;
            }
            $('share-save').disabled = false;
            if (!ok) { $('share-error').classList.remove('hidden'); return; }
            close();
            Eftkad.toast(T.done);
        }

        // Pass the visit on as a link: the phone's share menu when there is one, otherwise copy it.
        async function shareLink() {
            const url = location.origin + '/visits/' + visitId;
            try {
                if (navigator.share) { await navigator.share({ title: T.text, text: T.text, url: url }); return; }
                await navigator.clipboard.writeText(url);
                Eftkad.toast(T.copied);
            } catch (e) {}
        }

        $('share-search').addEventListener('input', render);
        $('share-people').addEventListener('click', function (ev) {
            const b = ev.target.closest('[data-code]');
            if (!b || b.disabled) return;
            const code = b.getAttribute('data-code');
            if (selected.has(code)) selected.delete(code); else selected.add(code);
            render();
        });
        $('share-close').addEventListener('click', close);
        $('share-backdrop').addEventListener('click', close);
        $('share-save').addEventListener('click', save);
        $('share-link').addEventListener('click', shareLink);

        return { open: open };
    })();
</script>
