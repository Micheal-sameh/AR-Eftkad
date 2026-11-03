{{-- Placeholder person rows/cards (directory, users). Usage: @include('partials.skeleton-people', ['count' => 6]) --}}
@for ($i = 0; $i < ($count ?? 6); $i++)
    <div class="sk-card" aria-hidden="true" style="flex-direction:row;align-items:center">
        <span class="sk sk-avatar"></span>
        <div class="sk-col"><span class="sk sk-line" style="width:50%"></span><span class="sk sk-line" style="width:30%"></span></div>
        <span class="sk sk-badge"></span>
    </div>
@endfor
