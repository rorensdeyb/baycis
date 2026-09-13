# BayCIS — Issuance Tab & Borrower Workflow Master Plan

> **Last Updated:** August 27, 2026  
> **Focus:** Issuance Tab (Admin/Custodian) + Borrower Issuance Workflow  
> **Stack:** Laravel 13, MySQL 8, Bootstrap 5.3, vanilla ES6+, Blade

---

## 🎯 Vision

A professional, intuitive **Issuance Workspace** where custodians manage consumable stock, fulfill requests, issue kits in bulk, and monitor consumption — while borrowers enjoy a frictionless self-service flow: select item → quantity/purpose → PIN confirmation. All mutations flow through a single `IssuanceService` with row-locked transactions, audit trails, and notifications.

---

## 🔄 Issuance Workflow — End-to-End Flow

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                        ADMIN / CUSTODIAN SIDE                               │
│                                                                              │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐              │
│  │  STOCKS  │───▶│ REQUESTS │───▶│ CONFIRM  │───▶│ HISTORY  │              │
│  │  TAB     │    │  TAB     │    │  TAB     │    │  TAB     │              │
│  │          │    │          │    │          │    │          │              │
│  │ Add/Edit │    │ Borrower │    │ Awaiting │    │ Filters  │              │
│  │ Delete   │    │ requests │    │ borrower │    │ CSV exp. │              │
│  │ Replenish│    │ Fulfill  │    │ receipt  │    │ Search   │              │
│  │ Search   │    │ Cancel   │    │ Cancel   │    │          │              │
│  │ CSV imp. │    │          │    │          │    │          │              │
│  │ Issue    │    │          │    │          │    │          │              │
│  └──────────┘    └──────────┘    └──────────┘    └──────────┘              │
│                                                                              │
│  ── Also: Bulk Kit Issue (multi-line), Quick Replenish, Stock History ──    │
│                                                                              │
└──────────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────────────────┐
│                         BORROWER SIDE                                       │
│                                                                              │
│  ┌──────────────────┐    ┌──────────────┐    ┌──────────────┐              │
│  │  ITEM SELECTION  │───▶│ QTY & PURPOSE│───▶│ PIN CONFIRM  │              │
│  │                  │    │              │    │              │              │
│  │ • Card grid      │    │ • Quantity   │    │ • 4-digit PIN│              │
│  │ • Favorites      │    │ • Purpose    │    │ • POST req   │              │
│  │ • Stock badge    │    │   chips      │    │ • Success    │              │
│  │ • Low stock warn │    │ • Notes      │    │   toast      │              │
│  └──────────────────┘    └──────────────┘    └──────────────┘              │
│                                                                              │
│  ── Also: Pending Requests (cancel), Confirm Receipt, History ──           │
│                                                                              │
└──────────────────────────────────────────────────────────────────────────────┘
```

### Issuance Lifecycle (State Machine)

```
                    ┌─────────────────────────────────────────────┐
                    │                                             │
  BORROWER REQUEST  │   ADMIN DIRECT ISSUE                       │
  ──────────────▶  │   ──────────────▶                           │
  pending_issue     │   issued                                   │
                    │      │                                      │
                    │      ├─────▶ confirmed (borrower confirms)  │
                    │      │                                      │
                    │      └─────▶ cancelled (admin cancels,      │
                    │                stock restored)              │
                    │                                             │
  pending_issue ───▶ cancelled (borrower cancels, no restore)    │
                    └─────────────────────────────────────────────┘

  BULK KIT ISSUE:
    Each line → issued (atomic or partial_ok mode)
    group_id links all lines in a kit
