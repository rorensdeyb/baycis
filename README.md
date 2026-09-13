<div align="center">

# 🏫 BayCIS — Bay Central Inventory System

**Enterprise-Grade Inventory Management for Educational Institutions**

![Laravel](https://img.shields.io/badge/Laravel-13.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![PWA](https://img.shields.io/badge/PWA-Enabled-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)

[📖 Documentation](.md/Documentation.md) • [🚀 Quick Start](#-quick-start) • [📋 Features](#-feature-overview) • [🏗️ Architecture](#️-system-architecture) • [📊 Report Bug]()

</div>

---

## 📌 Table of Contents

- [Overview](#-overview)
- [Problem Statement](#-problem-statement)
- [Feature Overview](#-feature-overview)
- [Technology Stack](#-technology-stack)
- [System Architecture](#️-system-architecture)
- [Quick Start](#-quick-start)
- [User Roles](#-user-roles)
- [Security & Compliance](#-security--compliance)
- [Performance](#-performance)
- [Development Guide](#-development-guide)
- [Deployment](#-deployment)
- [Roadmap](#-roadmap)
- [Team](#-team)
- [License](#-license)

---

## 📋 Overview

**BayCIS** (Bay Central Inventory System) is a full-featured, web-based inventory management platform developed for **Bay Central Elementary School (BCES)**. It replaces manual logbook and spreadsheet-based inventory processes with a centralized, real-time digital system that streamlines asset tracking, borrowing, returns, and reporting across the entire institution.

Designed with a **mobile-first, PWA-enabled architecture**, BayCIS works reliably even in low-connectivity environments — a critical requirement for school deployments. The system serves two distinct user portals (Admin and Borrower) with role-appropriate interfaces, from a full command-center dashboard to a simplified self-service borrowing experience.

### 🎯 Core Objectives

| Objective | Description |
|-----------|-------------|
| **Centralization** | Single source of truth for all school assets, supplies, and equipment |
| **Real-Time Visibility** | Live status monitoring of asset location, condition, and assignment |
| **Error Reduction** | Eliminate manual data entry inconsistencies with structured workflows |
| **Accountability** | Complete audit trail for every asset transaction from acquisition to disposal |
| **Efficiency** | Automated property tag generation, batch processing, and one-click reporting |
| **Accessibility** | Dual-portal design with PWA offline support for campus-wide access |

---

## ❗ Problem Statement

Prior to BayCIS, the school relied on:

- **Paper logbooks** for borrow/return tracking — frequently lost or damaged
- **Spreadsheet files** for inventory records — version conflicts, no access control
- **Manual property tag generation** — inconsistent formatting, prone to typos
- **No audit trail** — impossible to trace who had which asset and when
- **Slow report generation** — compiling quarterly reports took days

These manual processes led to inaccurate records, difficulty tracking asset locations, frequent human errors, risk of asset loss, and limited access to reliable data for decision-making.

---

## ⭐ Feature Overview

### 🔐 Authentication & Security

| Feature | Details |
|---------|---------|
| Dual Authentication | Login via Email or Teacher ID |
| PIN Security | 4-digit transaction PIN with brute-force lockout (3 attempts → 15 min freeze) |
| OTP Verification | Email-based OTP for password changes, PIN resets, and new account verification |
| Rate Limiting | Throttled endpoints for login, OTP, PIN, and registration |
| Session Management | Secure auth interceptor pattern prevents URL-bypass attacks |
| Security Headers | X-Frame-Options, X-Content-Type-Options, HSTS, Permissions-Policy, Referrer-Policy |

### 👑 Admin Portal

| Feature | Details |
|---------|---------|
| **Command Center Dashboard** | 6-metric sticky KPI bar, SVG donut chart (asset distribution), priority alert feed, borrower leaderboard, floating action button |
| **Inventory Management** | Full CRUD with batch creation, auto-generated DepEd property tags, category/location/supplier classification |
| **Barcode Ecosystem** | Inline SVG barcodes (JsBarcode CODE128), USB scanner support, mobile camera scanning (Html5Qrcode) |
| **Borrow Request Workflow** | Approve/reject with admin remarks, QR code verification, status tracking |
| **Return Verification** | Condition assessment (Good/Damaged/Needs Repair), automatic inventory status update |
| **Consumable Issuance** | Stock management, reorder alerts, issuance history |
| **Reporting Suite** | Summary ledger, borrower log, low-stock alerts, date-range filtering, monochrome print engine |
| **Soft-Delete Archive** | Restore or permanently delete assets with full history preservation |
| **System Settings** | Institutional identity, inventory thresholds, display density, user management, database backup/restore |
| **Global Search** | Page navigation search bar indexing all admin routes |
| **Enterprise Dashboard** | Sticky KPI bar, trend arrows, SVG donut chart, skeleton loaders, priority alert feed, borrower leaderboard |
| **Notification Auto-Polling** | Real-time badge updates and dropdown refresh every 30 seconds |

### 👤 Borrower Portal

| Feature | Details |
|---------|---------|
| **Self-Service Requests** | 3-step wizard: select item → review details → PIN-confirm submission |
| **QR Code Generation** | Unique QR per request for admin verification |
| **Return Initiation** | Declare condition, submit for admin verification |
| **Consumable Requests** | Request supplies, confirm receipt, view issuance history |
| **Transaction History** | Searchable, filterable log of all borrows, returns, and issuances |
| **Account Settings** | Profile, password, and PIN management |
| **Mobile-First UI** | Bottom navigation, card-based views, touch-optimized interactions |

### 🌐 Cross-Platform Features

| Feature | Details |
|---------|---------|
| **PWA Support** | Service worker caching, offline fallback page, manifest.json for install prompts |
| **Light/Dark Mode** | CSS variable theming, localStorage persistence, flash-prevention script |
| **Toast Notifications** | Animated slide-in toasts for success/error/warning/info with auto-dismiss |
| **Responsive Design** | Desktop sidebar → mobile bottom nav, card-based tables on small screens |
| **Accessibility** | Screen-reader friendly, keyboard navigable, high-contrast mode support |

---

## 🛠️ Technology Stack

### Backend

| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | ^8.3 | Server-side scripting language |
| **Laravel** | ^13.0 | MVC framework — routing, ORM, Blade templating, middleware |
| **MySQL** | 8.0+ | Primary relational database |
| **SQLite** | 3.x | Development/testing fallback |

### Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| **HTML5** | — | Semantic markup, accessibility |
| **CSS3** | — | Custom properties theming, responsive grid, animations |
| **Bootstrap** | 5.3 | Layout grid, modals, dropdowns, utility classes |
| **Vanilla JS** | ES6+ | DOM manipulation, Fetch API, async workflows |
| **Bootstrap Icons** | 1.11 | Icon set (bi-) |
| **JsBarcode** | 3.11 | Client-side CODE128 SVG barcode generation |
| **Html5Qrcode** | — | Mobile camera barcode/QR scanning |

### Infrastructure

| Tool | Purpose |
|------|---------|
| **Composer** | PHP dependency management |
| **NPM / Vite** | Frontend asset bundling (reserved for future expansion) |
| **Service Workers** | PWA offline caching |
| **Git** | Version control |

---

## 🏗️ System Architecture

### Directory Structure

```
BayCIS/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # 10 controllers (MVC logic)
│   │   ├── Middleware/           # SecurityHeaders middleware
│   │   └── ...
│   ├── Models/                   # 11 Eloquent models
│   └── Observers/                # ItemObserver, TransactionObserver
├── bootstrap/app.php             # Framework boot + middleware registration
├── config/                       # App, database, cache, session config
├── database/
│   ├── migrations/               # 20+ schema migrations
│   └── seeders/                  # AdminUser, Category seeders
├── public/
│   ├── css/                      # admin.css, borrower.css, welcome.css
│   ├── js/                       # admin.js, borrower.js, loader.js
│   └── sw.js                     # Service worker
├── resources/views/
│   ├── admin/                    # 10+ admin views (dashboard, inventory, etc.)
│   ├── borrower/                 # 6 borrower views
│   ├── layouts/                  # admin.blade.php, borrower.blade.php
│   └── auth/                     # Login, OTP, password views
├── routes/
│   ├── web.php                   # All application routes
│   └── api.php                   # RESTful API routes
└── .md/
    ├── Documentation.md          # Full project history (59 phases)
    ├── TODO.md                   # Analysis & optimization roadmap
    └── suggestions.md            # Feature improvement proposals
```

### Data Model (Core Entities)

```
User ──hasMany──> BorrowRequest ──belongsTo──> Item
User ──hasMany──> Notification
Item ──belongsTo──> Category
Item ──belongsTo──> Location
Item ──belongsTo──> Supplier
BorrowRequest ──belongsTo──> Item
BorrowRequest ──belongsTo──> User
ConsumableStock ──hasMany──> ConsumableIssuance
ConsumableIssuance ──belongsTo──> User
AuditLog (polymorphic event log)
```

### Authentication Flow

```
Login (Email / Teacher ID)
    │
    ├── Admin-created user? ──Yes──> Force Password Change
    │                                       │
    │                                   OTP Verification (email)
    │                                       │
    │                                   Set Password → Login
    │
    └── PIN Setup (first login)
            │
        PIN Verification (subsequent sensitive actions)
            │
        3 failed attempts? ──Yes──> 15-minute lockout
```

### Transaction Lifecycle

```
Borrower submits request
    │
Admin approves (QR code verification)
    │
Item status → "borrowed"
    │
Borrower initiates return (declares condition)
    │
Admin verifies return (assesses final condition)
    │
Item status → "available" / "damaged" / "maintenance"
    │
Audit log + notification created at every step
```

---

## 🚀 Quick Start

### Prerequisites

- **PHP** >= 8.3 (with extensions: mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json, fileinfo, bcmath, gd)
- **Composer** >= 2.5
- **Node.js** >= 20 & **NPM** >= 10
- **MySQL** 8.0+ or SQLite (development)

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/your-org/baycis.git
cd baycis

# 2. Install PHP dependencies
composer install

# 3. Install frontend dependencies
npm install

# 4. Configure environment
cp .env.example .env
# Edit .env with your database credentials

# 5. Generate application key
php artisan key:generate

# 6. Run database migrations
php artisan migrate

# 7. Build frontend assets
npm run build

# 8. Start development server
php artisan serve
```

Open **http://127.0.0.1:8000** in your browser.

> **Tip:** Use `composer run setup` to run steps 2–7 automatically on a fresh install.

### Default Database

For development, the `.env.example` defaults to **SQLite** (`database/database.sqlite`). For production, switch to **MySQL**:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=baycis
DB_USERNAME=root
DB_PASSWORD=your_password
```

---

## 👥 User Roles

### Administrator
- Full access to all inventory operations
- User management (create, edit, deactivate accounts)
- System configuration and settings
- Report generation and data export
- Database backup and restore

### Borrower (Teacher / Staff)
- View available inventory
- Submit borrow requests with PIN confirmation
- Initiate returns with condition declaration
- Request consumable supplies
- View personal transaction history
- Manage own profile, password, and PIN

---

## 🛡️ Security & Compliance

### Implemented Measures

| Measure | Implementation |
|---------|---------------|
| **Mass Assignment Protection** | Explicit `$fillable` arrays on all 11 models |
| **Rate Limiting** | `throttle` middleware on login (5/min), registration (3/5min), PIN/OTP endpoints |
| **PIN Brute-Force Protection** | Dual-layer lockout (login PIN: 3 fails → 15 min global freeze; transaction PIN: 3 fails → 15 min localized freeze) |
| **OTP Security** | 6-digit codes with 10-minute expiry, removed from server logs |
| **CSRF Protection** | Laravel's built-in CSRF token on all POST forms |
| **Security Headers** | X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy, Permissions-Policy, HSTS |
| **Authentication Interceptor** | Revokes session on OTP/password-change pages to prevent URL bypass |
| **Soft Deletes** | Assets are soft-deleted; permanent deletion requires explicit confirmation |
| **Audit Trail** | Every CRUD operation logged with user, timestamp, and description |

---

## ⚡ Performance

| Optimization | Status |
|-------------|--------|
| Cache Driver | File-based (eliminates DB query per cache operation) |
| Database Indexes | Composite indexes on borrow_requests, consumable_issuances, notifications, audit_logs, items, transactions |
| Service Worker Caching | Pre-caches critical CSS/JS for offline loading |
| Resource Hints | Preconnect + DNS-prefetch for CDN origins (jsdelivr, quickchart) |
| Query Optimization | Aggregated counts instead of collection loading |
| Pagination | Server-side pagination on all list views |
| Debounced Search | 1000ms debounce on live search inputs |

---

## 💻 Development Guide

### Session Code Convention

Development progress is tracked using session codes in the documentation:

| Prefix | Focus Area |
|--------|------------|
| **U** (UI) | Frontend architecture, UI/UX, responsive layouts, PWA |
| **B** (Backend) | Server logic, database, API routing, security |

See [Documentation.md](.md/Documentation.md) for the complete 107-phase development history.

### Coding Standards

- **PHP:** PSR-4 autoloading, PSR-12 style (enforced via Laravel Pint)
- **JavaScript:** Vanilla ES6+ — no jQuery dependency
- **CSS:** Custom properties via `:root` + `[data-theme="dark"]`, BEM-inspired naming
- **Database:** Descriptive migration names, foreign key constraints, composite indexes

### Testing

```bash
# Run PHPUnit tests
php artisan test

# Or using Composer
composer run test
```

---

## 🚢 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Generate fresh `APP_KEY` (never share)
- [ ] Configure **MySQL** with a dedicated database user (not root)
- [ ] Set strong **database password** and update `.env`
- [ ] Configure **mail** settings (SMTP) for OTP delivery
- [ ] Disable debug mode: `APP_DEBUG=false`
- [ ] Set `CACHE_STORE=file` (already configured)
- [ ] Run `php artisan route:cache`, `php artisan config:cache`
- [ ] Set proper file permissions (`storage/`, `bootstrap/cache/`)
- [ ] Enable HTTPS (SSL certificate)
- [ ] Configure cron for scheduled tasks (`php artisan schedule:run`)
- [ ] Test service worker registration and offline fallback

### System Requirements (Production)

| Resource | Minimum | Recommended |
|----------|---------|-------------|
| CPU | 1 vCPU | 2 vCPU |
| RAM | 1 GB | 2 GB |
| Storage | 10 GB SSD | 20 GB SSD |
| PHP | 8.3 | 8.3+ |
| MySQL | 8.0 | 8.0+ |
| Web Server | Apache / Nginx | Nginx with PHP-FPM |

---

## 🗺️ Roadmap

### Recently Completed

- ✅ Security hardening (mass assignment, OTP logs, rate limiting, audit fallback)
- ✅ Performance optimization (file cache, database indexes, service worker v6)
- ✅ Security headers middleware + PWA manifest
- ✅ Toast notification system + meta tags + resource hints
- ✅ Enterprise dashboard with KPI bar, donut chart, alert feed, leaderboard, FAB
- ✅ Notification auto-polling system (30-second interval)
- ✅ Borrower mobile responsiveness overhaul

### Planned (from [suggestions.md](.md/suggestions.md))

| Priority | Feature |
|----------|---------|
| 🔴 Critical | Bulk operations panel, mobile inventory card view |
| 🟡 High | Inventory CSV import/export, keyboard shortcuts, quick-duplicate asset |
| 🟡 High | Advanced filter bar with pill-style status filters |
| 🟢 Medium | Scanner-first workflow, batch print enhancements |
| 🔵 Future | Drag-and-drop row reorder, scheduled PDF reports |

---

## 👥 Team

| Name | Role |
|------|------|
| **John Oliver R. Valenzuela** | Lead Developer & System Architect |
| **Lawrence Dave P. Tolentino** | UI/UX Design & Documentation |
| **Cathlene Tolentino** | UI/UX Design & Documentation |

---

## 📚 References

- Ahmad, A. (2023) — *e-AIMSS: Asset Inventory Management System for Schools*
- Bambang, B., et al. (2024) — *Web-Based Inventory Information System*
- Ikhwan, A., et al. (2025) — *Implementation of Inventory Management in Education*
- Okorie, S. N., & Jibril, A. K. (2023) — *Asset Tracking System for Public Institutions*
- Makmun, S., & Marif, H. (2025) — *Development of School Inventory Applications*
- Aryani, H. F., & Ali, I. (2025) — *Inventory Management with QR Code Integration*

---

<div align="center">

**Bay Central Elementary School — Inventory Management System**

Built with ❤️ for public education

[📖 Full Documentation](.md/Documentation.md) • [📋 TODO & Analysis](.md/TODO.md) • [💡 Suggestions](.md/suggestions.md)

</div>
