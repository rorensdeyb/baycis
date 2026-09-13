/* ═══════════════════════════════════════════════════════
   BayCIS Borrower Portal — Shared JavaScript
   Extracted from inline <script> blocks for caching
   ═══════════════════════════════════════════════════════ */

// ── T O A S T   S Y S T E M ──────────────────────────
function playNotificationSound() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
        osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15); // A5
        gain.gain.setValueAtTime(0.001, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.18, ctx.currentTime + 0.05);
        gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.35);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.35);
    } catch (e) {}
}

function showToast(title, message, type, options) {
    type = type || 'info';
    options = options || {};
    const container = document.getElementById('toastContainer');
    if (!container) return;

    if (options.sound) {
        playNotificationSound();
    }

    // Limit maximum visible toasts to 4 to prevent viewport flooding
    const existingToasts = container.querySelectorAll('.toast-custom:not(.removing)');
    if (existingToasts.length >= 4) {
        const oldest = existingToasts[0];
        oldest.classList.add('removing');
        setTimeout(() => { if (oldest.parentNode) oldest.remove(); }, 320);
    }

    const icons = {
        success: '<i class="bi bi-check-circle-fill"></i>',
        error:   '<i class="bi bi-x-circle-fill"></i>',
        warning: '<i class="bi bi-exclamation-triangle-fill"></i>',
        info:    '<i class="bi bi-info-circle-fill"></i>'
    };

    const el = document.createElement('div');
    el.className = 'toast-custom toast-' + type;
    el.setAttribute('role', 'alert');
    el.setAttribute('aria-live', 'assertive');
    el.setAttribute('aria-atomic', 'true');

    let actionBtnHtml = '';
    if (options.url) {
        actionBtnHtml = `<a href="${escapeHtml(options.url)}" class="toast-action-link" onclick="event.stopPropagation()">View Details <i class="bi bi-arrow-right"></i></a>`;
    }

    el.innerHTML = `
        <div class="toast-icon-wrap">
            <span class="toast-icon">${icons[type] || icons.info}</span>
        </div>
        <div class="toast-body">
            <div class="toast-title">${escapeHtml(title)}</div>
            ${message ? `<div class="toast-message">${escapeHtml(message)}</div>` : ''}
            ${actionBtnHtml}
        </div>
        <button type="button" class="toast-close" aria-label="Close">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="toast-progress-bar"></div>
    `;

    let isDismissed = false;
    function dismissToast() {
        if (isDismissed) return;
        isDismissed = true;
        if (dismissTimer) clearTimeout(dismissTimer);
        el.classList.add('removing');
        setTimeout(() => {
            if (el.parentNode) el.remove();
        }, 320);
    }

    const closeBtn = el.querySelector('.toast-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            dismissToast();
        });
    }

    el.addEventListener('click', function(e) {
        e.stopPropagation();
        if (e.target.closest('.toast-close') || e.target.closest('.toast-action-link')) return;
        if (options.url) {
            window.location.href = options.url;
            return;
        }
        dismissToast();
    });

    const duration = options.duration || 5500;
    let remainingTime = duration;
    let startTime = Date.now();
    let dismissTimer = setTimeout(dismissToast, duration);

    const progressBar = el.querySelector('.toast-progress-bar');
    if (progressBar) {
        progressBar.style.animationDuration = duration + 'ms';
    }

    el.addEventListener('mouseenter', function() {
        if (isDismissed) return;
        clearTimeout(dismissTimer);
        remainingTime -= (Date.now() - startTime);
        if (progressBar) {
            progressBar.style.animationPlayState = 'paused';
        }
    });

    el.addEventListener('mouseleave', function() {
        if (isDismissed) return;
        startTime = Date.now();
        if (remainingTime < 1200) remainingTime = 1200;
        if (progressBar) {
            progressBar.style.animationPlayState = 'running';
        }
        dismissTimer = setTimeout(dismissToast, remainingTime);
    });

    container.appendChild(el);
}
window.showToast = showToast;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ── O N E - T A P   S U B M I T   G U A R D ──────────
// Prevents duplicate transactions from double-taps on slow connections.
// Usage: <button onclick="return lockSubmit(this, 'Saving…')"> — returns false
// (blocking submit) while busy, true once; auto-unlocks after 8s as a safety net.
window.lockSubmit = function (btn, label, confirmMsg) {
    if (btn.dataset.busy === '1') return false;
    if (confirmMsg && !window.confirm(confirmMsg)) return false;

    const form = btn.closest('form');
    if (form && !form.checkValidity()) {
        form.reportValidity();
        return false;
    }

    btn.dataset.busy = '1';
    btn.dataset.origHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + (label || 'Working…');

    // Defer disabling the button so the browser's form submission pipeline is triggered first!
    setTimeout(function () {
        btn.disabled = true;
    }, 10);

    setTimeout(function () { // safety net: never leave a button dead forever
        if (btn.dataset.busy === '1' && document.body.contains(btn)) {
            btn.dataset.busy = '';
            btn.disabled = false;
            if (btn.dataset.origHtml) btn.innerHTML = btn.dataset.origHtml;
        }
    }, 8000);

    return true;
};