```

---

## 🧭 Current State (Aug 27, 2026)

| Area | Status | Notes |
|------|--------|-------|
| **Core Mutations** | ✅ Done | `IssuanceService` centralizes all mutations with row locks, audits, notifications |
| **Stock Lifecycle** | ✅ Done | Add/Edit/Delete/Replenish + min_stock thresholds |
| **Bulk Kit Issuance** | ✅ Done | Multi-line kits, atomic or partial_ok, group_id |
| **Monitoring** | ✅ Done | KPI pills, 30-day trend chart, running-balance history |
| **History Workbench** | ✅ Done | Filters, date range, CSV export, per-page |
| **Low-Stock Alerts** | ✅ Done | Daily deduped notifications + sidebar badge |
| **Borrower Portal** | ⚠️ Partial | Item cards, favorites, purpose chips, PIN gate — wizard UI incomplete |
| **Reports** | ⚠️ Partial | Reports engine exists but has duplicate condition bug (see ISS-6a) |
| **CSV Import** | ✅ Done | Endpoint + modal, minor JS inconsistency |
| **Data Hygiene** | ⚠️ Partial | Delete guard done; dedupe tooling pending |

---

## 🐛 Critical Bugs Found (Aug 27, 2026)

### BUG-1: Admin Issuance Blade — Broken HTML Nesting (stocks tab)
**File:** `resources/views/admin/issuance.blade.php`  
**Severity:** High — breaks stocks tab layout  
**Location:** After the stocks toolbar `</div>` (around line 157)

**Problem:** There is an extra `</div>` after the toolbar that prematurely closes both `#stocks-panel` and the `panel-card`. The stocks `<table>` then renders **outside** the tab pane. This means:
- The stocks table is not inside the `.tab-content` container
- Tab switching may leave the table visible when it shouldn't be
- The table may not inherit the correct panel-card styling

**Current (broken):**
```html
<div class="tab-pane fade show active" id="stocks-panel">
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <!-- toolbar buttons -->
    </div>
    </div>  ← EXTRA CLOSING DIV (closes stocks-panel prematurely)
</div>     ← closes panel-card early

<table class="admin-table w-100" id="stocksTable">  ← ORPHANED
```

**Fix:** Remove the extra `</div>`. The toolbar div should close, then the table should be inside stocks-panel.

---

### BUG-2: Reports Blade — Duplicate `consumable_issuance` Condition
**File:** `resources/views/admin/reports.blade.php`  
**Severity:** Medium — second block is unreachable  
**Location:** Two `@elseif($reportType === 'consumable_issuance')` blocks

**Problem:** There are two `@elseif($reportType === 'consumable_issuance')` blocks. The first renders a low-stock-style consumable table (likely meant to be `consumable_low_stock`). The second renders the actual issuance log. The second block is **unreachable** because Blade evaluates conditions top-down.

**Fix:** Rename the first block's condition to `'consumable_low_stock'` (or a unique type) and update the report dropdown accordingly. Or merge them into a single view.

---

### BUG-3: Borrower Cancellation Bypasses Service Layer
**File:** `app/Http/Controllers/BorrowerController.php` → `cancelConsumableRequest()`  
**Severity:** Low — works correctly but violates architectural principle  
**Problem:** The borrower cancel action manually updates status and creates an audit log instead of routing through `IssuanceService::cancel()`. While functionally correct (pending items have no stock to restore), it:
- Bypasses the single-source-of-truth service layer
- Creates audit logs with a different format than `IssuanceService::audit()`
- Doesn't notify staff via `IssuanceService::notifyStaffExcept()`

**Fix:** Route through `IssuanceService::cancel()` which already handles the `pending_issue` case gracefully.

---

## 🗺️ Roadmap — Remaining Work

| Priority | ID | Item | Effort | Status |
|---|---|---|---|---|
| **P0** | **BUG-1** | Fix admin issuance blade JavaScript syntax error (broken `{` and `});` in script block) | 0.5 hr | ✅ Fixed - Removed stray `{` and unbalanced `});` on lines 1015-1018, added `flex-wrap` and `overflow-x:auto` to tracker
| **P0** | **BUG-2** | Fix reports blade duplicate condition | 0.5 hr | ⬜ Not started |
| **P1** | **BUG-3** | Route borrower cancel through IssuanceService | 0.5 hr | ⬜ Not started |
| **P1** | **ISS-8b** | Borrower 3-step wizard (item → qty/purpose → PIN) | 3 hrs | ⚠️ Partial |
| **P1** | **ISS-2b** | Camera scan-to-issue (mobile camera prefill) | 3 hrs | ⬜ Not started |
| **P2** | **ISS-6a** | Fix consumable_low_stock report type in reports blade | 1 hr | ⬜ Not started |
| **P3** | **ISS-10** | Data hygiene — dedupe tooling, orphan prevention | 2 hrs | ⚠️ Partial |
| **P3** | **ISS-11** | Extract admin issuance inline JS to external file | 2 hrs | ⬜ Not started |

