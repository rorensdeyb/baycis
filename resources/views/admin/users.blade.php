@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="page-header mb-0">
            <h1>User Management</h1>
            <p>Manage system access, roles, and view user statuses.</p>
        </div>
        <button class="btn btn-primary fw-bold px-4 py-2" data-bs-toggle="modal" data-bs-target="#createUserModal" style="border-radius: 10px; background-color: var(--accent-blue); border: none;">
            <i class="bi bi-plus-lg me-2"></i> Add New User
        </button>
    </div>

    <div id="users-table" class="panel-card p-0 overflow-hidden shadow-sm">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Name & Email</th>
                        <th>Teacher ID</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex flex-column">
                                <span class="fw-bold" style="color: var(--text-primary);">{{ $user->name }}</span>
                                <span style="font-size: 12px; color: var(--text-secondary);">{{ $user->email }}</span>
                            </div>
                        </td>
                        <td style="color: var(--text-secondary);">{{ $user->teacher_id ?? 'N/A' }}</td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge bg-icon-purple text-purple px-3 py-2 rounded-pill">Admin</span>
                            @else
                                <span class="badge bg-icon-blue text-blue px-3 py-2 rounded-pill">{{ ucfirst($user->role) }}</span>
                            @endif
                        </td>
                        <td>
                            @if(is_null($user->email_verified_at))
                                <span class="badge bg-icon-yellow text-yellow px-3 py-2 rounded-pill">Pending OTP</span>
                            @else
                                <span class="badge bg-icon-green text-green px-3 py-2 rounded-pill">Active</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <button class="icon-btn me-2" title="Edit"><i class="bi bi-pencil-square"></i></button>
                            <form action="/admin/users/{{ $user->id }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this user?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn text-danger" title="Delete" style="background: transparent; border: none;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="bi bi-people text-muted fs-1 mb-2 d-block"></i>
                            <p class="text-muted mb-0">No users found in the system.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
        <div class="p-3 border-top" style="border-color: var(--border-color) !important;">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>

<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px; background-color: var(--bg-surface); border: 1px solid var(--border-color);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: var(--text-primary);">Create New Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="createUserForm">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: var(--text-secondary); font-size: 13px;">Full Name</label>
                        <input type="text" class="form-control custom-input" name="name" placeholder="e.g. Jane Smith" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: var(--text-secondary); font-size: 13px;">Email Address</label>
                        <input type="email" class="form-control custom-input" name="email" placeholder="name@bces.edu.ph" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color: var(--text-secondary); font-size: 13px;">Teacher ID</label>
                            <input type="text" class="form-control custom-input" name="teacher_id" placeholder="TCH-XXX" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color: var(--text-secondary); font-size: 13px;">Role</label>
                            <select class="form-select custom-input" name="role" required>
                                <option value="borrower" selected>Borrower</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="alert mt-4 mb-0" style="background-color: var(--accent-blue-bg); border: 1px solid var(--accent-blue); color: var(--accent-blue); border-radius: 8px; font-size: 13px;">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        The user's temporary password will be <strong>BayCIS2026!</strong> They will be required to verify via OTP on their first login.
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-between">
                <button type="button" class="btn btn-light fw-semibold px-4" data-bs-dismiss="modal" style="border-radius: 8px; background-color: var(--bg-surface-hover); color: var(--text-primary); border: 1px solid var(--border-color);">Cancel</button>
                <button type="button" class="btn btn-primary fw-bold px-4" id="saveUserBtn" style="border-radius: 8px; background-color: var(--accent-blue); border: none;">Create Account</button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 1055;">
    <div id="liveToast" class="toast border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 12px; background-color: var(--bg-surface);">
        <div class="d-flex align-items-center p-3 rounded-top" id="toastHeader">
            <i id="toastIcon" class="bi bi-check-circle-fill text-white me-2 fs-5"></i>
            <div class="toast-body flex-grow-1 fw-bold text-white p-0 ms-2" id="toastMessage">
            </div>
            <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
// --- TOAST NOTIFICATION ENGINE ---
function showNotify(message, type = 'success') {
    const toastElement = document.getElementById('liveToast');
    const toastHeader = document.getElementById('toastHeader');
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');

    if (type === 'success') {
        toastHeader.style.backgroundColor = 'var(--accent-green)';
        toastIcon.className = 'bi bi-check-circle-fill text-white fs-5';
    } else {
        toastHeader.style.backgroundColor = 'var(--accent-red)';
        toastIcon.className = 'bi bi-exclamation-circle-fill text-white fs-5';
    }

    toastMessage.innerText = message;
    const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
    toast.show();
}

// --- CREATE USER LOGIC ---
document.addEventListener('DOMContentLoaded', function () {
    const saveBtn = document.getElementById('saveUserBtn');
    const form = document.getElementById('createUserForm');

    saveBtn.addEventListener('click', async function () {
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creating...';

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('/admin/users', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                const modalElement = document.getElementById('createUserModal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                modal.hide();

                showNotify("Account created successfully! They can now log in to verify their email.", "success");

                form.reset();
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Create Account';

                setTimeout(() => { window.location.reload(); }, 1500);
            } else {
                let errorMessage = 'Failed to create user.\n';
                if (result.errors) {
                    for (const [field, errors] of Object.entries(result.errors)) {
                        errorMessage += `${errors[0]}\n`;
                    }
                }
                showNotify(errorMessage, "error");
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Create Account';
            }
        } catch (error) {
            console.error('Error:', error);
            showNotify("A network error occurred. Please try again.", "error");
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Create Account';
        }
    });

    // Fix scroll position: append #users-table anchor to all pagination links
    // so clicking next/prev scrolls to the table, not back to the very top.
    document.querySelectorAll('.pagination a').forEach(function(link) {
        const href = link.getAttribute('href');
        if (href && !href.includes('#')) {
            link.setAttribute('href', href + '#users-table');
        }
    });
});
</script>
@endsection