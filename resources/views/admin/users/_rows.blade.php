                    @forelse($users as $user)
                    <tr data-user-id="{{ $user->id }}">
                        <td class="ps-3">
                            <input type="checkbox" class="user-checkbox" value="{{ $user->id }}" onchange="updateBulkBar()" style="cursor: pointer;">
                        </td>
                        <td class="ps-1">
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $initial = mb_strtoupper(mb_substr(trim($user->name) !== '' ? trim($user->name) : '?', 0, 1));
                                    if ($user->role === 'admin') {
                                        $avBg = 'rgba(139, 92, 246, 0.14)'; $avFg = '#8b5cf6';
                                    } elseif ($user->role === 'custodian') {
                                        $avBg = 'rgba(25, 135, 84, 0.14)';  $avFg = '#198754';
                                    } else {
                                        $avBg = 'var(--accent-blue-bg)'; $avFg = 'var(--accent-blue)';
                                    }
                                @endphp
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                     title="{{ ucfirst($user->role) }}"
                                     style="width: 34px; height: 34px; font-size: 13px; background: {{ $avBg }}; color: {{ $avFg }};">
                                    {{ $initial }}
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold" style="color: var(--text-primary);">{{ $user->name }}</span>
                                    <span style="font-size: 12px; color: var(--text-secondary);">{{ $user->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td style="color: var(--text-secondary);">{{ $user->teacher_id ?? 'N/A' }}</td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge bg-icon-purple text-purple px-3 py-2 rounded-pill">Admin</span>
                            @elseif($user->role === 'custodian')
                                <span class="badge bg-icon-green text-green px-3 py-2 rounded-pill">Property Custodian</span>
                            @else
                                <span class="badge bg-icon-blue text-blue px-3 py-2 rounded-pill">{{ ucfirst($user->role) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($user->isScheduledForDeletion())
                                <span class="badge bg-danger text-white px-3 py-2 rounded-pill" title="Permanent removal on {{ $user->deletion_effective_at->format('M d, Y') }}">
                                    Deleting {{ $user->deletion_effective_at->format('M d, Y') }}
                                </span>
                            @elseif($user->hasPendingDeletionRequest())
                                <span class="badge bg-icon-yellow text-yellow px-3 py-2 rounded-pill">Deletion Requested</span>
                            @elseif($user->isAwaitingActivation())
                                <span class="badge bg-info text-white px-3 py-2 rounded-pill">Awaiting Approval</span>
                            @elseif($user->hasPendingDeactivationRequest())
                                <span class="badge bg-icon-yellow text-yellow px-3 py-2 rounded-pill">Deactivation Requested</span>
                            @elseif(is_null($user->email_verified_at))
                                <span class="badge bg-icon-yellow text-yellow px-3 py-2 rounded-pill">Pending OTP</span>
                            @elseif(!$user->is_active)
                                <span class="badge bg-secondary text-white px-3 py-2 rounded-pill">Deactivated</span>
                            @else
                                <span class="badge bg-icon-green text-green px-3 py-2 rounded-pill">Active</span>
                            @endif
                        </td>
                        <td class="d-none d-md-table-cell" style="font-size: 12px; color: var(--text-secondary);">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->diffForHumans() }}
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1 justify-content-center">
                                {{-- Quick lifecycle actions â€” one tap, no menu needed --}}
                                @php
                                    $cleanName = addslashes($user->name);
                                    $deactReason = addslashes(preg_replace('/\s+/', ' ', (string) $user->deactivation_reason));
                                    $delReason = addslashes(preg_replace('/\s+/', ' ', (string) $user->deletion_reason));
                                @endphp
                                @if($user->isAwaitingActivation())
                                    <button type="button" class="btn btn-sm btn-success" style="padding: 4px 9px;" title="Approve activation"
                                            onclick="lifecycleAction({{ $user->id }}, 'approve_activation', '{{ $cleanName }}')">
                                        <i class="bi bi-person-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" style="padding: 4px 9px;" title="Reject registration"
                                            onclick="lifecycleAction({{ $user->id }}, 'reject_activation', '{{ $cleanName }}')">
                                        <i class="bi bi-person-x"></i>
                                    </button>
                                @elseif($user->hasPendingDeactivationRequest())
                                    <button type="button" class="btn btn-sm btn-warning" style="padding: 4px 9px;" title="Approve deactivation"
                                            onclick="lifecycleAction({{ $user->id }}, 'approve_deactivation', '{{ $cleanName }}', '{{ $deactReason }}')">
                                        <i class="bi bi-pause-circle"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" style="padding: 4px 9px;" title="Reject request"
                                            onclick="lifecycleAction({{ $user->id }}, 'reject_deactivation', '{{ $cleanName }}')">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                @elseif($user->hasPendingDeletionRequest())
                                    <button type="button" class="btn btn-sm btn-danger" style="padding: 4px 9px;" title="Approve deletion (starts 60-day grace period)"
                                            onclick="lifecycleAction({{ $user->id }}, 'approve_deletion', '{{ $cleanName }}', '{{ $delReason }}')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" style="padding: 4px 9px;" title="Reject request"
                                            onclick="lifecycleAction({{ $user->id }}, 'reject_deletion', '{{ $cleanName }}')">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                @elseif($user->isScheduledForDeletion())
                                    <button type="button" class="btn btn-sm btn-success" style="padding: 4px 9px;" title="Cancel scheduled deletion & restore account"
                                            onclick="lifecycleAction({{ $user->id }}, 'cancel_deletion', '{{ $cleanName }}')">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                @endif

                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" data-bs-strategy="fixed" aria-expanded="false"
                                            style="background: transparent; border: none; color: var(--text-secondary); padding: 4px 8px; border-radius: 8px; transition: all 0.15s ease;"
                                            onmouseover="this.style.background='var(--bg-main)'; this.style.color='var(--text-primary)'"
                                            onmouseout="this.style.background='transparent'; this.style.color='var(--text-secondary)'">
                                        <i class="bi bi-three-dots-vertical" style="font-size: 18px;"></i>
                                    </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-surface); min-width: 180px; padding: 6px;">
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center gap-2" onclick="event.stopPropagation(); openUserDrawer({{ $user->id }})" 
                                                style="border-radius: 6px; font-size: 13px; padding: 8px 12px; color: var(--text-primary);">
                                            <i class="bi bi-eye" style="font-size: 15px; width: 20px; color: var(--accent-blue);"></i>
                                            View Full Account
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center gap-2" onclick="editUser({{ $user->id }})"
                                                style="border-radius: 6px; font-size: 13px; padding: 8px 12px; color: var(--text-primary);">
                                            <i class="bi bi-pencil-square" style="font-size: 15px; width: 20px; color: var(--accent-blue);"></i>
                                            Edit
                                        </button>
                                    </li>
                                    @if($user->id !== Auth::id())
                                    <li><hr class="dropdown-divider" style="border-color: var(--border-color); margin: 4px 0;"></li>
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center gap-2" onclick="resetPassword({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                style="border-radius: 6px; font-size: 13px; padding: 8px 12px; color: var(--text-primary);">
                                            <i class="bi bi-key" style="font-size: 15px; width: 20px; color: #f59e0b;"></i>
                                            Reset Password
                                        </button>
                                    </li>
                                    {{-- â”€â”€ ACCOUNT LIFECYCLE ACTIONS (context-aware) --}}
                                    @if($user->isAwaitingActivation())
                                    <li><hr class="dropdown-divider" style="border-color: var(--border-color); margin: 4px 0;"></li>
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center gap-2" onclick="lifecycleAction({{ $user->id }}, 'approve_activation', '{{ addslashes($user->name) }}')"
                                                style="border-radius: 6px; font-size: 13px; padding: 8px 12px; color: #198754;">
                                            <i class="bi bi-person-check" style="font-size: 15px; width: 20px; color: #198754;"></i> Approve Activation
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center gap-2" onclick="lifecycleAction({{ $user->id }}, 'reject_activation', '{{ addslashes($user->name) }}')"
                                                style="border-radius: 6px; font-size: 13px; padding: 8px 12px; color: #ef4444;">
                                            <i class="bi bi-person-x" style="font-size: 15px; width: 20px; color: #ef4444;"></i> Reject Registration
                                        </button>
                                    </li>
                                    @endif
                                    @if($user->hasPendingDeactivationRequest())
                                    <li><hr class="dropdown-divider" style="border-color: var(--border-color); margin: 4px 0;"></li>
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center gap-2" onclick="lifecycleAction({{ $user->id }}, 'approve_deactivation', '{{ addslashes($user->name) }}', '{{ $deactReason }}')"
                                                style="border-radius: 6px; font-size: 13px; padding: 8px 12px; color: #f59e0b;">
                                            <i class="bi bi-pause-circle" style="font-size: 15px; width: 20px; color: #f59e0b;"></i> Approve Deactivation
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center gap-2" onclick="lifecycleAction({{ $user->id }}, 'reject_deactivation', '{{ addslashes($user->name) }}')"
                                                style="border-radius: 6px; font-size: 13px; padding: 8px 12px; color: #198754;">
                                            <i class="bi bi-check-circle" style="font-size: 15px; width: 20px; color: #198754;"></i> Reject Request
                                        </button>
                                    </li>
                                    @endif
                                    @if($user->hasPendingDeletionRequest())
                                    <li><hr class="dropdown-divider" style="border-color: var(--border-color); margin: 4px 0;"></li>
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center gap-2" onclick="lifecycleAction({{ $user->id }}, 'approve_deletion', '{{ addslashes($user->name) }}', '{{ $delReason }}')"
                                                style="border-radius: 6px; font-size: 13px; padding: 8px 12px; color: #ef4444;">
                                            <i class="bi bi-trash3" style="font-size: 15px; width: 20px; color: #ef4444;"></i> Approve Deletion
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center gap-2" onclick="lifecycleAction({{ $user->id }}, 'reject_deletion', '{{ addslashes($user->name) }}')"
                                                style="border-radius: 6px; font-size: 13px; padding: 8px 12px; color: #198754;">
                                            <i class="bi bi-check-circle" style="font-size: 15px; width: 20px; color: #198754;"></i> Reject Request
                                        </button>
                                    </li>
                                    @endif
                                    @if($user->isScheduledForDeletion())
                                    <li><hr class="dropdown-divider" style="border-color: var(--border-color); margin: 4px 0;"></li>
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center gap-2" onclick="lifecycleAction({{ $user->id }}, 'cancel_deletion', '{{ addslashes($user->name) }}')"
                                                style="border-radius: 6px; font-size: 13px; padding: 8px 12px; color: #198754;">
                                            <i class="bi bi-arrow-counterclockwise" style="font-size: 15px; width: 20px; color: #198754;"></i> Cancel Scheduled Deletion
                                        </button>
                                    </li>
                                    @endif
                                    @endif
                                </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-people text-muted fs-1 mb-2 d-block"></i>
                            <p class="text-muted mb-0">No users found matching your criteria.</p>
                        </td>
                    </tr>
                    @endforelse