---

## 🔧 ISSUE DETAILS — What Remains

### BUG-1: Admin Issuance Blade HTML Fix (P0)
**Goal:** Fix broken nesting in `resources/views/admin/issuance.blade.php`

The stocks tab (`#stocks-panel`) must contain both the toolbar and the `<table>`. Remove the extra `</div>` after the toolbar. Verify all three tabs render correctly after the fix.

**Files to touch:** `resources/views/admin/issuance.blade.php`

---

### BUG-2: Reports Blade Duplicate Condition Fix (P0)
**Goal:** Make the consumable issuance log report reachable

The reports blade (`resources/views/admin/reports.blade.php`) has two `@elseif($reportType === 'consumable_issuance')` blocks. The first should be renamed to `consumable_low_stock` and the report dropdown updated with a new option.

**Files to touch:**
- `resources/views/admin/reports.blade.php` — rename condition
- `app/Http/Controllers/ItemController.php` — add `consumable_low_stock` case in `reports()` method
- Add dropdown option in `reports.blade.php`

---

### BUG-3: Borrower Cancel Service Routing (P1)
**Goal:** Route borrower cancellation through `IssuanceService::cancel()` for consistency

`BorrowerController@cancelConsumableRequest` currently:
1. Manually sets `status = 'cancelled'`
2. Manually creates `AuditLog`
3. Manually notifies staff

This should call `IssuanceService::cancel($issuance, Auth::id())` which handles all three. The `IssuanceService::cancel()` already handles `pending_issue` status (no stock restore needed).

**Files to touch:** `app/Http/Controllers/BorrowerController.php` → `cancelConsumableRequest()`

---

### ISS-8b: Borrower 3-Step Wizard (P1)
**Current state:** The UI shows step indicators (1→2→3) but the flow is a single-page form. The PIN gate is a modal overlay, not a wizard step.

**Target:** True 3-step wizard with animated transitions:

```
┌─────────────────────────────────────────────┐
│ Step 1: ITEM SELECTION                      │
│ ┌─────────────────────────────────────────┐ │
│ │ • Favorites chips (request-again)       │ │
│ │ • Item cards grid with stock badges     │ │
│ │ • Low-stock warning indicators          │ │
│ │ • Click card → radio select → [Next]    │ │
│ └─────────────────────────────────────────┘ │
│                                             │
│ Step 2: QUANTITY & PURPOSE                  │
│ ┌─────────────────────────────────────────┐ │
│ │ • Quantity input with live stock max    │ │
│ │ • Purpose chips (6 presets + custom)    │ │
│ │ • Stock warning if exceeds available    │ │
│ │ • [Back] ←──────────────→ [Next]        │ │
│ └─────────────────────────────────────────┘ │
│                                             │
│ Step 3: PIN CONFIRMATION                    │
│ ┌─────────────────────────────────────────┐ │
│ │ • Summary card (item × qty, purpose)    │ │
│ │ • 4-digit PIN input                     │ │
│ │ • [Back] ←──────────────→ [Submit]      │ │
│ │ • POST request → verify-pin → create    │ │
│ └─────────────────────────────────────────┘ │
└─────────────────────────────────────────────┘
```

**Implementation plan:**
1. Replace single-form layout with three step panels (`display:none` toggled by JS)
2. Step 1: Reuse existing card grid + favorites
3. Step 2: Reuse quantity + purpose chips, move purpose selection here
4. Step 3: Summary card + inline PIN input (replace modal with inline step)
5. Keyboard: Arrow keys for step nav, Enter to advance
6. Back button preserves previous state
7. Step indicator bar updates active state with animation

**Files to touch:**
- `resources/views/borrower/issuance.blade.php` — restructure form into 3 panels
- Inline `<script>` block — add step navigation logic

---

### ISS-2b: Camera Scan-to-Issue (P1)
**Goal:** Open camera, scan barcode, match stock item, pre-fill issue modal

**Flow:**
1. Click "Scan" button in stocks toolbar
2. Full-screen camera modal opens (reuse `html5-qrcode` from composer.json)
3. Camera scans barcode → match `name` or custom field
4. Auto-navigate to stock in table or pre-fill single-issue modal