// ── T H E M E   T O G G L E ──────────────────────────
function toggleTheme() {
    const html = document.documentElement;
    const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);

    const themeIcon = document.getElementById('theme-icon');
    if (themeIcon) {
        themeIcon.className = newTheme === 'dark' ? 'bi bi-moon-stars' : 'bi bi-sun';
    }
}

// ── P A L E T T E   S E T T E R ──────────────────────
function setPalette(name) {
    localStorage.setItem('palette', name);
    document.documentElement.setAttribute('data-palette', name);
}

// ── L O G O U T   E X E C U T I O N ──────────────────
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirm-logout-btn')?.addEventListener('click', async function() {
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>...';
        try {
            const response = await fetch('/logout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
            });
            if (response.ok) window.location.replace('/');
        } catch (error) { console.error('Logout error:', error); this.disabled = false; }
    });
});

// ── D O M   I N I T ──────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    const themeIcon = document.getElementById('theme-icon');
    if (themeIcon) {
        themeIcon.className = savedTheme === 'dark' ? 'bi bi-moon-stars' : 'bi bi-sun';
    }
    const savedPalette = localStorage.getItem('palette') || 'default';
    document.documentElement.setAttribute('data-palette', savedPalette);

    // B-UI5: Connection indicator
    var connDot = document.getElementById('connectionDot');
    if (connDot) {
        function updateConnection() {
            connDot.style.background = navigator.onLine ? '#10b981' : '#ef4444';
            connDot.title = navigator.onLine ? 'Connected' : 'Offline';
        }
        updateConnection();
        window.addEventListener('online', updateConnection);
        window.addEventListener('offline', updateConnection);
        setInterval(function() {
            fetch('/favicon.ico', { method: 'HEAD', cache: 'no-store' })
                .then(function() { connDot.style.background = '#10b981'; })
                .catch(function() { connDot.style.background = '#f59e0b'; });
        }, 30000);
    }
});

