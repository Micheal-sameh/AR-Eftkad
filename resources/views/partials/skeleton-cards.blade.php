{{-- Placeholder visit cards shown while the real ones load. Usage: @include('partials.skeleton-cards', ['count' => 4]) --}}
@for ($i = 0; $i < ($count ?? 4); $i++)
    <div class="sk-card" aria-hidden="true">
        <div class="sk-row">
            <span class="sk sk-avatar"></span>
            <div class="sk-col"><span class="sk sk-line" style="width:55%"></span><span class="sk sk-line" style="width:35%"></span></div>
            <span class="sk sk-badge"></span>
        </div>
        <div class="sk-row"><span class="sk sk-line" style="width:24%"></span><span class="sk sk-line" style="width:28%"></span><span class="sk sk-line" style="width:28%"></span></div>
        <span class="sk sk-line" style="width:70%"></span>
    </div>
@endfor