**Files to touch:**
- `resources/views/admin/issuance.blade.php` — add scan modal + JS
- No new backend endpoints needed (uses existing `searchStocks`)

---

### ISS-6a: Fix Consumable Low Stock Report (P2)
**Goal:** Add a `consumable_low_stock` report type separate from `consumable_issuance`

Currently the first `consumable_issuance` block in reports.blade.php renders low-stock consumable data. This should be its own report type.

**Files to touch:**
- `resources/views/admin/reports.blade.php` — rename first condition to `consumable_low_stock`
- `app/Http/Controllers/ItemController.php` — add `consumable_low_stock` case
- `resources/views/admin/reports.blade.php` — add dropdown option

---

### ISS-10: Data Hygiene (P3)
| Sub-task | Status | Notes |
|---|---|---|
| Delete guard | ✅ Done | Blocks delete if issuance_count > 0 |
| Dedupe helper | ⏳ Pending | Report near-duplicates; merge action |
| Orphan prevention | ⏳ Pending | Soft-delete stocks with history |
| Soft-delete cleanup | 💡 Future | Scheduled command to prune old archived stocks |

---

### ISS-11: Extract Admin Issuance JS (P3)
**Goal:** Move ~400 lines of inline `<script>` from `admin/issuance.blade.php` to `public/js/admin-issuance.js`

**Benefits:**
- HTTP caching for repeat page loads
- CSP compliance (no inline scripts)
- Easier maintenance and debugging
- Smaller Blade template

**Implementation:**
1. Extract all JS from `<script>` blocks (except the `@json()` data injection)
2. Create `public/js/admin-issuance.js`
3. Pass data via `data-*` attributes on a root element instead of inline `@json()`
4. Include the script in the Blade template

---

## ✅ COMPLETED — Already Shipped (Reference)

| Item | Status | Key Files |
|---|---|---|
| ISS-0 Correctness (locks, silent paths, payloads) | ✅ | `IssuanceService`, `IssuanceController` |
| ISS-1 Stock lifecycle + thresholds | ✅ | `IssuanceController`, `ConsumableStock` model |
| ISS-2 Quick replenish + search endpoint | ✅ | `replenish()`, `searchStocks()`, `quickReplenish` modal |
| ISS-3 Bulk kit cart (multi-line, atomic/partial) | ✅ | `bulkIssue()`, `window.bulk` cart, `group_id` |
| ISS-4 KPI pills + 30-day trend + running balance | ✅ | `IssuanceController@index`, QuickChart, `stockHistory()` |
| ISS-5 History workbench | ✅ | Filters, CSV export, per-page, `withQueryString()` |
| ISS-6 Reports engine (consumable types) | ✅ | `ItemController@reports`, reports.blade.php |
| ISS-7 Low-stock alerts | ✅ | `checkLowStock()` daily deduped, sidebar badge |
| ISS-8 Borrower item cards + PIN gate | ✅ | `borrower/issuance.blade.php`, `requestConsumable()` |
| ISS-9 CSV bulk import | ✅ | `importStocks()`, import modal |

---

## 🗂️ File Map — Key Touch Points

| Area | Files |
|---|---|
| **Backend (Service)** | `app/Services/IssuanceService.php` |
| **Backend (Admin)** | `app/Http/Controllers/IssuanceController.php` |
| **Backend (Borrower)** | `app/Http/Controllers/BorrowerController.php` |
| **Models** | `app/Models/ConsumableIssuance.php`, `app/Models/ConsumableStock.php` |
| **Admin Views** | `resources/views/admin/issuance.blade.php`, `resources/views/admin/reports.blade.php` |
| **Borrower Views** | `resources/views/borrower/issuance.blade.php` |
| **JS (Shared)** | `public/js/admin.js`, `public/js/borrower.js` |
| **Routes** | `routes/web.php` (lines 57–68, 192–201) |
| **Migrations** | `database/migrations/2026_07_21_000004_create_consumable_issuances_table.php`, `database/migrations/2026_07_21_000002_create_consumable_stocks_table.php`, `database/migrations/2026_08_25_300001_add_group_id_to_consumable_issuances_table.php` |

---

## 🛠️ Next Actions (Priority Order)

