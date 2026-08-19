# PropertyRegistration

> **Languages:** English | [Português (BR)](README.pt-BR.md)

Real estate registration system built with Laravel 12, Vue 3, Inertia.js (SSR), Jetstream, PrimeVue, and multi-language support.

---

## Stack

| Layer        | Technology                          |
|--------------|-------------------------------------|
| Backend      | Laravel 12, PHP 8.2+                |
| Auth         | Laravel Jetstream (Fortify + Sanctum) |
| Frontend     | Vue 3, Inertia.js v2 (SSR)          |
| UI           | PrimeVue 4 (Aura theme)             |
| Styling      | Tailwind CSS v3                     |
| i18n         | laravel-vue-i18n                    |
| Database     | MariaDB (`mariadb` driver)          |
| Auditing     | owen-it/laravel-auditing            |
| Reports      | barryvdh/laravel-dompdf             |
| Build        | Vite 7 + SSR                        |

> **Note:** The project specification mentions Vuetify; the implemented UI library is **PrimeVue**, integrated throughout the frontend.

---

## Requirements

- PHP 8.2+ (document uploads: `upload_max_filesize` ≥ 16M and `post_max_size` ≥ 20M recommended)
- Node.js 18+
- MariaDB 10.6+ (or compatible MySQL 8.0+)
- Composer 2.x

---

## Installation

### 1. Clone and install PHP dependencies

```bash
git clone https://github.com/Amanda-Ros4/PropertyRegistration.git
cd PropertyRegistration
composer install
```

Repository: [github.com/Amanda-Ros4/PropertyRegistration](https://github.com/Amanda-Ros4/PropertyRegistration)

### 2. Environment

**Linux / macOS / Git Bash:**

```bash
cp .env.example .env
php artisan key:generate
```

**Windows (PowerShell or CMD):**

```powershell
copy .env.example .env
php artisan key:generate
```

Configure the database and locale in `.env`:

```env
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=property_registration
DB_USERNAME=root
DB_PASSWORD=your_password

VITE_APP_NAME="PropertyReg"
```

### 3. Create the database

Make sure MariaDB is running, then create the database:

```sql
CREATE DATABASE property_registration CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

For local development without MariaDB, use SQLite instead (see comments in `.env.example`).

### 4. Node dependencies

```bash
npm install
```

### 5. Database migrations and seed

```bash
php artisan migrate
php artisan db:seed
```

### 6. Assets

Production:

```bash
npm run build
```

Development:

```bash
npm run dev
```

### 7. Server

```bash
php artisan serve
```

Or run all processes together (includes PHP upload limits for property documents):

```bash
composer dev
```

Visit `http://localhost:8000` and sign in with a seeded account. **Public registration is disabled** — users are created by administrators.

---

## Access profiles

| Profile              | Users module | Audit | People / properties | Reports |
|----------------------|:------------:|:-----:|---------------------|:-------:|
| IT Administrator     | Full         | Yes   | **All records**     | Yes     |
| System Administrator | Manage attendants only | Yes | **Own records only** | Own records only |
| Attendant            | No access    | No    | Own records only    | Own records only |

### Profile details

- **IT Administrator** — Full access; can edit user emails and profiles; sees all data.
- **System Administrator** — Creates and edits **attendants only**; cannot create or edit IT administrators; manages users and audit; people and properties are scoped to their own `user_id`.
- **Attendant** — Creates and views only their own people and properties; no access to users or audit.

### General rules

- Users **cannot be deleted** — only activated or deactivated.
- People and properties **cannot be deleted** through the UI.
- Property **area and status** change only through **endorsements**.
- CPF is **locked after user creation** (except IT Administrator can change profiles).

---

## Modules

- **Users** — create, edit, activate/deactivate; no deletion
- **People** — registration and search with filters
- **Properties** — registration, documents, endorsements (area and status)
- **Endorsements** — only way to change property area and status
- **PDF reports** — synthetic and individual, translated by locale
- **Audit** — listing, filters, and details (Laravel Auditing; IT and System administrators only)

---

## Multi-tenancy

`Person` and `Property` records belong to a `user_id`. Attendants and System Administrators see only their own data. The **IT Administrator** sees all records via `canAccessAllRecords()`.

---

## Internationalization

Languages: **Portuguese** (`pt_BR`), **English** (`en`), **Spanish** (`es`).

Translation files: `lang/pt_BR.json`, `lang/en.json`, and `lang/es.json`.

The in-app language switcher persists the choice in a cookie (`app_locale`).

---

## Tests

Automated tests use in-memory SQLite (configured in `phpunit.xml`):

```bash
php artisan test
```

---

## Credentials (after seed)

| Profile              | Email                 | Password |
|----------------------|-----------------------|----------|
| IT Administrator     | ti@example.com        | password |
| System Administrator | admin@example.com     | password |
| Attendant            | atendente@example.com | password |

---

## License

MIT — see [composer.json](composer.json).