// ── P U L L - T O - R E F R E S H ────────────────────
(function() {
    var touchStartY = 0;
    var pulling = false;
    var pullIndicator = null;

    function createPullIndicator() {
        pullIndicator = document.createElement('div');
        pullIndicator.id = 'pull-to-refresh';
        pullIndicator.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;background:var(--bg-surface);border-bottom:1px solid var(--border-color);transform:translateY(-100%);transition:transform 0.3s cubic-bezier(0.16,1,0.3,1);';
        pullIndicator.innerHTML = '<div class="d-flex align-items-center gap-2"><div class="spinner-border spinner-border-sm" style="color:var(--accent-color);width:18px;height:18px;"></div><span style="font-size:13px;font-weight:600;color:var(--text-secondary);">Refreshing...</span></div>';
        document.body.appendChild(pullIndicator);
    }
    createPullIndicator();

    document.addEventListener('touchstart', function(e) {
        if (window.scrollY > 0) return;
        if (e.target.closest('.modal') || e.target.closest('.dropdown-menu') || e.target.closest('.pin-overlay')) return;
        touchStartY = e.touches[0].clientY;
        pulling = true;
    }, {passive: true});

    document.addEventListener('touchmove', function(e) {
        if (!pulling) return;
        var dy = e.touches[0].clientY - touchStartY;
        if (dy > 0 && window.scrollY <= 0) {
            var resistance = Math.min(dy * 0.3, 80);
            if (pullIndicator) pullIndicator.style.transform = 'translateY(' + (resistance - 100) + 'px)';
        }
    }, {passive: true});

    document.addEventListener('touchend', function() {
        if (!pulling) return;
        pulling = false;
        if (pullIndicator) {
            var transform = pullIndicator.style.transform;
            var match = transform.match(/translateY\(([0-9.]+)px\)/);
            if (match && parseFloat(match[1]) > -30) {
                pullIndicator.style.transform = 'translateY(0)';
                setTimeout(function() { window.location.reload(); }, 600);
            } else {
                pullIndicator.style.transform = 'translateY(-100%)';
            }
        }
    }, {passive: true});
})();

