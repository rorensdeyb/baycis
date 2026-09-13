@extends('layouts.admin')

@section('content')
<style>
    .ref-card { transition: box-shadow .15s ease, border-color .15s ease; }
    .ref-card:hover { border-color: var(--accent-blue); }
    .ref-header { cursor: pointer; user-select: none; background: none; border: none; width: 100%; text-align: left; padding: 0; }
    .ref-header:focus-visible { outline: 2px solid var(--accent-blue); outline-offset: 3px; border-radius: 8px; }
    .ref-chevron { transition: transform .2s ease; color: var(--text-secondary); }
    .ref-card.is-open .ref-chevron { transform: rotate(180deg); }
    .ref-card.is-open { border-color: var(--accent-blue) !important; }
    .ref-preview { margin-top: 10px; }
    .ref-card.is-open .ref-preview { display: none !important; }
    .ref-pill { display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:16px;
                font-size:12.5px; font-weight:600; background:var(--bg-main);
                border:1px solid var(--border-color); color:var(--text-primary); }
    .ref-add-form input:focus { border-color: var(--accent-blue) !important; }
</style>

<div class="dashboard-wrapper">

    {{-- ── Header ── --}}
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <a href="{{ route('admin.settings') }}" class="d-inline-flex align-items-center gap-1 text-decoration-none mb-1"
               style="font-size: 12.5px; color: var(--text-secondary);">
                <i class="bi bi-arrow-left"></i> Back to Settings
            </a>
            <h1 class="h3 fw-bold mb-0" style="color: var(--text-primary);">
                <i class="bi bi-bookmark-star me-2 text-primary"></i> Inventory Reference
            </h1>
            <p class="text-secondary mb-0" style="font-size: 13px;">
                Sub-categories ("Tags") for each Asset Category — they power the dropdown on asset forms and help borrowers browse by general type.
            </p>
        </div>
        <a href="{{ route('admin.settings') }}" class="btn fw-semibold d-inline-flex align-items-center gap-2 px-3 py-2"
           style="border-radius: 10px; border: 1px solid var(--border-color); color: var(--text-primary); background: var(--bg-surface);">
            <i class="bi bi-tags"></i> Asset Categories
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 d-flex align-items-center gap-2 fw-semibold shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div class="ms-2">{{ session('success') }}</div>
            <button type="button" class="btn-close shadow-none ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center gap-2 fw-semibold shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div class="ms-2">{{ session('error') }}</div>
            <button type="button" class="btn-close shadow-none ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $totalTags = $categories->sum(fn ($c) => $c->tags->count());
        $usedCategories = $categories->filter(fn ($c) => $c->tags->count() > 0)->count();
    @endphp

    {{-- ── Toolbar: stats · search · expand/collapse ── --}}
    <div class="panel-card px-3 py-2 mb-4 d-flex flex-wrap align-items-center gap-2" style="border-radius: 12px;">
        <span class="d-inline-flex align-items-center gap-1 fw-semibold" style="font-size: 12.5px; color: var(--text-secondary);">
            <i class="bi bi-collection"></i>
            {{ $categories->count() }} categories
        </span>
        <span class="text-secondary">·</span>
        <span class="d-inline-flex align-items-center gap-1 fw-semibold" style="font-size: 12.5px; color: var(--text-secondary);">
            <i class="bi bi-bookmark-fill" style="color: var(--accent-blue);"></i>
            {{ $totalTags }} tags on {{ $usedCategories }} categories
        </span>

        <div class="position-relative ms-auto" style="min-width: 220px;">
            <i class="bi bi-search" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-secondary); font-size:13px;"></i>
            <input type="text" id="refSearch" class="form-control form-control-sm" autocomplete="off"
                   placeholder="Filter by category or tag…"
                   style="padding-left:34px; border-radius:10px; background:var(--bg-main); border-color:var(--border-color); color:var(--text-primary);">
        </div>

        <div class="btn-group">
            <button type="button" id="refExpandAll" class="btn btn-sm fw-semibold" title="Expand all"
                    style="border-radius:8px 0 0 8px; border:1px solid var(--border-color); color:var(--text-secondary); background:transparent;">
                <i class="bi bi-chevron-bar-down"></i>
            </button>
            <button type="button" id="refCollapseAll" class="btn btn-sm fw-semibold" title="Collapse all"
                    style="border-radius:0 8px 8px 0; border:1px solid var(--border-color); border-left:none; color:var(--text-secondary); background:transparent;">
                <i class="bi bi-chevron-bar-up"></i>
            </button>
        </div>
    </div>

    {{-- ── Category cards (2-col grid) ── --}}
    <div class="row g-3" id="refGrid">
        @foreach($categories as $category)
        <div class="col-12 col-xl-6 ref-item" id="cat-{{ $category->id }}" data-search="{{ strtolower($category->name) }} {{ strtolower($category->tags->pluck('name')->implode(' ')) }}" style="scroll-margin-top: 100px;">
            <div class="panel-card p-3 ref-card h-100 {{ $loop->first ? 'is-open' : '' }}" style="border-radius: 12px;">
                {{-- Card header (toggle) --}}
                <button type="button" class="ref-header d-flex align-items-center gap-2"
                        data-bs-toggle="collapse" data-bs-target="#ref-body-{{ $category->id }}"
                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="ref-body-{{ $category->id }}">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:34px;height:34px;border-radius:9px;background:var(--accent-blue-bg);color:var(--accent-blue);">
                        <i class="bi bi-folder2" style="font-size:14px;"></i>
                    </div>
                    <div class="flex-grow-1 text-start" style="min-width:0;">
                        <div class="fw-bold text-truncate" style="font-size:14.5px; color:var(--text-primary);">{{ $category->name }}</div>
                        <div style="font-size:11.5px;color:var(--text-secondary);">
                            {{ $category->items_count ?? 0 }} asset{{ ($category->items_count ?? 0) === 1 ? '' : 's' }}
                            · PPE {{ $category->ppe_sub_major ?: '—' }}
                        </div>
                    </div>
                    <span class="badge rounded-pill px-2 py-1"
                          style="background: rgba(59,130,246,0.1); color: var(--accent-blue); font-weight:700;">
                        {{ $category->tags->count() }}
                    </span>
                    <i class="bi bi-chevron-down ref-chevron ms-1"></i>
                </button>

                {{-- Collapsed preview: first tags so you can scan without opening --}}
                <div class="ref-preview d-flex flex-wrap gap-1">
                    @foreach($category->tags->take(4) as $t)
                        <span class="badge rounded-pill" style="font-weight:600; font-size:11px; background: rgba(59,130,246,0.08); color: var(--accent-blue);">{{ $t->name }}</span>
                    @endforeach
                    @if($category->tags->count() > 4)
                        <span class="badge rounded-pill" style="font-weight:600; font-size:11px; background: var(--bg-main); color: var(--text-secondary);">+{{ $category->tags->count() - 4 }}</span>
                    @endif
                    @if($category->tags->count() === 0)
                        <span style="font-size:11.5px; color:var(--text-secondary); font-style:italic;">No tags yet</span>
                    @endif
                </div>

                {{-- Body --}}
                <div class="collapse {{ $loop->first ? 'show' : '' }}" id="ref-body-{{ $category->id }}">
                    <div class="mt-3 pt-3" style="border-top: 1px dashed var(--border-color);">

                        <form method="POST" action="{{ route('admin.settings.tags.store') }}"
                              class="ref-add-form d-flex flex-wrap align-items-center gap-2 mb-3 p-2"
                              style="background: var(--bg-main); border-radius: 10px; border: 1px dashed var(--border-color);"
                              data-ref-category="{{ $category->id }}">
                            @csrf
                            <input type="hidden" name="category_id" value="{{ $category->id }}">
                            <input type="text" name="name" class="form-control form-control-sm theme-dynamic-input flex-grow-1"
                                   placeholder="New sub-category…" maxlength="100" required style="min-width: 180px;">
                            <button type="submit" class="btn btn-primary btn-sm fw-bold d-inline-flex align-items-center gap-1 px-3"
                                    style="border-radius: 8px; background: var(--accent-blue); border: none;">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </form>

                        @if($category->tags->count() > 0)
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($category->tags as $tag)
                                    <span class="ref-pill">
                                        {{ $tag->name }}
                                        <button type="button" class="btn btn-sm p-0 lh-1" title="Rename tag"
                                                style="color: var(--text-secondary); background: none; border: none;"
                                                onclick="renameTag({{ $tag->id }}, {{ $category->id }}, '{{ addslashes($tag->name) }}')">
                                            <i class="bi bi-pencil" style="font-size: 11px;"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.settings.tags.destroy', $tag->id) }}" class="m-0 d-inline"
                                              onsubmit="return confirm('Delete the tag \"{{ addslashes($tag->name) }}\"? Items using it will simply show no tag.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm p-0 lh-1" title="Delete tag"
                                                    style="color: #dc3545; background: none; border: none;">
                                                <i class="bi bi-x-lg" style="font-size: 12px;"></i>
                                            </button>
                                        </form>
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div style="font-size: 12.5px; color: var(--text-secondary); font-style: italic;">Use the box above to add the first sub-category.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div id="refEmpty" class="text-center py-5" style="display: none;">
        <i class="bi bi-search fs-1 d-block mb-2 text-secondary"></i>
        <p class="fw-semibold" style="color: var(--text-primary);">No categories or tags match your search.</p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ── Chevron + open-state sync with Bootstrap collapse ──
        document.querySelectorAll('.ref-card').forEach(function (card) {
            const collapseEl = card.querySelector('.collapse');
            if (!collapseEl) return;
            collapseEl.addEventListener('shown.bs.collapse', function () { card.classList.add('is-open'); });
            collapseEl.addEventListener('hidden.bs.collapse', function () { card.classList.remove('is-open'); });
            if (collapseEl.classList.contains('show')) card.classList.add('is-open');
        });

        // ── Reopen the category that was just edited/added ──
        const lastCat = sessionStorage.getItem('refOpenCategory');
        if (lastCat) {
            sessionStorage.removeItem('refOpenCategory');
            const target = document.getElementById('cat-' + lastCat);
            const card = target?.querySelector('.ref-card');
            const body = document.getElementById('ref-body-' + lastCat);
            if (body && !body.classList.contains('show')) bootstrap.Collapse.getOrCreateInstance(body).show();
            if (target) {
                setTimeout(function () { target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 150);
                card?.classList.add('is-open');
            }
        }

        // ── Remember open card when submitting its add form ──
        document.querySelectorAll('.ref-add-form').forEach(function (form) {
            form.addEventListener('submit', function () {
                sessionStorage.setItem('refOpenCategory', form.dataset.refCategory);
            });
        });

        // ── Expand / collapse all ──
        function allCollapses(action) {
            document.querySelectorAll('#refGrid .collapse').forEach(function (c) {
                bootstrap.Collapse.getOrCreateInstance(c)[action]();
            });
        }
        document.getElementById('refExpandAll')?.addEventListener('click', function () { allCollapses('show'); });
        document.getElementById('refCollapseAll')?.addEventListener('click', function () { allCollapses('hide'); });

        // ── Live search across category names AND tag names ──
        const searchInput = document.getElementById('refSearch');
        let timer = null;
        searchInput?.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                const q = searchInput.value.trim().toLowerCase();
                let visible = 0;
                document.querySelectorAll('.ref-item').forEach(function (item) {
                    const match = !q || item.dataset.search.includes(q);
                    item.style.display = match ? '' : 'none';
                    if (match) visible++;
                });
                document.getElementById('refEmpty').style.display = visible === 0 ? 'block' : 'none';
            }, 150);
        });
    });
</script>
@endsection