1. **BUG-1** — Fix admin issuance blade HTML nesting (0.5 hr)
2. **BUG-2** — Fix reports blade duplicate condition (0.5 hr)
3. **BUG-3** — Route borrower cancel through IssuanceService (0.5 hr)
4. **ISS-8b** — Borrower 3-step wizard (Item → Qty/Purpose → PIN) (3 hrs)
5. **ISS-2b** — Camera scan-to-issue modal (reuse `html5-qrcode`) (3 hrs)
6. **ISS-6a** — Consumable low stock report type fix (1 hr)
7. **ISS-10** — Dedupe helper + orphan prevention (2 hrs)
8. **ISS-11** — Extract admin issuance inline JS to external file (2 hrs)

---

## 🔑 Design Principles (Non-Negotiable)

1. **Single source of truth** — All mutations via `IssuanceService` (row-locked, audited, notified)
2. **No duplicate implementation** — Single service, shared partials, shared JS modules
3. **Professional UI** — System design tokens (`--bg-surface`, `--accent-blue`, `panel-card`, `admin-table`, `kpi-pill`), AppDrawer for drawers, `data-centered` for destructive modals
4. **Accessibility** — Semantic HTML, focus management, ARIA labels, keyboard shortcuts (`/` search, `N` new, `Esc` close)
5. **Observability** — Every mutation audited, low-stock alerts deduped daily, PIN-gated borrower actions
6. **Performance** — Lazy-load heavy vendor JS on demand; critical CSS inline; critical JS deferred
7. **Service layer discipline** — Controllers delegate to services; never bypass `IssuanceService` for mutations

---

> **Status:** Core issuance workflow is **production-ready** with 3 bugs to fix. Remaining work is polish (wizard UI, scanner), reporting fixes, and code quality improvements. All mutations are safe, audited, and notified.

---

# 🏷️ On Good Condition — Item Status & Register New Asset Plan

> **Date:** September 7, 2026  
> **Focus:** Add "On Good Condition" status for inventory items not available for borrowing; modify Register New Asset page and all related pages  
> **Stack:** Laravel 13, MySQL 8, Bootstrap 5.3, vanilla ES6+, Blade

---

## 🎯 Vision

Items in the inventory can now exist in two distinct tracked states:
- **`available`** — Item is tracked AND available for borrowing
- **`ongoodcondition`** — Item is tracked but NOT available for borrowing (in good working condition, but intentionally excluded from the borrowing pool)

The client wants items that are tracked in the system but should not appear as borrowable. The "On Good Condition" status fills this gap, keeping items visible in the inventory while preventing them from being issued to borrowers.

---

## 🔄 Current Status System

```
┌─────────────────────────────────────────────────────────────────┐
│                    CURRENT STATUS FLOW                              │
│                                                                    │
│  ┌─────────────┐     ┌─────────────┐     ┌─────────────┐      │
│  │  AVAILABLE   │────▶│  BORROWED   │────▶│ RETURNED    │      │
│  │  (borrowable)│     │  (in use)   │     │  (back)     │      │
│  └─────────────┘     └─────────────┘     └─────────────┘      │
│                                                                    │
│  ┌─────────────┐     ┌─────────────┐     ┌─────────────┐      │
│  │  DAMAGED     │     │ MAINTENANCE │     │  DISPOSED   │      │
│  │  (unavail)   │     │  (unavail)  │     │  (removed)  │      │
│  └─────────────┘     └─────────────┘     └─────────────┘      │
│                                                                    │
└─────────────────────────────────────────────────────────────────┘
```

### New Status Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    NEW STATUS FLOW                                │
│                                                                    │
│  ┌─────────────────┐     ┌─────────────┐     ┌─────────────┐  │
│  │AVAILABLE        │────▶│  BORROWED   │────▶│ RETURNED    │  │
│  │(borrowable)     │     │  (in use)   │     │  (back)     │  │
│  └─────────────────┘     └─────────────┘     └─────────────┘  │
│                                                                    │
│  ┌─────────────────┐     ┌─────────────┐     ┌─────────────┐  │
│  │ON GOOD CONDITION│     │  DAMAGED    │     │ MAINTENANCE │  │
│  │(NOT borrowable) │     │  (unavail)  │     │  (unavail)  │  │
│  └─────────────────┘     └─────────────┘     └─────────────┘  │
│                                                                    │
│  ┌─────────────┐                                              │
│  │  DISPOSED   │                                              │
│  │  (removed)  │                                              │
│  └─────────────┘                                              │
│                                                                    │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📋 Status Definitions