// ── N O T I F I C A T I O N   P O L L I N G ──────────
(function() {
    const ACTIVE_POLL_INTERVAL = 4000;
    const HIDDEN_POLL_INTERVAL = 25000;
    const notifBtn = document.getElementById('borrowerBellBtn') || document.querySelector('.top-navbar .dropdown .icon-btn.position-relative') || document.querySelector('.dropdown .icon-btn.position-relative');
    const notifDropdown = document.getElementById('borrowerNotifDropdown') || document.querySelector('.dropdown .notif-dropdown-menu') || document.querySelector('.top-navbar .dropdown .dropdown-menu');

    if (!notifBtn) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let lastSeenNotifId = null;
    let isPolling = false;
    let currentInterval = ACTIVE_POLL_INTERVAL;
    let pollTimer = null;

    let currentFilter = 'all';

    function _esc(t) {
        var d = document.createElement('div');
        d.textContent = t || '';
        return d.innerHTML;
    }

    function updateBadge(id, count) {
        const el = document.getElementById(id);
        if (!el) return;
        const currentCount = parseInt(el.textContent.trim()) || 0;
        const newCount = parseInt(count) || 0;
        el.textContent = newCount;
        if (newCount > 0) {
            el.classList.remove('d-none');
            if (newCount !== currentCount) {
                el.classList.remove('badge-pulse');
                void el.offsetWidth; // trigger reflow
                el.classList.add('badge-pulse');
            }
        } else {
            el.classList.add('d-none');
        }
    }

    function updateBnCount(id, count) {
        const el = document.getElementById(id);
        if (!el) return;
        const newCount = parseInt(count) || 0;
        el.textContent = newCount > 9 ? '9+' : newCount;
        if (newCount > 0) {
            el.classList.remove('d-none');
        } else {
            el.classList.add('d-none');
        }
    }

    function updateNotifCounters(unread, total) {
        updateBadge('borrowerBellBadge', unread);

        const pill = document.getElementById('headerUnreadPill');
        if (pill) {
            pill.textContent = unread + ' new';
            if (unread > 0) pill.classList.remove('d-none');
            else pill.classList.add('d-none');
        }

        const markAllBtn = document.getElementById('btnMarkAllRead');
        if (markAllBtn) {
            if (unread > 0) markAllBtn.classList.remove('d-none');
            else markAllBtn.classList.add('d-none');
        }

        const clearAllBtn = document.getElementById('btnClearAllNotifs');
        if (clearAllBtn) {
            if (total > 0) clearAllBtn.classList.remove('d-none');
            else clearAllBtn.classList.add('d-none');
        }

        const countAllEl = document.getElementById('notifTabCountAll');
        if (countAllEl) countAllEl.textContent = total;

        const countUnreadEl = document.getElementById('notifTabCountUnread');
        if (countUnreadEl) countUnreadEl.textContent = unread;
    }

    function renderNotificationItem(n) {
        const isUnread = !n.is_read;
        const bg = n.type_bg || 'rgba(25,135,84,0.12)';
        const icon = n.type_icon || 'bi-bell-fill';
        const colorClass = n.type_class || 'text-success';
        const targetUrl = n.target_url || n.url || '#';
        const toggleIcon = isUnread ? 'bi-envelope' : 'bi-envelope-open';
        const toggleTitle = isUnread ? 'Mark as read' : 'Mark as unread';

        return '<div class="notif-item ' + (isUnread ? 'is-unread' : 'is-read') + '" data-notif-id="' + n.id + '" data-is-read="' + (n.is_read ? '1' : '0') + '">'
             +   '<a href="' + _esc(n.url) + '" data-target-url="' + _esc(targetUrl) + '" class="notif-item-link">'
             +     '<div class="notif-item-icon" style="background: ' + bg + ';">'
             +       '<i class="bi ' + icon + ' ' + colorClass + '"></i>'
             +     '</div>'
             +     '<div class="notif-item-content">'
             +       '<div class="notif-item-header">'
             +         '<span class="notif-item-title">' + _esc(n.title) + '</span>'
             +         '<span class="notif-item-time"><i class="bi bi-clock me-1"></i>' + _esc(n.time_ago) + '</span>'
             +       '</div>'
             +       '<p class="notif-item-message">' + _esc(n.message) + '</p>'
             +     '</div>'
             +   '</a>'
             +   '<div class="notif-item-actions">'
             +     '<button type="button" class="btn-notif-action btn-notif-toggle" title="' + toggleTitle + '" data-action="toggle-read">'
             +       '<i class="bi ' + toggleIcon + '"></i>'
             +     '</button>'
             +     '<button type="button" class="btn-notif-action btn-notif-delete text-danger" title="Delete notification" data-action="delete">'
             +       '<i class="bi bi-x-lg"></i>'
             +     '</button>'
             +   '</div>'
             + '</div>';
    }

    function applyFilter(filter) {
        currentFilter = filter;
        const tabAll = document.getElementById('notifTabAll');
        const tabUnread = document.getElementById('notifTabUnread');
        if (tabAll && tabUnread) {
            if (filter === 'unread') {
                tabUnread.classList.add('active');
                tabAll.classList.remove('active');
            } else {
                tabAll.classList.add('active');
                tabUnread.classList.remove('active');
            }
        }

        const listContainer = notifDropdown?.querySelector('[data-notif-list]');
        if (!listContainer) return;

        const items = listContainer.querySelectorAll('.notif-item');
        let visibleCount = 0;

        items.forEach(function(item) {
            const isRead = item.getAttribute('data-is-read') === '1';
            if (filter === 'unread') {
                if (!isRead) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            } else {
                item.style.display = '';
                visibleCount++;
            }
        });

        let emptyState = listContainer.querySelector('.notif-empty-state');
        if (visibleCount === 0) {
            if (!emptyState) {
                emptyState = document.createElement('div');
                emptyState.className = 'notif-empty-state';
                listContainer.appendChild(emptyState);
            }
            if (filter === 'unread') {
                emptyState.innerHTML = '<div class="notif-empty-icon text-success"><i class="bi bi-check2-circle"></i></div>'
                                     + '<div class="notif-empty-title">No unread notifications</div>'
                                     + '<p class="notif-empty-subtitle">You\'re all caught up! Switch to "All" for previous history.</p>';
            } else {
                emptyState.innerHTML = '<div class="notif-empty-icon"><i class="bi bi-bell-slash"></i></div>'
                                     + '<div class="notif-empty-title">No notifications yet</div>'
                                     + '<p class="notif-empty-subtitle">You\'re completely caught up!</p>';
            }
            emptyState.style.display = '';
        } else if (emptyState) {
            emptyState.style.display = 'none';
        }
    }

    async function handleMarkAllRead() {
        try {
            const res = await fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            if (res.ok) {
                const items = notifDropdown.querySelectorAll('.notif-item');
                items.forEach(function(item) {
                    item.classList.remove('is-unread');
                    item.classList.add('is-read');
                    item.setAttribute('data-is-read', '1');
                    const toggle = item.querySelector('[data-action="toggle-read"]');
                    if (toggle) {
                        toggle.title = 'Mark as unread';
                        toggle.innerHTML = '<i class="bi bi-envelope"></i>';
                    }
                });

                const total = items.length;
                updateNotifCounters(0, total);
                applyFilter(currentFilter);
            }
        } catch (err) {
            console.error('Error marking all notifications as read:', err);
        }
    }

    async function handleClearAll() {
        try {
            const res = await fetch('/notifications/clear-all', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            if (res.ok) {
                const listContainer = notifDropdown.querySelector('[data-notif-list]');
                if (listContainer) {
                    const items = listContainer.querySelectorAll('.notif-item');
                    items.forEach(function(item) { item.classList.add('removing'); });
                    setTimeout(function() {
                        listContainer.innerHTML = '';
                        updateNotifCounters(0, 0);
                        applyFilter(currentFilter);
                    }, 250);
                }
            }
        } catch (err) {
            console.error('Error clearing notifications:', err);
        }
    }

    async function handleToggleRead(item, toggleBtn) {
        const id = item.getAttribute('data-notif-id');
        if (!id) return;
        try {
            const res = await fetch('/notifications/' + id + '/toggle-read', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            if (res.ok) {
                const data = await res.json();
                const isRead = data.is_read;
                item.setAttribute('data-is-read', isRead ? '1' : '0');
                if (isRead) {
                    item.classList.remove('is-unread');
                    item.classList.add('is-read');
                    toggleBtn.title = 'Mark as unread';
                    toggleBtn.innerHTML = '<i class="bi bi-envelope"></i>';
                } else {
                    item.classList.add('is-unread');
                    item.classList.remove('is-read');
                    toggleBtn.title = 'Mark as read';
                    toggleBtn.innerHTML = '<i class="bi bi-envelope-open"></i>';
                }

                const total = notifDropdown.querySelectorAll('.notif-item').length;
                updateNotifCounters(data.unread, total);
                applyFilter(currentFilter);
            }
        } catch (err) {
            console.error('Error toggling notification read status:', err);
        }
    }

    async function handleDeleteItem(item) {
        const id = item.getAttribute('data-notif-id');
        if (!id) return;
        try {
            const res = await fetch('/notifications/' + id, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            if (res.ok) {
                const data = await res.json();
                item.classList.add('removing');
                setTimeout(function() {
                    item.remove();
                    const total = notifDropdown.querySelectorAll('.notif-item').length;
                    updateNotifCounters(data.unread, total);
                    applyFilter(currentFilter);
                }, 250);
            }
        } catch (err) {
            console.error('Error deleting notification:', err);
        }
    }

    if (notifDropdown) {
        notifDropdown.addEventListener('click', function(e) {
            // Filter tab click
            const tabBtn = e.target.closest('[data-notif-filter]');
            if (tabBtn) {
                e.preventDefault();
                e.stopPropagation();
                const filter = tabBtn.getAttribute('data-notif-filter');
                applyFilter(filter);
                return;
            }

            // Mark all read button
            const markAll = e.target.closest('#btnMarkAllRead');
            if (markAll) {
                e.preventDefault();
                e.stopPropagation();
                handleMarkAllRead();
                return;
            }

            // Clear all button
            const clearAll = e.target.closest('#btnClearAllNotifs');
            if (clearAll) {
                e.preventDefault();
                e.stopPropagation();
                handleClearAll();
                return;
            }

            // Quick action: Toggle read
            const toggleBtn = e.target.closest('[data-action="toggle-read"]');
            if (toggleBtn) {
                e.preventDefault();
                e.stopPropagation();
                const item = toggleBtn.closest('.notif-item');
                if (item) handleToggleRead(item, toggleBtn);
                return;
            }

            // Quick action: Delete
            const deleteBtn = e.target.closest('[data-action="delete"]');
            if (deleteBtn) {
                e.preventDefault();
                e.stopPropagation();
                const item = deleteBtn.closest('.notif-item');
                if (item) handleDeleteItem(item);
                return;
            }

            // Notification item click (navigation)
            const link = e.target.closest('.notif-item-link');
            if (link) {
                const item = link.closest('.notif-item');
                const isUnread = item && item.getAttribute('data-is-read') === '0';
                const notifId = item?.getAttribute('data-notif-id');
                const targetUrl = link.getAttribute('data-target-url') || link.getAttribute('href');

                if (isUnread && notifId) {
                    e.preventDefault();
                    fetch('/notifications/' + notifId + '/read', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        }
                    }).then(function(r) { return r.json(); }).then(function(data) {
                        window.location.href = data.target_url || targetUrl;
                    }).catch(function() {
                        window.location.href = targetUrl;
                    });
                }
            }
        });
    }

    function determineToastType(title, message) {
        const text = ((title || '') + ' ' + (message || '')).toLowerCase();
        if (text.includes('reject') || text.includes('decline') || text.includes('declined') || text.includes('cancel') || text.includes('overdue') || text.includes('error') || text.includes('fail')) return 'error';
        if (text.includes('approve') || text.includes('confirm') || text.includes('fulfill') || text.includes('return') || text.includes('success')) return 'success';
        if (text.includes('pending') || text.includes('request') || text.includes('warning') || text.includes('alert')) return 'warning';
        return 'info';
    }

    function determineTargetUrl(title, message) {
        const text = ((title || '') + ' ' + (message || '')).toLowerCase();
        if (text.includes('issuance') || text.includes('consumable') || text.includes('receipt')) return '/borrower/issuance';
        if (text.includes('decline') || text.includes('reject')) return '/borrower/history';
        if (text.includes('return') || text.includes('borrowed') || text.includes('due')) return '/borrower/returns';
        if (text.includes('request')) return '/borrower/requests';
        return null;
    }

    async function pollNotifications() {
        if (isPolling) return;
        isPolling = true;

        var pollBase = notifBtn.getAttribute('data-poll-url') || window._notifPollUrl || '/notifications/poll';
        var pollUrl = new URL(pollBase, window.location.origin);
        if (lastSeenNotifId !== null) {
            pollUrl.searchParams.set('since_id', lastSeenNotifId);
        }

        try {
            const res = await fetch(pollUrl.toString(), {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            if (!res.ok) return;
            const data = await res.json();

            // First run: set watermark so we don't alert old notifications
            if (lastSeenNotifId === null) {
                lastSeenNotifId = Number(data.max_id || 0);
            } else if (Array.isArray(data.new_notifications) && data.new_notifications.length > 0) {
                data.new_notifications.forEach(function(n) {
                    const toastType = determineToastType(n.title, n.message);
                    const toastUrl = n.target_url || determineTargetUrl(n.title, n.message);
                    showToast(n.title, n.message, toastType, { sound: true, url: toastUrl });
                });
                if (data.max_id) {
                    lastSeenNotifId = Math.max(lastSeenNotifId, Number(data.max_id));
                }
            }

            // Update counters across dropdown and bell badge
            const totalItems = Array.isArray(data.notifications) ? data.notifications.length : 0;
            updateNotifCounters(data.unread, totalItems);

            // Update sidebar & bottom nav badges
            if (data.counts) {
                if (data.counts.active_loans !== undefined) {
                    updateBadge('borrowerSidebarReturnsBadge', data.counts.active_loans);
                    updateBnCount('bnReturnsCount', data.counts.active_loans);
                }
                if (data.counts.pending_confirmations !== undefined) {
                    updateBadge('borrowerSidebarIssuanceBadge', data.counts.pending_confirmations);
                    updateBnCount('bnIssuanceCount', data.counts.pending_confirmations);
                }
                if (data.counts.pending_borrow_requests !== undefined) {
                    updateBadge('borrowerSidebarRequestsBadge', data.counts.pending_borrow_requests);
                    updateBnCount('bnRequestsCount', data.counts.pending_borrow_requests);
                }
            }

            // Update dropdown list if present
            if (notifDropdown) {
                const listWrapper = notifDropdown.querySelector('[data-notif-list]');
                if (listWrapper && Array.isArray(data.notifications)) {
                    if (data.notifications.length === 0) {
                        listWrapper.innerHTML = '<div class="notif-empty-state">'
                                             +   '<div class="notif-empty-icon"><i class="bi bi-bell-slash"></i></div>'
                                             +   '<div class="notif-empty-title">No notifications yet</div>'
                                             +   '<p class="notif-empty-subtitle">You\'re completely caught up!</p>'
                                             + '</div>';
                    } else {
                        const existingItems = listWrapper.querySelectorAll('.notif-item');
                        const currentSignature = Array.from(existingItems).map(function(el) {
                            return el.getAttribute('data-notif-id') + ':' + el.getAttribute('data-is-read');
                        }).join(',');
                        const newSignature = data.notifications.map(function(n) {
                            return n.id + ':' + (n.is_read ? '1' : '0');
                        }).join(',');

                        if (currentSignature !== newSignature) {
                            let html = '';
                            data.notifications.forEach(function(n) {
                                html += renderNotificationItem(n);
                            });
                            listWrapper.innerHTML = html;
                            applyFilter(currentFilter);
                        }
                    }
                }
            }

            // Dispatch global event so current view updates dynamically
            window.dispatchEvent(new CustomEvent('baycis:realtime-update', { detail: data }));

        } catch (e) {
            /* silently ignore polling errors */
        } finally {
            isPolling = false;
        }
    }

    function setPollingInterval(ms) {
        if (pollTimer) clearInterval(pollTimer);
        currentInterval = ms;
        pollTimer = setInterval(pollNotifications, ms);
    }

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            setPollingInterval(HIDDEN_POLL_INTERVAL);
        } else {
            pollNotifications();
            setPollingInterval(ACTIVE_POLL_INTERVAL);
        }
    });

    if (!window._skipNotifPoll) {
        setPollingInterval(document.hidden ? HIDDEN_POLL_INTERVAL : ACTIVE_POLL_INTERVAL);
        setTimeout(pollNotifications, 500);
    }
})();

// ── O F F L I N E   B A N N E R ──────────────────────
(function() {
    function showOfflineBanner() {
        if (document.getElementById('offlineBanner')) return;
        var banner = document.createElement('div');
        banner.id = 'offlineBanner';
        banner.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:99999;background:#f59e0b;color:#000;padding:10px 16px;font-size:13px;font-weight:600;display:flex;align-items:center;justify-content:space-between;box-shadow:0 4px 12px rgba(0,0,0,0.1);animation:slideDown 0.3s ease;';
        banner.innerHTML = '<span><i class="bi bi-wifi-off me-2"></i>You are offline. Some features may be unavailable until connection returns.</span><button onclick="this.parentElement.remove()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#000;padding:0 4px;">&times;</button>';
        document.body.prepend(banner);
    }
    function hideOfflineBanner() {
        var b = document.getElementById('offlineBanner');
        if (b) b.remove();
    }
    window.addEventListener('offline', showOfflineBanner);
    window.addEventListener('online', function() { hideOfflineBanner(); });
    if (!navigator.onLine) showOfflineBanner();
})();
