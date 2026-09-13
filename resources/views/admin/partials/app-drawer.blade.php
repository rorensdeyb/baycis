{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     AppDrawer â€” system-wide right slide-over panel
     Replaces centered modals for information/detail/action flows.
     Opt-out per modal: add  data-centered  to the .modal element.
     Row-overview opt-in per table: <table data-drawer-rows>
     â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div id="appDrawerBackdrop" style="position:fixed;inset:0;background:rgba(16,24,40,.45);z-index:1090;display:none;"></div>
<aside id="appDrawer" role="dialog" aria-modal="true"
       style="position:fixed;top:0;right:0;height:100vh;width:min(600px,100vw);background:var(--bg-surface);
              z-index:1091;box-shadow:-14px 0 44px rgba(16,24,40,.18);border-left:1px solid var(--border-color);
              transform:translateX(102%);transition:transform .26s cubic-bezier(.32,.72,.28,1);
              display:flex;flex-direction:column;">
    <header style="display:flex;align-items:flex-start;gap:12px;padding:18px 20px 14px;border-bottom:1px solid var(--border-color);flex-shrink:0;">
        <div style="flex:1 1 auto;min-width:0;">
            <div id="adOverline" style="font-size:10.5px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:var(--text-secondary,#6b7280);margin-bottom:3px;">Details</div>
            <h5 id="adTitle" style="margin:0;font-size:17px;font-weight:700;color:var(--text-primary);line-height:1.3;word-wrap:break-word;">â€”</h5>
        </div>
        <button type="button" id="adCloseBtn" aria-label="Close panel"
                style="flex-shrink:0;background:transparent;border:none;color:var(--text-secondary);width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px;">
            <i class="bi bi-x-lg"></i>
        </button>
    </header>
    <div id="adBody" style="flex:1 1 auto;overflow-y:auto;padding:20px;-webkit-overflow-scrolling:touch;"></div>
    <footer id="adFooter" style="display:none;gap:10px;padding:14px 20px calc(14px + env(safe-area-inset-bottom,0px));border-top:1px solid var(--border-color);flex-shrink:0;background:var(--bg-surface);"></footer>
</aside>

<style>
    #appDrawerBackdrop{backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);opacity:0;transition:opacity .2s ease;}
    #appDrawerBackdrop.open{display:block;opacity:1;}
    #appDrawer button:focus-visible,#appDrawer a:focus-visible{outline:2px solid var(--accent-blue, #1b3550);outline-offset:2px;}
    #adCloseBtn:hover{background:var(--bg-surface-hover,var(--bg-main));color:var(--text-primary);}
    #adCloseBtn:active{transform:scale(.94);}
    /* Content moved from legacy modals keeps its padding rhythm tidy */
    #adBody > form:first-child { margin:0; }
    #adBody .modal-body, #adBody .modal-footer { padding:0 !important; border:0 !important; }
    @media (prefers-reduced-motion: reduce){ #appDrawer{transition:none;} }
    /* Property tag replica (drawer asset overview) — mirrors create/edit preview styles */
    #adBody .tag-container-preview { width:100%; background:#ffffff; border:2px solid #000; border-collapse:separate; border-spacing:0; color:#000000; font-family:Arial, sans-serif; box-shadow:0 4px 6px -1px rgba(0,0,0,.1); overflow:hidden; }
    #adBody .tag-container-preview .tag-header { text-align:center; font-weight:bold; padding:8px 6px; border-bottom:2px solid #000; background-color:#FFFF00; color:#000; font-size:13.5px; letter-spacing:.02em; }
    #adBody .tag-container-preview .tag-body { display:flex; align-items:stretch; height:235px; }
    #adBody .tag-container-preview .left-panel { width:35%; border-right:2px solid #000; display:flex; flex-direction:column; align-items:center; justify-content:space-between; gap:10px; padding:12px 6px 10px; overflow:hidden; }
    #adBody .tag-container-preview .left-panel img { width:82%; height:auto; flex-shrink:0; }
    #adBody .tag-container-preview .right-panel { width:65%; display:flex; }
    #adBody .tag-container-preview table.preview-table { width:100%; border-collapse:collapse; margin:0; height:100%; table-layout:fixed; }
    #adBody .tag-container-preview table.preview-table td { border-bottom:1px solid #000; padding:3px 9px; font-size:10.5px; line-height:1.25; color:#000; background:transparent; vertical-align:middle; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    #adBody .tag-container-preview table.preview-table td:first-child { border-right:1px solid #000; width:42%; font-weight:600; }
    #adBody .tag-container-preview table.preview-table tr:last-child td { border-bottom:none; }
    #adBody .tag-container-preview table.preview-table td strong { font-size:11px; letter-spacing:.02em; }
    #adBody .tag-container-preview .barcode-placeholder { width:100%; }
    #adBody .tag-container-preview .tag-footer { text-align:center; font-size:9.5px; padding:5px 4px; border-top:2px solid #000; font-weight:bold; }
    
    /* ── AppDrawer Core Viewport Sizing & Footer Pinning ── */
    #appDrawer {
        position: fixed !important;
        top: 0 !important;
        bottom: 0 !important;
        right: 0 !important;
        height: 100vh !important;
        height: 100dvh !important;
        max-height: 100vh !important;
        max-height: 100dvh !important;
        width: min(600px, 100vw) !important;
        background: var(--bg-surface) !important;
        z-index: 1091 !important;
        box-shadow: -14px 0 44px rgba(16, 24, 40, .18) !important;
        border-left: 1px solid var(--border-color) !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }
    #adBody {
        flex: 1 1 0% !important;
        min-height: 0 !important;
        max-height: 100% !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        padding: 20px !important;
        -webkit-overflow-scrolling: touch;
        box-sizing: border-box !important;
    }
    #adFooter {
        flex: 0 0 auto !important;
        flex-shrink: 0 !important;
        flex-grow: 0 !important;
        width: 100% !important;
        min-height: 60px !important;
        box-sizing: border-box !important;
        padding: 14px 20px calc(14px + env(safe-area-inset-bottom, 0px)) !important;
        border-top: 1px solid var(--border-color) !important;
        background: var(--bg-surface) !important;
        position: relative !important;
        z-index: 1092 !important;
        margin-top: auto !important;
    }
    #adFooter .modal-footer {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        background: transparent !important;
        gap: 12px !important;
        flex-wrap: nowrap !important;
    }
    #adFooter .modal-footer > * {
        margin: 0 !important;
    }
    #adFooter button, #adFooter .btn {
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: auto !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        z-index: 1093 !important;
    }
