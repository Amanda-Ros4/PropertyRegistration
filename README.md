# PropertyRegistration

A complete real estate registration system built with Laravel 12, Vue 3, Inertia.js (SSR), Jetstream, PrimeVue, and multi-language support.

---

## Stack

| Layer        | Technology                          |
|--------------|-------------------------------------|
| Backend      | Laravel 12, PHP 8.2+                |
| Auth         | Laravel Jetstream (Fortify + Sanctum)|
| Frontend     | Vue 3, Inertia.js v2 (SSR enabled)  |
| UI Library   | PrimeVue 4 (Aura theme)             |
| Styling      | Tailwind CSS v3                     |
| i18n         | laravel-vue-i18n                    |
| Database     | MySQL                               |
| Build        | Vite 7 + SSR                        |

---

## Requirements

- PHP 8.2+
- Node.js 18+
- MySQL 8.0+
- Composer 2.x

---

## Setup Instructions

### 1. Clone & Install PHP Dependencies

```bash
git clone <repository-url>
cd PropertyRegistration
composer install
```

### 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=properties
DB_USERNAME=root
DB_PASSWORD=your_password

VITE_APP_NAME="PropertyReg"
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Database Setup

```bash
php artisan migrate
```

Optionally seed demo data (creates `admin@example.com` / `password`):

```bash
php artisan db:seed
```

### 5. Build Frontend Assets

For production:
```bash
npm run build
```

For development with hot reload:
```bash
npm run dev
```

### 6. Start the Application

```bash
php artisan serve
```

Visit `http://localhost:8000` and register or log in with the seeded account.

---

## Running the Dev Server (All-in-One)

The project includes a `dev` composer script that runs all processes concurrently:

```bash
composer dev
```

This starts: `php artisan serve`, queue listener, Pail log viewer, and `npm run dev` simultaneously.

---

## Architecture Overview

### Backend

```
app/
├── Enums/
│   └── Gender.php               # Backed string enum: male, female, other, prefer_not_to_say
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── PersonController.php
│   │   └── PropertyController.php
│   ├── Middleware/
│   │   └── HandleInertiaRequests.php  # Shares flash, locale, ziggy props
│   └── Requests/
│       ├── People/
│       │   ├── StorePersonRequest.php
│       │   └── UpdatePersonRequest.php
│       └── Properties/
│           ├── StorePropertyRequest.php
│           └── UpdatePropertyRequest.php
├── Models/
│   ├── User.php
│   ├── Person.php               # Scopes: forUser, search
│   └── Property.php             # Scopes: forUser, search, filterByPerson
├── Policies/
│   ├── PersonPolicy.php         # Auto-discovered by Laravel
│   └── PropertyPolicy.php
├── Rules/
│   └── ValidCpf.php             # Full CPF digit validation
└── Services/
    ├── PersonService.php        # Business logic + deletion protection
    └── PropertyService.php
```

### Frontend

```
resources/js/
├── app.js                       # Client entry point
├── ssr.js                       # SSR entry point (eager i18n loading)
├── composables/
│   ├── useTheme.js              # Dark/light toggle, localStorage persistence
│   └── useLocale.js             # Language switcher + PrimeVue locale maps
├── plugins/
│   ├── primevue.js              # PrimeVue config (Aura preset, dark mode)
│   └── i18n.js                  # laravel-vue-i18n setup (SSR-safe)
├── Layouts/
│   └── AppLayout.vue            # Main layout with nav, theme, lang, flash toasts
├── Components/
│   ├── PageHeader.vue           # Page title + create/back buttons
│   ├── FilterBar.vue            # Search input + optional select filter
│   ├── EmptyState.vue           # Empty table state component
│   ├── DeleteConfirmation.vue   # ConfirmDialog wrapper
│   ├── FormCard.vue             # Form wrapper card
│   └── FormField.vue            # Label + slot + error display
└── Pages/
    ├── Dashboard.vue
    ├── People/
    │   ├── Index.vue
    │   ├── Create.vue
    │   └── Edit.vue
    └── Properties/
        ├── Index.vue
        ├── Create.vue
        └── Edit.vue
```

---

## Multi-tenancy

Every `Person` and `Property` record is scoped to `user_id`. The system enforces:

- All queries use `scopeForUser($userId)` — users can never see each other's data
- Form Requests validate `person_id` belongs to the authenticated user
- Policies (`PersonPolicy`, `PropertyPolicy`) enforce ownership on edit/delete
- CPF uniqueness is scoped **per tenant** (`UNIQUE(user_id, cpf)`)

---

## Validation Rules

### Person
| Field       | Rules                                              |
|-------------|---------------------------------------------------|
| name        | required, string, max:255                         |
| birth_date  | required, date, before_or_equal:today             |
| cpf         | required, ValidCpf rule, unique per user_id       |
| gender      | required, Gender enum                             |
| phone       | nullable, string, max:20                          |
| email       | nullable, email, max:255                          |

### Property
| Field        | Rules                                             |
|--------------|--------------------------------------------------|
| person_id    | required, exists in people scoped to user_id     |
| street       | required, string, max:255                        |
| number       | required, string, max:20                         |
| neighborhood | required, string, max:255                        |
| complement   | nullable, string, max:255                        |

---

## Deletion Rules

- **Person with active Properties**: deletion is **blocked** with a clear error message
- **Property**: soft-deleted safely
- Both models use `SoftDeletes`; hard deletion is never exposed in the UI

---

## Internationalization

Languages supported: **English** (`en`), **Português** (`pt_BR`), **Español** (`es`)

- Translation files: `lang/en.json`, `lang/pt_BR.json`, `lang/es.json`
- Library: `laravel-vue-i18n` v2.x
- SSR-safe: uses `import.meta.glob(..., { eager: true })` for synchronous resolution
- PrimeVue locale maps for calendar, paginator, and UI components per language
- User preference persisted in `localStorage`
- Language switcher available in the navigation bar

---

## Dark / Light Mode

- PrimeVue configured with `darkModeSelector: '.dark'`
- Tailwind configured with `darkMode: 'class'`
- Toggle adds/removes `.dark` class on `<html>`
- Preference persisted in `localStorage`
- Respects OS preference on first visit

---

## Suggested Commit Steps

```
git commit -m "feat: initial Laravel 12 + Jetstream + Inertia SSR setup"
git commit -m "feat: install and configure PrimeVue 4 + laravel-vue-i18n"
git commit -m "feat: add dark/light theme and multi-language support"
git commit -m "feat: create migrations for people and properties tables"
git commit -m "feat: add Person and Property models with tenant scopes"
git commit -m "feat: add Gender enum and ValidCpf validation rule"
git commit -m "feat: implement PersonService with deletion protection"
git commit -m "feat: implement PropertyService"
git commit -m "feat: add PersonController and PropertyController with full CRUD"
git commit -m "feat: add PersonPolicy and PropertyPolicy for authorization"
git commit -m "feat: rebuild AppLayout with PrimeVue nav, theme toggle, lang switcher"
git commit -m "feat: create People pages (Index, Create, Edit)"
git commit -m "feat: create Properties pages (Index, Create, Edit)"
git commit -m "feat: create Dashboard with stats and recent records"
git commit -m "feat: add database seeders with demo data"
git commit -m "docs: add README with full setup and architecture guide"
```

---

## Test Credentials (after seeding)

| Field    | Value               |
|----------|---------------------|
| Email    | admin@example.com   |
| Password | password            |
