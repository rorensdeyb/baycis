@extends('layouts.borrower')

@section('content')
<div class="dashboard-wrapper">
    <div class="welcome-header">
        <div>
            <h1>Account Settings</h1>
            <p class="text-muted m-0">Manage your profile, password, and preferences.</p>
        </div>
    </div>
    <div class="activity-card">
        <p class="text-muted">Account settings form will go here.</p>
    </div>

    {{-- Sign Out Card: mobile-only (sidebar already has sign out on desktop) --}}
    <div class="d-md-none mt-3">
        <button class="w-100 border-0 p-0"
                data-bs-toggle="modal"
                data-bs-target="#logoutModal"
                style="background: transparent; cursor: pointer;">
            <div class="activity-card d-flex align-items-center justify-content-between" style="border-color: rgba(239,68,68,0.2);">
                {{-- Left: icon + text --}}
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; background: var(--accent-red-bg); border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-box-arrow-right text-danger" style="font-size: 20px;"></i>
                    </div>
                    <div class="text-start">
                        <div class="fw-semibold text-danger" style="font-size: 14px; line-height: 1.3;">Sign Out</div>
                        <div class="text-muted" style="font-size: 12px; line-height: 1.3;">End your current session</div>
                    </div>
                </div>
                {{-- Right: chevron indicator --}}
                <i class="bi bi-chevron-right text-danger" style="font-size: 14px; opacity: 0.6;"></i>
            </div>
        </button>
    </div>
</div>
@endsection