| Status Value | Label | Badge Color | Borrowable | Description |
|---|---|---|---|---|
| `available` | Available | Green | ✅ Yes | Item is tracked and available for borrowing |
| `ongoodcondition` | On Good Condition | Blue/Cyan | ❌ No | Item is tracked, in good working condition, but NOT available for borrowing |
| `borrowed` | Borrowed | Yellow | N/A | Item is currently borrowed by someone |
| `damaged` | Damaged | Red | ❌ No | Item is damaged/broken |
| `maintenance` | Maintenance | Red | ❌ No | Item is in maintenance/repair |
| `disposed` | Disposed | Red/Gray | ❌ No | Item has been disposed/removed |

---

## 📄 Files to Modify

| Area | Files | Change |
|---|---|---|
| **Backend Controller** | `app/Http/Controllers/ItemController.php` | Add validation, update status handling |
| **Model** | `app/Models/Item.php` | No changes needed (fillable already includes `status`) |
| **Create View** | `resources/views/admin/items/create.blade.php` | Add status field |
| **Edit View** | `resources/views/admin/items/edit.blade.php` | Add `ongoodcondition` to dropdown |
| **Index View** | `resources/views/admin/items/index.blade.php` | Add badge, filter, stats, bulk action |
| **Reports View** | `resources/views/admin/reports.blade.php` | Add counts and filters |
| **CSS** | `public/css/admin.css` | Add new status color |

---

## ✅ Task List

### P1 — Backend Changes

- [ ] **PC-1** — Add `ongoodcondition` to `ItemController@store()`: change default `$item->status = 'available'` to use `$request->status ?? 'available'` so the register form can set the initial status
- [ ] **PC-2** — Add `ongoodcondition` to `ItemController@update()` validation: add `ongoodcondition` to the `status` allowed values in the `required|string` validation rule
- [ ] **PC-3** — Add `ongoodcondition` to `ItemController@index()`: ensure the status filter works with the new status (already handles arbitrary string, but verify)
- [ ] **PC-4** — Update `ItemController@availableItems()`: this returns only `where('status', 'available')` — verify it does NOT include `ongoodcondition` items (it already does, since it filters by `status = 'available'` only)
- [ ] **PC-5** — Update `ItemController@reports()`: add `ongoodcondition` to the counts (currently only `borrowedCount` and `damagedCount` are cached; add `goodConditionCount`)
- [ ] **PC-6** — Update `ItemController@details()`: ensure `ongoodcondition` items are handled properly in the JSON details response (no special logic needed since status is just returned as-is)
- [ ] **PC-7** — Update `ItemController@availableItems()` if used by issuance: verify it still only returns `available` items (not `ongoodcondition`)

### P2 — Frontend: Register New Asset Page (create.blade.php)

- [ ] **FR-1** — Add a **Status** dropdown field to the "Classification & Sourcing" section of `create.blade.php` with options:
  - `Available` (default, borrowable)
  - `On Good Condition` (not borrowable)
- [ ] **FR-2** — Add `<input type="hidden" name="status" value="available">` or use a `<select>` with the two options
- [ ] **FR-3** — Update the Live Tag Preview section to show the selected status
- [ ] **FR-4** — Ensure the form submits the selected status to `ItemController@store`

### P3 — Frontend: Edit Asset Page (edit.blade.php)

- [ ] **EE-1** — Add `ongoodcondition` option to the Current Status `<select>` dropdown in `edit.blade.php`
- [ ] **EE-2** — Ensure the selected status is properly preserved when editing

### P4 — Frontend: Index Page (index.blade.php)

- [ ] **IX-1** — Add `ongoodcondition` to the `statusColors` array with appropriate color (e.g., blue/cyan):
  ```js
  'ongoodcondition' => ['bg' => 'var(--accent-blue-bg)', 'text' => 'var(--accent-blue)', 'label' => 'GOOD CONDITION']
  ```
