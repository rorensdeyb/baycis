# 📘 BayCIS — System Functions & User Guide

> **Version:** 1.5 — September 8, 2026 (Updated: Session Code: B31/U33)  
> **System:** Bay Central Elementary School — Inventory Management System

---

## Table of Contents

1. [Getting Started](#-getting-started)
2. [User Roles](#-user-roles)
3. [Administrator Guide](#-administrator-guide)
   - 3.1 [Command Center (Dashboard)](#31-command-center-dashboard)
   - 3.2 [Inventory Management](#32-inventory-management)
   - 3.3 [Borrow Requests](#33-borrow-requests)
   - 3.4 [Issuance (Consumables)](#34-issuance-consumables)
   - 3.5 [Return Assets](#35-return-assets)
   - 3.6 [Transaction History](#36-transaction-history)
   - 3.7 [Reports](#37-reports)
   - 3.8 [Archived Assets](#38-archived-assets)
   - 3.9 [User Management](#39-user-management)
    - 3.10 [Settings & Account](#310-settings--account)
       - Asset Categories now support **Sub-Categories / Tags** (e.g., ICT Equipment → Laptop, Printer, Projector). Manage them under Settings → Asset Categories; they appear as a dependent dropdown on asset forms and as browse filters for borrowers.
4. [Borrower Guide](#-borrower-guide)
   - 4.1 [Borrower Dashboard](#41-borrower-dashboard)
   - 4.2 [Requesting an Asset](#42-requesting-an-asset)
   - 4.3 [Consumable Issuances](#43-consumable-issuances)
   - 4.4 [Returning Items](#44-returning-items)
   - 4.5 [Transaction History](#45-transaction-history)
   - 4.6 [Account Settings](#46-account-settings)
5. [Common Features](#-common-features)
   - 5.1 [Navigation & Search](#51-navigation--search)
   - 5.2 [Notifications](#52-notifications)
   - 5.3 [PIN Security](#53-pin-security)
   - 5.4 [Theme Toggle & 60-30-10 Design System](#54-theme-toggle--60-30-10-design-system)
   - 5.5 [Keyboard Shortcuts](#55-keyboard-shortcuts)
   - 5.6 [Offline Mode](#56-offline-mode)
   - 5.7 [Live Tag Preview (Create/Edit Asset)](#57-live-tag-preview)
   - 5.8 [Print Studio](#58-print-studio)
   - 5.9 [Saved Filters & Column Toggle](#59-saved-filters--column-toggle)

---

## 🚀 Getting Started

### Accessing the System

1. Open a web browser (Chrome, Firefox, Safari, or Edge)
2. Navigate to the BayCIS URL provided by your system administrator
3. You will see the **Welcome Screen** with login options

### First-Time Login

1. Click **"Sign In"** on the welcome screen
2. Enter your **Email Address** and **Password** (provided by your administrator)
3. Click **"Login"**

### PIN Setup (First Login Only)

On your first login, you will be prompted to set a 4-digit PIN:

1. **Phase 1:** Enter a 4-digit PIN using the on-screen number pad
2. **Phase 2:** Re-enter the same PIN to confirm
3. If the PINs match, your account is secured
4. If they don't match, you'll be prompted to try again

> ⚠️ **Keep your PIN secure.** You will need it for sensitive actions like approving returns and verifying transactions.

---

## 👤 User Roles

The system has three user roles:

| Role | Access | Capabilities |
|------|--------|-------------|
| **Administrator** | Full system access | Manage inventory, approve requests, create users & set roles, configure system settings, audit logs, backup/restore, generate reports |
| **Property Custodian** | Operational inventory access | Day-to-day asset work: inventory CRUD, barcode/property-tag printing via Print Studio, approve borrows, verify returns, manage consumable stock, reports (no user management, settings, or backup) |
| **Borrower** | Self-service portal | Request assets, return items, request account deletion, view history, manage account |

Your role determines which dashboard you see after logging in. Administrators and Custodians share the Command Center; custodians see operational menus only.

---

# 🛠️ Administrator Guide

## 3.1 Command Center (Dashboard)

The **Command Center** is your headquarters for real-time inventory monitoring and operational property management.

### Live Time & Date Widget

Located in the top-right of the Command Center header:
- **Digital Clock** — Live ticking time with seconds (`h:i:s A`) rendered with tabular numeric alignment to prevent layout jitter.
- **Calendar Date** — Formatted weekday, month, day, and year.
- **LIVE Indicator** — A pulsing emerald badge indicating real-time system synchronization.

### KPI Bar

Six core asset status metrics are displayed in the sticky header bar:
- **Total Assets** — Count of all items in the inventory
- **Available Rate %** — Percentage of assets currently available (green ≥ 75%, yellow ≥ 40%, red < 40%)
- **Active Borrows** — Number of items currently borrowed out (with week-over-week trend arrow and percentage)
- **Pending Requests** — Borrow requests awaiting your approval (pulses red when > 0)
- **Overdue Returns** — Items borrowed for over 7 days without return
- **Low Stock Items** — Consumable supplies below their reorder level

### Operational Velocity & Activity Strip

A secondary 4-card metric strip tracks monthly operational throughput and borrower compliance:
- **Monthly Checkouts** — Total equipment loans initiated during the current month, accompanied by a percentage trend indicator compared against last month (`+X% vs last month`).
- **Supplies Issued** — Cumulative count of consumable supply units fulfilled and confirmed to borrowers this month.
- **On-Time Return Rate** — Punctuality compliance percentage measuring the ratio of equipment returned within the standard 7-day loan window.
- **Active Borrowers** — Unique count of individual teachers and staff with active equipment circulations this month.

### Asset Distribution Chart (Interactive Donut)

An interactive donut chart shows asset status breakdown:
- **Green:** Available
- **Blue:** On Good Condition
- **Yellow:** Borrowed
- **Red:** Damaged
- **Purple:** Maintenance

**Entry Animation:** When the page loads, segments animate in sequentially from zero to their final size with a spring-like bounce (200ms stagger per segment). The center percentage fades in after all segments complete.

**Hover Interactions:**
- Hover over a colored arc → segment brightens with a glow `drop-shadow`; non-matching legend items dim to 50% opacity
- Hover over a legend item → legend slides right 6px; corresponding chart segment highlights with glow
- Mouse leave → everything resets smoothly

**Click to Filter:** Click any segment arc or its legend label to navigate directly to the filtered inventory page (e.g., clicking "Available" opens `/admin/inventory?status=available`). Segments with zero count are non-clickable.

**Refresh:** Load new chart data at any time using the **Refresh** action in the FAB menu (bottom-right `+` button). Listener accumulation is prevented — old event handlers are stripped before re-binding.

### 7-Day Loan Velocity Trend Chart

An interactive daily checkout bar chart located beside the donut chart:
- Visualizes day-by-day borrowing activity across the last 7 calendar days (e.g., Wed, Thu, Fri, Sat, Sun, Mon, Tue).
- Animated bar heights dynamically scaled relative to the week's peak volume.
- Hover over any bar to view the exact date, day, and checkout count.

### Top Inventory Categories Breakdown

A classification breakdown panel displaying asset density across the top 5 equipment categories:
- Displays category names, item counts, and relative percentage shares of the total inventory.
- Proportional progress bars color-coded to the active theme palette.
- Direct link ("All Items →") to navigate into the full inventory workspace.

### Priority Alerts

The alert panel highlights urgent items:
- Pending requests needing approval
- Returns pending verification
- Overdue returns
- Low stock consumables

Each alert links directly to the relevant page for quick action.

### Quick Actions Panel

- **Issue Item** — Navigate to the issuance module
- **Review Requests** — View and approve/decline borrow requests
- **Manage Inventory** — View and edit the full inventory
- **Add New Item** — Register a new asset

### FAB (Floating Action Button)

The `+` button at the bottom-right expands into a quick-action menu:
- **New Item** — Create a new asset
- **Issue** — Go to issuance
- **Approve** — Review pending requests
- **Refresh** — Reload the chart data

### Recent Activity Feed

Shows the latest borrow request activities with status indicators:
- 🟢 Green dot = Approved or Returned
- 🔴 Red dot = Rejected or Cancelled
- 🟡 Yellow dot = Pending

---

## 3.2 Inventory Management

### Viewing the Inventory

Navigate to **Inventory** in the sidebar. The inventory page features:

**Mini-Widget Stats Bar:**
- Shows Total, Available, Good Condition, Borrowed, and Unavailable counts for the current filtered view
- Updates automatically when filters change
- Includes a **CSV Export** link to download the current view

**Quick Filter Pills:**
- **All** — Show all items
- **Available** — Show only available items (green)
- **On Good Condition** — Show items tracked but not available for borrowing (blue)
- **Borrowed** — Show only borrowed items (yellow)
- **Unavailable** — Show damaged/maintenance items (red)

**Category Filter Dropdown:**
- Filter by specific asset categories alongside the status pills

**Column Visibility Toggle:**
- Click the **columns icon** (📊) next to the "Clear" button to show/hide columns
- **Category** — Toggle the category column visibility
- **Location** — Toggle the location column visibility
- **Reset** — Restore all columns to visible
- Preferences are saved to localStorage and persist across sessions
- `toggleColumnMenu()` → Opens/closes the column visibility dropdown
- `toggleCol(colName)` → Toggles a specific column and saves preference
- `resetColumns()` → Restores all columns to visible and clears saved preference

**Inline Status Quick-Edit:**
- Click any status badge (Available, Borrowed, UNAVAILABLE) on the table
- A floating popover appears near the badge with alternative status options
- Click a new status to instantly update it via AJAX
- The page refreshes automatically to reflect the change
- `quickSetStatus(id, status, button)` → Sends POST request to update the item's status
- The popover auto-closes when clicking outside

**Saved Filters:**
- Click the **bookmark icon** (🔖) in the filter pills to open the Saved Filters dropdown
- Shows a list of previously saved filter presets with one-click application
- Click **"+ Save Current Filter"** to open the **Name This Filter** modal
- Enter a name (e.g., "Borrowed IT Assets") and click **Save Filter** or press **Enter**
- The filter (status + category + search) is saved to localStorage
- Click a saved filter name to apply it immediately (navigates to the filtered inventory page)
- Click the **×** on a saved filter to delete it
- Press **Escape** to cancel naming; click the backdrop to close

**Keyboard Shortcuts Help:**
- Click the **?** button next to "Add New" to see available keyboard shortcuts
- Shortcuts: `/` (focus search), `N` (new asset), `B` (toggle bulk select), `Esc` (clear selection)
- The dropdown closes when clicking outside
- `toggleShortcutsHelp()` → Toggles the shortcuts help dropdown visibility

**Bulk Select Operations:**
1. Click the checkbox in the table header to select all items on the page
2. Or check individual items
3. A floating action bar appears showing the count of selected items
4. Actions available:
   - **Print** — Open batch print view for selected tags
   - **Change Status** — Set all selected items to a new status (Available, On Good Condition, Borrowed, Damaged, Maintenance, Disposed)
   - **Clear** — Deselect all items

**Copy Property Tag:**
Click the clipboard icon next to any property tag to copy it. The icon changes to a checkmark for 1.5 seconds as confirmation.

### Searching

Type in the search bar to filter items by name or property tag. The search debounces (waits 1 second after you stop typing) to avoid excessive page reloads. Press `Enter` to search immediately.

### Exporting Assets (CSV)

Administrators and Property Custodians can export asset records directly to CSV:
1. Apply any desired search keywords, status filter pills, category filters, or location dropdowns (or leave filters unselected to export the entire active inventory).
2. Click the **"Export CSV"** link located in the inventory header stats bar.
3. The server generates and streams a standardized CSV file (`inventory-report-YYYY-MM-DD.csv`) formatted for official DepEd inventory records.
4. Exported data includes:
   - **Property Tag**
   - **Asset Name**
   - **Model / Serial Number**
   - **Category**
   - **Sub-Category (Tag)**
   - **Location**
   - **Unit Value**
   - **Current Status**
   - **Acquisition Date**

### Adding a New Item

1. Click **"Add New Item"** button
2. Fill out the form:

| Field | Description | Required |
|-------|-------------|----------|
| Asset Category | Classification (e.g., IT Equipment, Furniture) | ✅ |
| Supplier | Funding source (LGU, MOOE, Donation) | ✅ |
| Item Description | Brand, model, and name | ✅ |
| Serial Number | Manufacturer serial number | Optional |
| Accountable Personnel | Person responsible for the asset | ✅ |
| Acquisition Date | Date of purchase or receipt | ✅ |
| Cost per Item | Unit price in Philippine Pesos | ✅ |
| Quantity Received | Number of identical items in this batch (1–100) | ✅ |
| Location | Where the asset is stored | ✅ |

3. As you fill out the form, the **Live Tag Preview** on the right updates in real-time
4. Click **"Register Asset & Generate Tag"** to save
5. After saving, you can:
   - **Print All Tags** — Open batch print view for all items just created
   - **Finish & Go to Inventory** — Return to the inventory list

**Duplicating an Existing Item:**
1. Open an existing item's edit page
2. Click **"Duplicate This Asset"** to pre-fill a new create form with that item's data
3. Modify fields as needed and save as a new item

### Editing an Item

1. Click the **pencil icon** (✏️) next to any item in the inventory table
2. Update any fields on the edit form
3. The **Live Tag Preview** on the right updates in real-time as you edit
4. Click **"Save Changes"**
5. After saving, you can print the tag or return to inventory

### Deleting an Item

1. Click the **trash icon** (🗑️) next to any item
2. A **PIN confirmation modal** appears — enter your 4-digit security PIN
3. Click **"Delete"** (or press Enter after entering the PIN)
4. On successful PIN verification, the item is removed from the active inventory
5. Deleted items move to the **Archived Assets** page and can be restored later

> ⚠️ PIN verification is required for every deletion. The system uses the same lockout protection as login PINs (3 failed attempts = 15-minute cooldown).

### Printing Property Tags

All property-tag printing flows through the **Print Studio** (see [5.8 Print Studio](#58-print-studio)):

1. Click the **grid icon** next to an item to open that tag in Print Studio
2. In the **Asset Overview drawer**, click **"Print in Studio"**
3. Select multiple items and click **"Print Studio"** in the bulk action bar
4. After registering new assets, the success screen offers **"Print Tag(s) in Studio"**

### Barcode Scanning

Two scanning options are available:
1. **Desktop Scanner Modal** — Click "Scan" to open a hardware scanner input. Scan a barcode to auto-search the inventory
2. **Mobile Camera Scanner** — Click the camera icon (mobile only) to use your device's camera to scan barcodes

---

## 3.3 Borrow Requests

### Viewing Requests

Navigate to **Borrow Requests** in the sidebar. The page shows all requests with:
- **Search** by Transaction ID, borrower name, or property tag
- **Status Filter** — All, Pending, Approved, Returned, Rejected, Cancelled

### Approving a Request

1. Click the **green checkmark** (✓) on a pending request
2. (Optional) Add admin remarks
3. Click **"Yes, Approve Request"**
4. The asset is automatically marked as "Borrowed" in the inventory

### Rejecting a Request

1. Click the **red X** (✗) on a pending request
2. Enter a **reason for rejection** (required)
3. Click **"Yes, Decline Request"**

### Viewing Request Details

Click the **eye icon** (👁️) on any request to see full details including:
- Borrower information (name, email)
- Asset information (name, category, property tag, serial number)
- Request status and timeline
- Admin remarks
- Fast-Track QR code (for pending requests)

### QR Code Scanner

Click **"Scan Request QR"** to use your camera to scan a borrower's digital receipt QR code — instantly locates their request.

---

## 3.4 Issuance (Consumables)

The Issuance module manages consumable supplies (e.g., paper, ink, cleaning materials).

### Managing Stock

- View current stock levels for all consumables
- Items below reorder level are highlighted
- Click **"Add Stock"** to increase inventory for a consumable item

### Issuing Items to Borrowers

Two methods:

**Admin-Initiated Issue:**
1. Click **"Issue Item"**
2. Select the borrower and item
3. Specify quantity and purpose
4. Submit — the borrower will receive a notification

**Fulfilling a Request:**
1. When a borrower requests a consumable, a notification appears
2. Click to view the request and fulfill it
3. The borrower receives a confirmation notification

---

## 3.5 Return Assets

### Verifying Returns

When a borrower submits a return:

1. Navigate to **Return Assets** in the sidebar
2. Review the reported condition (Good, Damaged, Needs Repair)
3. Read the borrower's remarks
4. Click **"Verify Return"** to confirm
5. The asset is returned to "Available" status in the inventory

### Scanning for Return

Click **"Scan to Return"** to use the camera to scan an asset's barcode and quickly locate its return record.

---

## 3.6 Transaction History

View a complete log of all borrow requests and their status changes.

**Features:**
- Search by Transaction ID, borrower name, or asset
- Filter by status
- Time-ordered list with the most recent first

---

## 3.7 Reports

Generate two types of reports:

### Summary Report
Lists all inventory items with optional filters:
- Category
- Status
- Date range (acquisition date)

### Borrowing Report
Lists all borrowing transactions with:
- Date range filtering
- Status filtering

Reports can be generated and viewed on-screen.

---

## 3.8 Archived Assets

View, restore, or permanently destroy disposed assets.

**Stats Bar:**
- **Total Archived** — Count of all soft-deleted items
- **This Month** — Items archived in the current month
- **Restored** — Items restored in the previous month

**Search & Filter:**
- Search by asset name, property tag, or serial number
- Filter by category (dropdown)
- Sort by newest/oldest or name (A-Z/Z-A)
- Preferences are preserved in the URL — bookmarkable

**Table:**
- Each row shows asset name (with icon), category, location, and relative archived timestamp
- Click any row to open the **Details Drawer** with full asset information
- **Restore** — Return an item to active inventory (sets status to Available)
- **Force Delete** — Permanently remove from database (requires confirmation)
- **Bulk actions** — Select multiple items to restore or destroy at once

**Details Drawer:**
Click any archived asset row to view its complete profile:
- Category, Tag, Location, Location Code
- Supplier, Serial Number, Acquisition Cost & Date
- Accountable Personnel, Registration Date
- Restore and Destroy action buttons at the bottom

---

## 3.9 User Management

### Creating Users

1. Navigate to **User Management**
2. Click **"Add New User"** — opens the slide-over account creation panel
3. Fill out account details:
   - Full Name
   - Email Address (used for login and OTP verification)
   - Teacher ID
   - Role (Borrower, Property Custodian, or Admin)
   - (A temporary initial password `BayCIS2026!` is assigned automatically, requiring OTP verification upon first sign-in)
4. Click **"Create Account"** in the pinned drawer action footer (or press Enter in any form field, or click "Cancel" to dismiss)

### Managing Existing Users
- Edit user details
- Reset passwords
- Deactivate/reactivate accounts

**Password reset options:** choose *Auto-generate a temporary password* or *Assign a specific password*, plus an optional *Require password change at first sign-in* toggle.
**PIN control:** use **Reset PIN** on an account's page to clear a forgotten PIN — you confirm with your own admin PIN, and the user creates a fresh PIN at next sign-in.

---

## 3.10 Settings & Account

### System Settings
- **General** — System name, organization name
- **Inventory** — Default settings for new items
- **Appearance** — Row density (Compact/Comfortable)

### Account Settings
- **Profile** — Update your name and email
- **Password** — Change your login password
- **PIN** — Update your security PIN

### PIN Reset
If you forget your PIN:
1. Go to Account Settings → PIN tab
2. Click **"Send OTP"** — an OTP code is sent to your email
3. Enter the OTP code
4. Set a new PIN

---

# 🙋 Borrower Guide

## 4.1 Borrower Dashboard

Your home screen shows:
- **Welcome Headline** — A modern, fluid greeting with your first name and a friendly wave emoji (`Good morning, {Name} 👋`) plus a subtitle, engineered with adaptive font-sizing (`clamp(20px, 3.5vw, 24px)`) to prevent awkward line breaks on narrow mobile viewports.
- **Date & Role Badges** — Compact calendar date and role indicator.
- **Quick Action Launchers** — Two large primary action buttons for **Borrow Equipment** and **Request Supplies**.
- **Action Notices** — Urgent alerts displayed when consumable supplies are issued by the custodian and await receipt confirmation, or when returns are pending staff verification.
- **Key Status Metrics** — Responsive 4-card grid tracking Active Loans, Pending Requests, Supplies Awaiting Confirmation, and Return Verifications.
- **Recent Activity Feed** — Chronological transaction log showing equipment name, status indicators, and timestamps.

### Mobile Navigation
On mobile, a **bottom navigation bar** provides quick access:
- 🏠 **Home** — Dashboard
- 📦 **Request** — Borrow an asset
- 📥 **Issuance** — Consumable requests
- 🕐 **History** — Past transactions
- ↩️ **Returns** — Return borrowed items

---

## 4.2 Requesting an Asset

1. Navigate to **Request Asset** (or click "Borrow Equipment" on the dashboard)
2. Browse the list of available items
3. Select an item by clicking the radio button
4. Enter the **Purpose** (why you need it)
5. Click **"Submit Request"**
6. Wait for admin approval — you'll receive a real-time toast alert and notification when it's approved or declined (clicking a decline alert takes you directly to your Transaction History to view administrator remarks).

### Cancelling a Request
1. Go to **History** and find your pending request
2. Click **"Cancel Request"** if the admin hasn't acted on it yet

---

## 4.3 Consumable Issuances

### Requesting Supplies
1. Navigate to **My Issuances**
2. Click **"Request Supplies"**
3. Select the consumable item and quantity
4. Enter the purpose
5. Submit — the admin will process your request

### Confirming Receipt
When an admin issues supplies to you:
1. Navigate to **My Issuances**
2. Find the issuance with status "Issued"
3. Click **"Confirm Receipt"** to acknowledge you received the items

### Cancelling a Request
If you no longer need the supplies, you can cancel a pending request.

---

## 4.4 Returning Items

1. Navigate to **Return Items**
2. Find the asset you borrowed (if you have active borrows, a badge shows the count)
3. Click **"Submit Return"**
4. Report the **condition** of the item:
   - **Good** — Item is in the same condition as when borrowed
   - **Damaged** — Item has damage
   - **Needs Repair** — Item needs maintenance
5. Add optional remarks
6. Confirm — the admin will verify the return

---

## 4.5 Transaction History

View all your past and current requests:
- Status indicators (Pending, Approved, Rejected, Cancelled, Returned)
- Date and time of each request
- Item details

---

## 4.6 Account Settings

- **Profile** — Update your name and email
- **Password** — Change your login password
- **PIN** — Set or update your security PIN

### PIN Reset
If you forget your PIN:
1. Click **"Forgot PIN?"** on the login page
2. Enter your registered email
3. Check your email for the OTP code
4. Enter the OTP and set a new PIN

---

# 🔧 Common Features

## 5.1 Navigation & Search

### Global Search Bar (Admin Only)
The global search bar at the top of every admin page lets you quickly navigate between pages:

1. Click or press `/` to focus the search bar
2. Type keywords (e.g., "inventory", "requests", "settings")
3. Matching pages appear in a dropdown with result counts
4. Use `↑` / `↓` arrow keys to highlight a result
5. Press `Enter` to navigate to the highlighted page
6. Press `Escape` to close the dropdown

### Sidebar Navigation
- **Desktop:** Persistent sidebar on the left with all pages organized by category
- **Mobile:** Tap the hamburger menu (☰) to open the sidebar
- **Collapsible sections:** Click "Transactions" or "System" headers to collapse/expand those sections. State is remembered between sessions.

### Breadcrumbs
A breadcrumb trail at the top of every admin page shows your current location. Click any breadcrumb link to navigate to a parent page.

### Quick Actions (FAB)
The floating `+` button on the Command Center provides one-tap access to common tasks.

### Page Size Selector
A dropdown next to the pagination controls lets you choose how many items to display per page (5, 10, 25, or 50). Your selection is applied immediately.

---

## 5.2 Notifications

The system includes a centralized real-time notification subsystem keeping administrators, custodians, and borrowers continuously informed of actionable workflow events.

### Notification Dropdown & Center
- **Bell Icon (🔔) & Unread Badge:** Positioned in the unified header with a real-time counter badge and a pulsing status indicator dot when unread notifications are present.
- **Segmented Filter Tabs:** The dropdown menu includes **"All"** and **"Unread"** tabs with live badge counters, allowing quick filtering between pending alerts and past activities.
- **Type-Specific Identification:** Visual distinction with custom icon medallions and color-coded pills for borrow requests, return submissions, approvals, cancellations, overdue reminders, low-stock warnings, and registration approvals.

### Notification Management Controls
- **Direct Workspace Navigation:** Clicking any notification marks it as read instantly and navigates directly to the relevant record or workflow (e.g., specific borrow approval modal, return verification list, or User Management filtered to pending activations for new account registrations).
- **Per-Item Quick Actions:** Hovering or focusing a notification row reveals inline controls to toggle read/unread status (`PATCH /notifications/{id}/toggle-read`) or remove the notification (`DELETE /notifications/{id}`).
- **Bulk Actions:** Header controls provide one-tap **"Mark All Read"** and **"Clear All"** commands to triage or dismiss notifications in bulk without page reloads.

### Real-Time In-Page Polling & Synchronization
- **Intelligent Polling Engine:** Background client polling (`GET /notifications/poll`) runs every 15 seconds during active usage, automatically switching to exponential backoff during user inactivity.
- **Cross-Tab Synchronization:** Polling results dispatch custom `baycis:realtime-update` DOM events, synchronizing header badges, unread tallies, and dashboard KPI indicators seamlessly across multiple open browser tabs without manual page refreshes.

---

## 5.3 PIN Security

Your 4-digit PIN is used to verify sensitive actions:

**What requires a PIN:**
- Approving borrow requests (admin)
- Verifying returns (admin)
- Confirming receipt of issuances (borrower)
- Submitting returns (borrower)
- Deleting assets from inventory (admin/custodian)
- Role promotion or demotion (admin)
- User deletion (admin)
- Clearing audit logs (admin)

**PIN Rules:**
- 4 digits only
- Locked out after 5 failed attempts (temporary)
- Can be reset via email OTP

---

## 5.4 Theme Toggle & 60-30-10 Design System

BayCIS features a fully unified theme and color architecture designed for visual clarity, reduced fatigue, and accessibility compliance.

### Dark & Light Mode Toggle
Click the **sun/moon icon** (☀️/🌙) in the unified header to switch between light and dark modes. Theme preference is persisted in browser `localStorage` and synchronized across all user portals (Administrator, Property Custodian, Borrower, and Welcome/Auth screens).

### The 60-30-10 Color Architecture
The interface strictly adheres to the 60-30-10 color distribution rule across both light and dark themes:

- **60% Dominant Base / Canvas (`--canvas`, `--bg-main`)**
  - **Light Mode:** Crisp, cool off-white canvas (`#f6f8fa`)
  - **Dark Mode:** Deep, low-contrast navy-slate canvas (`#0c1520`)
  - *Purpose:* Forms the expansive background foundation, keeping workspace clutter-free and preventing ocular strain.
- **30% Structural Surfaces & Typography (`--surface`, `--line`, `--ink`)**
  - **Light Mode:** Pure white cards/sidebars (`#ffffff`), soft boundary borders (`#e3e8ee`), dark charcoal ink (`#1e293b`).
  - **Dark Mode:** Elevated surface layers (`#132235`), hairline structural borders (`#1e334a`), crisp off-white typography (`#f1f5f9`).
  - *Purpose:* Provides structural definition for cards, data tables, modals, sidebars, and legible typography without harsh contrast jumps.
- **10% Purposeful Accents (`--accent-color`, `--accent-bg`)**
  - *Purpose:* Strictly reserved for high-priority interactive focal points — primary call-to-action buttons, active navigation pills, KPI trend badges, chart highlights, and PIN dot indicators.

### 6 Curated Color Palettes
Users can customize their accent color from **Settings > Appearance**:
- **Default** — Classic Royal Blue (`#0d6efd`)
- **Ocean** — Deep Cyan / Teal (`#0891b2`)
- **Emerald** — Forest Emerald Green (`#059669`)
- **Sunset** — Radiant Amber / Orange (`#ea580c`)
- **Lavender** — Modern Electric Purple (`#7c3aed`)
- **Rose** — Vibrant Magenta / Rose (`#e11d48`)

Each palette automatically recalibrates accent hues, hover states, and background tints across both light and dark modes to guarantee full **WCAG AA contrast compliance**.

### Bootstrap Loading Diagnostic

If interactive elements (modals, tabs, dropdowns) suddenly stop working on any admin page, the system has a built-in diagnostic:

1. The admin layout checks every 100ms (up to 10 seconds) whether Bootstrap JS has loaded
2. If Bootstrap fails to load from the CDN, a **red warning banner** appears at the bottom of the screen: *"Some interactive features are unavailable because Bootstrap failed to load. Please refresh the page or check your internet connection."*
3. The banner auto-dismisses after 8 seconds
4. A warning is also logged to the browser console for developers

**Common causes:**
- Network/firewall blocking `cdn.jsdelivr.net`
- Ad blocker or browser extension interfering with CDN scripts
- Temporary CDN outage

**Fix:** Refresh the page or check your internet connection.

---

## 5.5 Keyboard Shortcuts

### Global Shortcuts
| Key | Action | Available On |
|-----|--------|-------------|
| `/` | Focus search bar | Admin — all pages |
| `Escape` | Close modals, clear search | All |

### Inventory Page Shortcuts
| Key | Action |
|-----|--------|
| `/` | Focus search bar |
| `n` | Navigate to New Asset |
| `b` | Toggle bulk select all |
| `Escape` | Clear bulk selection |

The **?** help button next to "Add New" on the inventory page shows these shortcuts in a dropdown panel. `toggleShortcutsHelp()` toggles the panel visibility.

---

## 5.6 Offline Mode

If your internet connection drops:

- An **amber banner** appears at the top of the page: "You are offline. Some features may be unavailable until connection returns."
- You can continue viewing the current page
- When connection returns, the banner disappears automatically
- A success toast may appear: "Back Online — Connection restored."

> Previously, going offline redirected to a separate offline page. The new banner behavior is less disruptive and lets you continue working with cached content.

---

## 5.7 Live Tag Preview (Create/Edit Asset)

When creating or editing an asset, a **Live Tag Preview** panel appears on the right side of the screen. It shows a real-time rendering of the physical property tag as you fill out the form.

### Preview Features

**Real-Time Updates:**
- Item name, serial number, and property tag update instantly as you type
- Acquisition cost is formatted as Philippine Peso
- Acquisition date is displayed in standard format
- Accountable personnel name appears at the bottom
- The header bar color changes based on funding source:
  - 🟢 **Green** — LGU (Local Government Unit)
  - 🔵 **Blue** — MOOE (Maintenance & Other Operating Expenses)
  - 🩷 **Pink** — Donation

### Zoom Controls

Use the `+` and `−` buttons to zoom in and out of the preview:
- **Zoom Range:** 1.0x (original size) to 3.0x (3x magnification)
- **Zoom Step:** 0.1x per click
- **Display:** Current zoom level is shown between the buttons (e.g., "1.5x")

### Mouse Panning (when zoomed in)

- When zoomed above 1.0x, **move your mouse** over the preview to pan around the magnified tag
- The content follows your cursor proportionally — the part under your cursor stays visible
- Cursor changes from `grab` (1x) to `move` (zoomed in)
- When you move the mouse away, the view snaps back to the top-left corner

### Copy Actions

**Copy Tag Number (📋):**
1. Click the clipboard icon button
2. The property tag text is read from the `#preview-tag` element
3. **Primary path:** `navigator.clipboard.writeText()` — works on HTTPS and localhost
4. **Fallback path:** Creates a hidden textarea, selects the text, runs `document.execCommand('copy')` — works on HTTP
5. The icon briefly changes to a green checkmark (✓) for 1.5 seconds as confirmation
6. A success toast appears: "Copied — Property tag: {tag}"
7. If the element or clipboard is unavailable, the error is caught by a try/catch wrapper and logged to console — the page remains fully interactive

**Copy as Image (🖼️):**
1. Click the image icon button
2. The system captures the `#previewZoomContainer` at 2x resolution using the html2canvas library
3. **Primary path:** `navigator.clipboard.write()` with `ClipboardItem` — copies PNG image data to clipboard
4. **Fallback path:** If clipboard image is not supported (HTTP, older browser), generates a download link and triggers `property-tag.png` download
5. The button shows a spinning animation during processing
6. On success: green checkmark icon + toast "Tag image copied!"
7. On download fallback: toast "Tag image downloaded as PNG"
8. All operations are wrapped in try/catch — html2canvas CDN failures show an alert with "Check your internet connection"

**Code Architecture (standalone functions):**
```javascript
window.copyPreviewTag()       // Main entry: try/catch, clipboard chain, visual feedback
fallbackCopy(text)            // execCommand fallback for non-HTTPS
flashCopyBtn(fnName, cls, flashCls, msg)  // Dual selector matching for icon feedback
window.copyPreviewAsImage()   // Main entry: try/catch, html2canvas, clipboard/download chain
downloadCanvas(canvas, icon)  // Generates and auto-clicks download link
resetImgBtn(icon)             // Resets image button icon to default
```

All functions are defined at the **top level of the script** (outside any init function), ensuring they are available immediately when the page loads — not deferred to DOMContentLoaded. This eliminates the risk of onclick handlers silently failing due to init function early-return guards.

### Focus Mode (Create/Edit Asset Pages)

Focus Mode hides the sidebar, header, and preview panel so you can concentrate on filling out the form without distractions.

**How to toggle:**
1. On the **Create Asset** or **Edit Asset** page, find the **"Focus Mode"** button in the right-column preview panel (below the main action button, above Cancel)
2. Click the button to toggle Focus Mode ON/OFF
3. When ON, the sidebar, header, and preview panel are hidden; the form expands to a centered 800px width
4. When OFF, all UI elements return to their normal positions

**How to exit:**
- Click the **"Focus Mode"** button again
- Press the **Escape** key — this automatically exits Focus Mode

**Visual indicator:**
When Focus Mode activates, a blue pill appears at the top of the screen for 3 seconds: *"Focus Mode — click the Focus button or press Esc to exit"*

**State persistence:**
Focus Mode is a **per-session** convenience. It is NOT auto-restored on page reload — the layout always loads in its default state. This prevents confusion when revisiting the page.

---

## 5.8 Print Studio

The **Print Studio** (`/admin/print-studio`) is the all-in-one workspace for designing and printing property tags. It replaced single/batch print pages and supports unlimited tags per sheet, freeform placement, ratio-based scaling, exports, and Excel round-trips.

### Opening the Studio

Any of these entry points pre-load your selection:
1. **Inventory bulk-select** → click **"Print Studio"** in the floating action bar (up to 200 tags)
2. **Row grid icon** or **Asset Overview drawer → "Print in Studio"**
3. **After registering assets** → "Print Tag(s) in Studio" on the success screen
4. **Quick add search box** inside the Studio's left panel

### Tools Toolbar (below the header)

| Tool | What it does |
|------|-------------|
| **Auto-arrange** | Re-packs every tag into a clean grid across pages |
| **Blank page** | Appends an empty sheet |
| **Duplicate** | Copies the selected tag beside itself (Ctrl+D) |
| **Align ▾** | Snap selected tag to left / center-X / right / top / center-Y / bottom edge |
| **Center page** | Centers the selected tag on the sheet |
| **Front / Back** | Z-order the selected tag above/below others |
| **Delete** | Removes the selected tag from the sheet (Del key works too) |
| **Snap** | Toggle 1mm grid snapping — hold **Alt** while dragging to bypass temporarily |
| **Export ▾** | PDF, PNG, JSON, Excel (details below) |
| **Import Excel** | Load tags from an .xlsx file (details below) |

Keyboard: **arrow keys** nudge the selected tag 1mm (**Shift+Arrow** = 10mm).

### Interactive Canvas

- Every tag is **draggable** anywhere on its page; a live badge shows mm coordinates while moving
- Drag the **corner dot** to resize — contents scale proportionally so nothing is ever cut off
- Each sheet shows a screen-only **millimeter grid + numbered rulers** (never printed)
- Click a tag to select it; press **Escape**-style outside clicks to deselect

### Tag Size — Ratio to Original

Instead of fixed sizes, you pick a **ratio of the original design (150×90mm)**:
- Slider from 20%–200%, plus quick chips (40/50/75/100/150%)
- Live readout shows actual measurements (e.g., 50% ⇒ 75 × 45 mm)
- All text, barcode, and logo scale together with the tag — guaranteed no cropping

### Tag Content Options (per-tag capable)

With a tag selected, changes apply **only to that tag** (the panel announces "Editing TAG-XXX"); with nothing selected they become global defaults. Toggleable: serial number, acquisition cost/date, accountable personnel, validation signature row, DepEd logo, header text template (`{supplier}`), header color override, five independent font-size sliders, content boost, barcode width.

### Paper & Page Setup

Paper size (A4/Letter/Legal/A5/A6/custom mm), orientation, margins and gaps in millimeters. The print `@page` rule updates automatically — set browser Margins to **None** and Scale to **100%** when printing.

### Export Formats

| Format | Contents |
|--------|----------|
| **PDF** | Exact-size vector pages, one per sheet (~288 DPI tags) |
| **PNG** | Current page or all pages as high-resolution images |
| **JSON** | Full layout + placements for backup/re-use |
| **Excel (.xlsx)** | 3 sheets: *Property Tags* data table, *Layout Settings*, and *Tag Previews* with embedded PNG snapshots of each placed tag |

### Excel Import

Click **Import Excel** and choose an `.xlsx` file containing a Property Tag column (plus optional Copies column):
1. The system matches each tag string against inventory assets
2. Matched tags merge into your queue at the current ratio; copies are honored
3. Unmatched tags are listed explicitly after import

### Print Readiness Checklist

Before printing, a reminder appears covering the four settings that guarantee WYSIWYG output: matching paper size, Scale = 100%, Margins = None, Background graphics enabled.

---

## 5.9 Saved Filters & Column Toggle

### Saved Filters

**What they do:**
Save your current filter combination (status + category + search text) as a named preset for one-click access later.

**Saving a Filter:**
1. Set up your desired filters (e.g., status = "Borrowed", category = "IT Equipment")
2. Click the **bookmark icon** (🔖) in the filter pills bar
3. In the dropdown, click **"+ Save Current Filter"**
4. In the **"Name This Filter"** modal, enter a memorable name (e.g., "Borrowed IT Assets")
5. Press **Enter** or click **"Save Filter"** to save
6. Press **Escape** or click **Cancel** to dismiss

**Applying a Saved Filter:**
1. Click the bookmark icon to open the saved filters dropdown
2. Click any saved filter name to navigate to the filtered inventory view

**Deleting a Saved Filter:**
1. Open the saved filters dropdown
2. Click the **×** button next to the filter you want to delete

**Functions:**
```javascript
showSavedFilters()         → Opens/closes the saved filters dropdown, loads saved filters
saveCurrentFilter()        → Opens the themed "Name This Filter" modal
confirmFilterName()        → Saves the filter with the entered name to localStorage
cancelFilterName()         → Closes the naming modal without saving
applyFilter(index)         → Navigates to the inventory with the saved filter's parameters
deleteFilter(index)        → Removes a saved filter from localStorage
loadSavedFilters()         → Reads localStorage and renders the saved filters list
```

### Column Visibility Toggle

**What it does:**
Show or hide table columns (Category, Location) on the inventory page to focus on relevant data.

**Toggling Columns:**
1. Click the **columns icon** (📊) next to the "Clear" button
2. Check or uncheck columns to show/hide them:
   - ☑ Category — Show/hide the Category column
   - ☑ Location — Show/hide the Location column
3. Your preferences are saved to localStorage

**Resetting Columns:**
1. Click **"Reset"** in the column toggle menu to restore all columns to visible

**Functions:**
```javascript
toggleColumnMenu()     → Opens/closes the column visibility dropdown
toggleCol(colName)     → Toggles a specific column and saves preference to localStorage
resetColumns()         → Restores all columns to visible, clears saved preference
```

### Inline Status Quick-Edit

**What it does:**
Change an item's status directly from the inventory table without opening the edit page.

**Using:**
1. Click any **status badge** (e.g., "AVAILABLE", "BORROWED", "UNAVAILABLE", "GOOD CONDITION")
2. A small popover appears with alternative status options
3. Click the desired status:
   - **Available** — Item is ready for use (green)
   - **On Good Condition** — Item is tracked but not available for borrowing (blue)
   - **Borrowed** — Item is currently loaned out (yellow)
   - **Damaged** — Item has damage/broken parts (red)
   - **Maintenance** — Item needs repair (red)
   - **Disposed** — Item is disposed and automatically archived (gray)
4. The system updates the status via AJAX and refreshes the page
5. The badge color animates to reflect the new status

**Functions:**
```javascript
quickSetStatus(id, status, button)  → Updates the item's status via POST /admin/inventory/bulk-status
```

---

## ❓ Frequently Asked Questions

**Q: I forgot my password. What do I do?**
A: Contact your system administrator to reset your password.

**Q: I forgot my PIN. How do I reset it?**
A: On the login page, click "Forgot PIN?" and follow the OTP verification process. You can also reset it from Account Settings if you're logged in.

**Q: How do I print property tags?**
A: In the Inventory page, click the printer icon next to an item for a single tag, or use bulk select to print multiple tags at once. You can choose between Small, Medium, or Large tag sizes.

**Q: How do I zoom in on the live tag preview?**
A: On the Create or Edit Asset page, use the `+` button to zoom in (up to 3x) and the `−` button to zoom out (minimum 1x). When zoomed in, move your mouse over the preview to pan around.

**Q: Can I copy the property tag as an image?**
A: Yes! On the Create or Edit Asset page, click the image icon button next to the tag preview. The system captures and copies the tag as a high-resolution PNG image to your clipboard. (Note: Make sure required asset details—such as category, item name, acquisition date, cost, and location—are completed before copying so a complete official tag is generated.)

**Q: How do I save my current filter for later use?**
A: Set up your desired filters, click the bookmark icon, then click "+ Save Current Filter". Give it a name and it's saved for one-click access.

**Q: Why can't I see the camera/scanner option?**
A: The scanner requires:
- A secure connection (HTTPS) or localhost
- Camera permission granted in your browser
- A device with a camera (for mobile scanning)

**Q: Where do deleted items go?**
A: Deleted items are moved to the **Archived Assets** page. They can be restored or permanently deleted from there. Deleting an item requires your 4-digit PIN for security.

**Q: What happens when I set an item to "Disposed"?**
A: The item is automatically archived — it moves to the Archived Assets page. You don't need to separately delete it after changing the status.

**Q: How do I change between light and dark mode?**
A: Click the sun/moon icon in the top-right corner of any page.

**Q: Who receives notifications?**
A: All administrators receive notifications when borrowers take actions (request, return, confirm). Borrowers receive notifications when admins act on their requests.

**Q: How do I use the keyboard shortcuts on the inventory page?**
A: Press `/` to focus the search bar, `N` to create a new asset, `B` to toggle bulk select, and `Esc` to clear your selection. Click the `?` button for a quick reference.

**Q: What do the colors in the donut chart mean?**
A: Green = Available, Blue = On Good Condition, Yellow = Borrowed, Red = Damaged, Purple = Maintenance. Click any segment to go to the filtered inventory view.

**Q: How do I quickly change an item's status without editing?**
A: Click the status badge directly on the inventory table. A popover appears — select the new status and it updates instantly.

**Q: Can I show/hide columns on the inventory page?**
A: Yes! Click the columns icon next to the Clear button to toggle Category and Location column visibility. Your preferences are saved automatically.

---

*BayCIS — Bay Central Elementary School Inventory Management System*  
*Department of Education (DepEd)*  
*School ID: 108200*

---

# 🔑 TEMPORARY: Account Access Credentials

> ⚠️ **INTERNAL / TEMPORARY SECTION** — Created Aug 25, 2026 for the RBAC rollout.
> Remove this section before distributing this document to end users.

## Demo Accounts (seeded)

| Role | Name | Email (Login) | Teacher ID | Password |
|------|------|--------------|------------|----------|
| **Administrator** | Admin: JJ | `admin@example.com` | `0000-0000` | `default_secure_password` |
| **Property Custodian** *(new)* | Custodian: JJ | `custodian@bces.edu.ph` | `0000-0002` | `Custodian@123` |
| Borrower | Borrower: JJ | `borrower@bces.edu.ph` | `0000-0001` | `Borrower@123` |

**Notes**
- All three accounts are active and skip first-login OTP/password-change.
- On first login each account will be asked to **set a 4-digit PIN** (used to confirm sensitive actions).
- Login works with either **email + password** or the PIN login option.
- The admin password can be overridden in `.env` via `INITIAL_ADMIN_EMAIL` / `INITIAL_ADMIN_PASSWORD`, then re-run `php artisan db:seed`.

## What the Property Custodian Can Do

✅ Command Center dashboard, Inventory CRUD, batch creation & property tags, barcode printing/scanning, approve/reject borrow requests, verify returns, consumable stock management & issuance, reports, archive restore, audit logs (**read-only**), own account settings.

❌ Cannot: force-delete archived assets permanently, manage users, change system/inventory settings, or back up/restore the database (admin-only — those links are hidden from the custodian sidebar).

---

# 🔄 Account Lifecycle (Added Aug 25, 2026)

## Self-Registration → OTP → Admin Activation

1. On the login screen, click **"Need an account? Register here"**
2. Fill out the registration form (Full Name, Email, Teacher ID, Password)
3. A **6-digit OTP** is sent to your email — enter it on the verification page
4. After verification your account is **queued for administrator approval** (you cannot sign in yet)
5. An administrator approves it in **User Management** (filter: *Pending Activation*) → you receive a notification and can sign in

## Requesting Account Deactivation or Deletion

Available in **Account Settings → Danger Zone** (both Borrower and Staff portals):

| Request | What Happens |
|---------|-------------|
| **Request Deactivation** | Admin approval required. Account is disabled; access can be restored later. |
| **Request Deletion** | PIN-confirmed. Requires admin approval, then a **60-day grace period** starts. Sign in any time during those 60 days and click **Cancel My Request** to keep the account. |

**Admin actions (User Management → row menu):** Approve/Reject activation, Approve/Reject deactivation, Approve/Reject deletion, Cancel Scheduled Deletion.

**Important guarantees**
- Administrators can **no longer delete accounts directly** — deletion always originates from the user's own request.
- During the 60-day buffer the account cannot log in (password **or** PIN), but an admin can restore it.
- When the purge runs (daily 3:00 AM scheduler, `accounts:purge-expired`), the account's personal data is permanently scrubbed while **all ongoing and closed transaction records are fully preserved** for school auditing.
