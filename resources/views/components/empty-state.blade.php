<div class="text-center py-5">
    <div style="width: 72px; height: 72px; background: var(--bg-surface-hover); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
        <i class="{{ $icon ?? 'bi-inbox' }} fs-2 text-secondary"></i>
    </div>
    <h5 class="fw-bold" style="color: var(--text-primary);">{{ $title ?? 'No Items Found' }}</h5>
    <p class="small mb-3" style="color: var(--text-secondary); max-width: 360px; margin: 0 auto;">{{ $message ?? 'There are no items to display.' }}</p>
    @if(isset($actionUrl) && isset($actionLabel))
    <a href="{{ $actionUrl }}" class="btn fw-bold" style="background: var(--text-primary); color: var(--bg-surface); border-radius: 8px; padding: 8px 24px;">
        <i class="bi bi-plus-lg me-1"></i> {{ $actionLabel }}
    </a>
    @endif
    @if(isset($secondaryAction))
    {{ $secondaryAction }}
    @endif
</div>