- [ ] **IX-2** — Add `ongoodcondition` to the filter pills:
  ```html
  <a href="{{ request()->fullUrlWithQuery(['status' => 'ongoodcondition']) }}" class="pill pill-blue ...">On Good Condition</a>
  ```
- [ ] **IX-3** — Add `ongoodcondition` to the inventory stat counters (Available/Borrowed/Unavailable breakdown):
  - Update the stats card area to show "On Good Condition" as a separate stat
  - Update the JS `data.available` / `data.borrowed` / `data.damaged` references to include `data.goodCondition`
- [ ] **IX-4** — Add `ongoodcondition` to the bulk status change dropdown (`bulkSetStatus`):
  ```html
  <a onclick="bulkSetStatus('ongoodcondition')">On Good Condition</a>
  ```
- [ ] **IX-5** — Update the "Unavailable" stat card label or add a new stat card for "On Good Condition"

### P5 — Frontend: Reports Page (reports.blade.php)

- [ ] **RP-1** — Add `ongoodcondition` to the status filter dropdown:
  ```html
  <option value="ongoodcondition">On Good Condition</option>
  ```
- [ ] **RP-2** — Update the `damagedCount` section to also include `ongoodcondition` count, or add a new `goodConditionCount` KPI card
- [ ] **RP-3** — Update the summary report to include `ongoodcondition` in asset counts

### P6 — Frontend: CSS & Visual Design

- [ ] **CV-1** — Add `ongoodcondition` color scheme to `public/css/admin.css`:
  ```css
  /* On Good Condition — Blue/Cyan theme */
  :root[data-palette="default"] {
    --accent-ongoodcondition-bg: #eff6ff;
    --accent-ongoodcondition: #3b82f6;
  }
  ```
  Or reuse the existing `--accent-blue` variables.
- [ ] **CV-2** — Add `.pill-blue` CSS class if not already existing (similar to `.pill-green`, `.pill-yellow`, `.pill-red`)

### P7 — Frontend: Issuance Page (issuance.blade.js)

- [ ] **IS-1** — Verify that `availableItems()` endpoint only returns `available` items (not `ongoodcondition`) — already verified in PC-4
- [ ] **IS-2** — If the issuance page has any "available" counters, update them to exclude `ongoodcondition` items

### P8 — Testing & Verification

- [ ] **TV-1** — Run `php artisan test` — all 35 tests should still pass
- [ ] **TV-2** — Verify register form submits `ongoodcondition` status correctly
- [ ] **TV-3** — Verify index page shows `ongoodcondition` badge and filters work
- [ ] **TV-4** — Verify reports page shows `ongoodcondition` count
- [ ] **TV-5** — Verify edit page allows changing status to/from `ongoodcondition`
- [ ] **TV-6** — Verify bulk status change works with `ongoodcondition`
- [ ] **TV-7** — Verify `availableItems()` JSON endpoint does NOT return `ongoodcondition` items

---

## 🔑 Design Principles (Non-Negotiable)

1. **Backward compatible** — All existing `available` items work exactly as before
2. **Clear distinction** — `ongoodcondition` must look visually distinct from `available` (different badge color)
3. **No borrowable path** — `ongoodcondition` items must NEVER appear in borrower item selection or `availableItems()` endpoints
4. **Consistent everywhere** — New status must work in index, edit, create, reports, and bulk actions
5. **Professional UI** — Follow existing design tokens (`--accent-blue`, `panel-card`, `admin-table`, `pill-*` classes)
6. **Service layer discipline** — No direct model mutations outside controllers; all changes go through `ItemController`

---

## 🗂️ File Map — New Touch Points

| Area | Files |
|---|---|
| **Backend** | `app/Http/Controllers/ItemController.php` (store, update, index, reports, availableItems) |
| **Views** | `resources/views/admin/items/create.blade.php`, `resources/views/admin/items/edit.blade.php`, `resources/views/admin/items/index.blade.php`, `resources/views/admin/reports.blade.php` |
| **CSS** | `public/css/admin.css` (new status color) |
| **Tests** | `tests/Feature/ItemDetailsDrawerTest.php` (verify new status) |

---

> **Status:** Plan created. Implementation follows Priority Order: Backend first, then Frontend (Create → Edit → Index → Reports → CSS), then Testing. All `ongoodcondition` items must never be borrowable.