</style>

<script>
(function () {
    'use strict';

    const backdrop = document.getElementById('appDrawerBackdrop');
    const drawer   = document.getElementById('appDrawer');
    const bodyEl   = document.getElementById('adBody');
    const footEl   = document.getElementById('adFooter');
    const titleEl  = document.getElementById('adTitle');
    const overEl   = document.getElementById('adOverline');

    // Modals that intentionally stay centered (credential / destructive confirms)
    const CENTERED_IDS = [
        'logoutModal', 'passwordResetModal', 'privilegePinModal',
        'lifecycleModal', 'deleteUserModal', 'cancelConfirmModal',
        'deleteConfirmModal', 'approveModal', 'rejectModal',
        'cancelBorrowModal', 'scannerModal'
    ];

    let isDrawerOpen = false;
    let hostEl = null;          // originating .modal element
    let bodyPlaceholder = null;
    let footPlaceholder = null;
    let lastFocused = null;
    let staticBackdrop = false;

    function csrfToken() {
        const m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    function isOpen() { return isDrawerOpen; }

    function openHost(el) {
        if (!el) return;
        if (hostEl && hostEl !== el) {
            close();
        }
        hostEl = el;
        isDrawerOpen = true;
        lastFocused = document.activeElement;
        staticBackdrop = el.getAttribute('data-bs-backdrop') === 'static';

        // ── Title ──
        const t = el.querySelector('.modal-title');
        titleEl.textContent = (t ? t.textContent : '').trim() || 'Details';

        // ── Width tier from the dialog classes ──
        const dlg = el.querySelector('.modal-dialog');
        drawer.style.width = dlg && dlg.classList.contains('modal-lg') ? 'min(760px,100vw)'
                           : dlg && dlg.classList.contains('modal-sm') ? 'min(430px,100vw)'
                           : 'min(600px,100vw)';

        // ── MOVE body (keeps listeners / ids intact via DOM placeholder) ──
        const body = el.querySelector('.modal-body');
        if (body && body.parentNode) {
            bodyPlaceholder = document.createComment('ad-body-placeholder');
            body.parentNode.insertBefore(bodyPlaceholder, body);
            bodyEl.appendChild(body);
        }

        // ── MOVE footer container intact (preserves all button nodes and listeners via DOM placeholder) ──
        const foot = el.querySelector('.modal-footer');
        if (foot && foot.parentNode) {
            footPlaceholder = document.createComment('ad-foot-placeholder');
            foot.parentNode.insertBefore(footPlaceholder, foot);
            footEl.appendChild(foot);
            footEl.style.setProperty('display', 'flex', 'important');
        } else {
            footEl.style.setProperty('display', 'none', 'important');
        }

        // Overline label from context (page-defined hook)
        overEl.textContent = window.AppDrawerOverline || 'Details';

        backdrop.style.display = 'block';
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(function () {
            drawer.style.transform = 'translateX(0)';
            backdrop.classList.add('open');
        });

        setTimeout(function () {
            const f = bodyEl.querySelector('input:not([type=hidden]):not([disabled]), select, textarea');
            if (f) f.focus();
        }, 280);
    }

    function close() {
        if (!isDrawerOpen && !hostEl) return;
        isDrawerOpen = false;
        document.querySelectorAll('.drawer-active').forEach(function (r) { r.classList.remove('drawer-active'); });
        drawer.style.transform = 'translateX(102%)';
        backdrop.classList.remove('open');
        setTimeout(function () {
            if (!isDrawerOpen) backdrop.style.display = 'none';
        }, 240);

        // Return moved nodes intact so the original modal stays 100% functional
        if (hostEl) {
            const modalContent = hostEl.querySelector('.modal-content') || hostEl;

            if (bodyPlaceholder && bodyPlaceholder.parentNode) {
                while (bodyEl.firstChild) {
                    bodyPlaceholder.parentNode.insertBefore(bodyEl.firstChild, bodyPlaceholder);
                }
                bodyPlaceholder.remove();
                bodyPlaceholder = null;
            } else {
                while (bodyEl.firstChild) {
                    modalContent.appendChild(bodyEl.firstChild);
                }
            }

            if (footPlaceholder && footPlaceholder.parentNode) {
                while (footEl.firstChild) {
                    footPlaceholder.parentNode.insertBefore(footEl.firstChild, footPlaceholder);
                }
                footPlaceholder.remove();
                footPlaceholder = null;
            } else {
                while (footEl.firstChild) {
                    modalContent.appendChild(footEl.firstChild);
                }
            }
        }
        bodyEl.innerHTML = ''; footEl.innerHTML = '';
        footEl.style.setProperty('display', 'none', 'important');
        document.body.style.overflow = '';
        if (lastFocused && lastFocused.focus) {
            try { lastFocused.focus(); } catch (e) {}
        }
        hostEl = null;
    }

    const AppDrawer = {
        open: openHost,
        close: close,
        isOpen: isOpen,
        /** Open with explicit content (for building panels in-page). */
        show: function (opts) {
            opts = opts || {};
            if (hostEl) close();
            isDrawerOpen = true;
            titleEl.textContent = opts.title || 'Details';
            if (opts.overline !== undefined) overEl.textContent = opts.overline;
            bodyEl.innerHTML = '';
            footEl.innerHTML = ''; footEl.style.setProperty('display', 'none', 'important'); hostEl = null;
            if (typeof opts.body === 'string') bodyEl.innerHTML = opts.body;
            else if (opts.body instanceof HTMLElement) { bodyEl.appendChild(opts.body); }
            (opts.footer || []).forEach(function (b) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = b.class || 'btn btn-light';
                btn.style.borderRadius = '8px';
                btn.innerHTML = b.label || b.text || '';
                btn.addEventListener('click', function () { if (b.onClick) b.onClick(AppDrawer); });
                footEl.appendChild(btn);
            });
            if ((opts.footer || []).length) footEl.style.setProperty('display', 'flex', 'important');
            backdrop.style.display = 'block';
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(function () {
                drawer.style.transform = 'translateX(0)';
                backdrop.classList.add('open');
            });
            return AppDrawer;
        },
        setBody: function (html) { bodyEl.innerHTML = html; },
    };
    window.AppDrawer = AppDrawer;

    // â”€â”€ Close affordances â”€â”€
    document.getElementById('adCloseBtn').addEventListener('click', close);
    backdrop.addEventListener('click', function () { if (!staticBackdrop) close(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen() && !staticBackdrop) close();
    });

    // â”€â”€ Modal Dismissers inside AppDrawer â”€â”€
    document.addEventListener('click', function (e) {
        const dismisser = e.target.closest('#appDrawer [data-bs-dismiss="modal"]');
        if (dismisser) {
            e.preventDefault();
            close();
        }
    });

    // â”€â”€ Universal conversion: intercept every Bootstrap modal opening â”€â”€
    // 1) Declarative triggers (data-bs-toggle="modal")
    document.addEventListener('click', function (e) {
        const trig = e.target.closest('[data-bs-toggle="modal"][data-bs-target]');
        if (!trig) return;
        const target = document.querySelector(trig.getAttribute('data-bs-target'));
        if (!target) return;

        // If target is centered modal and AppDrawer is open, close AppDrawer first so modal is not blocked
        if (target.hasAttribute('data-centered') || CENTERED_IDS.includes(target.id)) {
            if (isOpen()) {
                close();
            }
            return;
        }

        e.preventDefault(); e.stopImmediatePropagation();
        openHost(target);
    }, true);

    // 2) Programmatic triggers â€” patch Modal.prototype.show
    function patchWhenReady() {
        if (!window.bootstrap || !window.bootstrap.Modal) { setTimeout(patchWhenReady, 60); return; }
        const proto = window.bootstrap.Modal.prototype;
        const origShow = proto.show;
        proto.show = function () {
            const el = this._element;
            if (!el || el.hasAttribute('data-centered') || CENTERED_IDS.includes(el.id)) {
                if (isOpen()) close();
                return origShow.call(this);
            }
            return openHost(el);
        };
        const origHide = proto.hide;
        proto.hide = function () {
            const el = this._element;
            if (el && hostEl === el) return close();
            return origHide.call(this);
        };
    }
    patchWhenReady();

    // â”€â”€ Row-overview drawers: opt-in via <table data-drawer-rows> â”€â”€
    function bindRows(scope) {
        (scope || document).querySelectorAll('table[data-drawer-rows] tbody tr').forEach(function (tr) {
            if (tr.dataset.drawerBound) return;
            tr.dataset.drawerBound = '1';
            tr.addEventListener('click', function (e) {
                if (e.target.closest('input,button,a,select,textarea,label,form,.dropdown-menu')) return;
                openRowInfo(tr);
            });
            tr.style.cursor = 'pointer';
        });
    }
    function openRowInfo(tr) {
        document.querySelectorAll('.drawer-active').forEach(function (r) { r.classList.remove('drawer-active'); });
        tr.classList.add('drawer-active');
        const table = tr.closest('table[data-drawer-rows]');
        const rendererName = table?.dataset.drawerRenderer;
        if (rendererName && typeof window[rendererName] === 'function') {
            window[rendererName](tr); // page-specific rich drawer
            return;
        }
                const heads = Array.from(table.querySelectorAll('thead th')).map(function (th) { return th.textContent.trim(); });
        const cells = Array.from(tr.children);
        let html = '<dl style="margin:0;display:grid;grid-template-columns:1fr;gap:14px;">';
        cells.forEach(function (td, i) {
            if (i >= heads.length) return;
            const label = heads[i];
            if (/^action/i.test(label) || label === '') return;
            const text = td.textContent.replace(/\s+/g, ' ').trim();
            html += '<div><dt style="font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-secondary,#6b7280);margin-bottom:3px;">' 
                 + escapeHtmlText(label) + '</dt><dd style="margin:0;font-size:13.5px;font-weight:600;color:var(--text-primary);">' 
                 + escapeHtmlText(text || 'â€”') + '</dd></div>';
        });
        html += '</dl>';
        const titleCell = cells.find(function (c, i) { return i > 0 && c.textContent.trim(); });
        AppDrawer.show({ overline: 'Record overview', title: titleCell ? titleCell.textContent.replace(/\s+/g,' ').trim().slice(0,80) : 'Details', body: html,
            footer: [{ label: 'Close', class: 'btn btn-light', onClick: function (d) { d.close(); } }] });
    }
    function escapeHtmlText(t) {
        const d = document.createElement('div'); d.textContent = t; return d.innerHTML;
    }
    document.addEventListener('DOMContentLoaded', function () { bindRows(document); });
    window.AppDrawerBindRows = bindRows;
})();
</script>

