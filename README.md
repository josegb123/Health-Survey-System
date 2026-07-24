<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-red?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/Livewire-4.x-pink?style=for-the-badge&logo=livewire" alt="Livewire">
  <img src="https://img.shields.io/badge/PHP-8.3-%23777BB4?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/SQLite-003B57?style=for-the-badge&logo=sqlite" alt="SQLite">
  <img src="https://img.shields.io/badge/Flux-2.x-cyan?style=for-the-badge" alt="Flux">
  <img src="https://img.shields.io/badge/DomPDF-3.x-red?style=for-the-badge" alt="DomPDF">
  <img src="https://img.shields.io/badge/license-MIT-green?style=for-the-badge" alt="MIT">
</p>

<div align="center">
  <h1>📋 Sistema de Encuestas de Salud — Health Survey System</h1>
  <p><strong>Automatización de encuestas de satisfacción clínica, reportes estadísticos y generación de reportes tipo 3 para el Ministerio de Salud de Colombia</strong></p>
</div>

---

## 📑 Table of Contents / Tabla de Contenido

- [📋 Sistema de Encuestas de Salud — Health Survey System](#-sistema-de-encuestas-de-salud--health-survey-system)
    - [📑 Table of Contents / Tabla de Contenido](#-table-of-contents--tabla-de-contenido)
- [🚀 Project Overview / Visión General](#-project-overview--visión-general)
    - [🇪🇸 Español](#-español)
    - [🇬🇧 English](#-english)
- [🏗️ Architecture / Arquitectura](#️-architecture--arquitectura)
    - [Tech Stack / Stack Tecnológico](#tech-stack--stack-tecnológico)
    - [Database Schema / Esquema de Base de Datos](#database-schema--esquema-de-base-de-datos)
    - [Directory Structure / Estructura del Proyecto](#directory-structure--estructura-del-proyecto)
- [📐 Design Patterns / Patrones de Diseño](#-design-patterns--patrones-de-diseño)
    - [Action Pattern](#action-pattern)
    - [Service Layer](#service-layer)
    - [Singleton Configuration](#singleton-configuration)
    - [Livewire Component Architecture](#livewire-component-architecture)
- [🔌 API Endpoints](#-api-endpoints)
- [📦 Reports System / Sistema de Reportes](#-reports-system--sistema-de-reportes)
    - [1. Excel Survey Report / Reporte Excel de Encuestas](#1-excel-survey-report--reporte-excel-de-encuestas)
    - [2. Statistics PDF Report / Reporte PDF Estadístico](#2-statistics-pdf-report--reporte-pdf-estadístico)
    - [3. Ministry of Health Report (Type 3) / Reporte Ministerio de Salud (Tipo 3)](#3-ministry-of-health-report-type-3--reporte-ministerio-de-salud-tipo-3)
- [⚙️ Setup \& Installation / Instalación y Configuración](#️-setup--installation--instalación-y-configuración)
    - [Prerequisites / Requisitos](#prerequisites--requisitos)
    - [Quick Start (1-command setup)](#quick-start-1-command-setup)
    - [Manual Step-by-Step / Paso a Paso Manual](#manual-step-by-step--paso-a-paso-manual)
- [🔐 Authentication / Autenticación](#-authentication--autenticación)
- [🤖 Frontend (Headless API)](#-frontend-headless-api)
- [🧪 Testing / Pruebas](#-testing--pruebas)
    - [Running Tests / Ejecutar Pruebas](#running-tests--ejecutar-pruebas)
- [🗑️ Data Lifecycle / Ciclo de Vida de Datos](#️-data-lifecycle--ciclo-de-vida-de-datos)
    - [Survey Purge System / Sistema de Purga de Encuestas](#survey-purge-system--sistema-de-purga-de-encuestas)
- [🛡️ Security / Seguridad](#️-security--seguridad)
    - [Cloudflare Turnstile Integration](#cloudflare-turnstile-integration)
    - [Rate Limiting / Limitación de Peticiones](#rate-limiting--limitación-de-peticiones)
    - [Maintenance Mode / Modo Mantenimiento](#maintenance-mode--modo-mantenimiento)
- [📄 PRD — Product Requirements Document / Documento de Requisitos del Producto](#-prd--product-requirements-document--documento-de-requisitos-del-producto)
    - [1. Purpose / Propósito](#1-purpose--propósito)
    - [2. Scope / Alcance](#2-scope--alcance)
    - [3. Functional Requirements / Requisitos Funcionales](#3-functional-requirements--requisitos-funcionales)
    - [4. Non-Functional Requirements / Requisitos No Funcionales](#4-non-functional-requirements--requisitos-no-funcionales)
    - [5. User Roles / Roles de Usuario](#5-user-roles--roles-de-usuario)
    - [6. Glossary / Glosario](#6-glossary--glosario)
- [🤝 Contributing / Contribución](#-contributing--contribución)
- [📝 License / Licencia](#-license--licencia)

---

# 🚀 Project Overview / Visión General

## 🇪🇸 Español

**Health Survey System** es un sistema web desarrollado en **Laravel 13 + Livewire 4 + Flux UI** que automatiza el ciclo completo de encuestas de satisfacción en clínicas y centros de salud colombianos.

**Finalidad principal:**

1. **Automatizar** el registro de encuestas de satisfacción realizadas por pacientes mediante una **API REST pública** consumida por un frontend externo.
2. **Generar reportes estadísticos** en formato **PDF** con métricas agregadas (calificaciones, desglose por aseguradora, tendencias diarias, ranking de plantillas).
3. **Generar reportes tabulados en Excel** con una hoja por pregunta, incluyendo ponderaciones y marcas por opción seleccionada.
4. **Generar el reporte tipo 3 del Ministerio de Salud de Colombia** en formato **TXT pipe-separado** con la estructura `TIPO|CONSECUTIVO|ENTIDAD|NIT|CONTADORES...`.

**Público objetivo:** Administradores de clínicas, IPS, EPS y personal administrativo que necesita llevar control de la satisfacción de sus pacientes y cumplir con la normativa del Ministerio de Salud.

## 🇬🇧 English

**Health Survey System** is a web application built with **Laravel 13 + Livewire 4 + Flux UI** that automates the complete survey lifecycle in Colombian clinics and healthcare centers.

**Main purpose:**

1. **Automate** the registration of patient satisfaction surveys through a **public REST API** consumed by an external headless frontend.
2. **Generate statistical reports** in **PDF** format with aggregated metrics (ratings, insurer breakdown, daily trends, template ranking).
3. **Generate tabulated Excel reports** with one sheet per question, including weighted values and X-marks for selected options.
4. **Generate the Colombian Ministry of Health Type 3 report** in pipe-separated **TXT** format with the structure `TYPE|CONSECUTIVE|ENTITY|TAX_ID|COUNTERS...`.

**Target audience:** Clinic administrators, IPS, EPS, and administrative staff who need to track patient satisfaction and comply with Ministry of Health regulations.

---

# 🏗️ Architecture / Arquitectura

## Tech Stack / Stack Tecnológico

| Layer / Capa            | Technology / Tecnología          | Purpose / Propósito                       |
| ----------------------- | -------------------------------- | ----------------------------------------- |
| **Backend Framework**   | Laravel 13.x                     | Full-stack PHP framework                  |
| **UI Components**       | Livewire 4.x + Flux UI 2.x       | Reactive UI without JavaScript frameworks |
| **Templating**          | Blade                            | Server-side rendering                     |
| **Frontend (headless)** | JS Vanilla (external)            | Patient-facing survey application         |
| **Database**            | SQLite (default) / MySQL         | Persistent storage                        |
| **PDF Generation**      | DomPDF (barryvdh/laravel-dompdf) | Export PDF reports                        |
| **Excel Generation**    | PhpSpreadsheet (phpoffice)       | Export XLSX reports                       |
| **RBAC**                | Spatie Laravel Permission        | Role-based access control                 |
| **Auth**                | Laravel Fortify + Sanctum        | Authentication & API tokens               |
| **Captcha**             | Cloudflare Turnstile             | Bot protection                            |
| **CI/CD**               | GitHub Actions                   | Automated testing & linting               |

## Database Schema / Esquema de Base de Datos

```
┌─────────────────┐       ┌─────────────────────┐
│  system_settings │       │  survey_templates    │
├─────────────────┤       ├─────────────────────┤
│ id: 1 (singleton)│       │ id                  │
│ theme, language  │       │ title               │
│ company_name     │──────→│ is_active           │
│ company_dni      │       │ soft_deletes        │
│ entity_type      │       └────────┬────────────┘
│ registry_type    │                │
│ turnstile_*_key  │                │ 1
│ survey_monthly_goal│              │
│ default_template │                │ *
│ is_maintenance   │       ┌────────▼────────────┐
└─────────────────┘       │  survey_questions    │
                           ├─────────────────────┤
┌─────────────────┐       │ id                  │
│  insurers        │       │ survey_template_id   │
├─────────────────┤       │ question_text        │
│ id              │       │ field_type           │
│ name            │       │ options (JSON)       │
│ type (enum)     │       │ is_required          │
│ is_active       │       │ order                │
└────────┬────────┘       └────────┬────────────┘
         │                         │
         │ 1                       │ 1
         │                         │
         │ *                       │ *
┌────────▼────────┐       ┌────────▼────────────┐
│  patients        │       │  survey_answers      │
├─────────────────┤       ├─────────────────────┤
│ id              │       │ id                  │
│ document_type   │       │ survey_id            │
│ dni (unique)    │       │ survey_question_id   │
│ name            │       │ answer_value         │
│ email           │       │ weighted_value       │
│ insurer_id      │       │ soft_deletes        │
└────────┬────────┘       └─────────────────────┘
         │
         │ 1
         │
         │ *
┌────────▼────────┐
│  surveys         │
├─────────────────┤
│ id              │
│ survey_template │
│ patient_id      │
│ signature_path  │
│ status          │
│ rating          │
│ completed_at    │
│ soft_deletes    │
└─────────────────┘

┌────────────────────────┐
│  ministry_report_configs│
├────────────────────────┤
│ id: 1 (singleton)      │
│ survey_template_id     │
│ pipe_mapping (JSON)    │
└────────────────────────┘
```

## Directory Structure / Estructura del Proyecto

```
├── app/
│   ├── Actions/                    # Atomic business logic
│   │   ├── Survey/
│   │   ├── Patient/
│   │   ├── Insurer/
│   │   ├── SurveyAnswer/
│   │   └── SurveyTemplate/
│   ├── Console/Commands/
│   ├── Http/
│   │   ├── Controllers/Api/       # Public REST API controllers
│   │   ├── Middleware/             # CheckMaintenanceMode, SetAppLocale
│   │   └── Requests/Api/          # FormRequest validation
│   ├── Livewire/                   # Livewire components
│   │   ├── Admin/                  # Dashboard, templates, settings
│   │   │   └── Dashboard/         # StatsCards, ChartsSection, RecentSurveys
│   │   ├── Settings/               # Profile, security, appearance
│   │   └── User/                   # User management
│   ├── Helpers/                    # CalculateSurveyRating
│   ├── Models/                     # Eloquent models
│   └── Services/                   # Business logic services
│       ├── SurveyReportService.php
│       ├── MinistryReportGeneratorService.php
│       ├── ExcelReportService.php
│       ├── DashboardMetricsService.php
│       ├── SurveyProcessorService.php
│       ├── SurveyPublicProccessorService.php
│       └── SurveyTemplateBuilderService.php
├── config/                         # Laravel configuration
├── database/
│   ├── factories/                  # Model factories
│   ├── migrations/                 # Database migrations (11 files)
│   └── seeders/                    # Seeder classes
├── resources/
│   └── views/
│       ├── layouts/                # Blade layouts
│       ├── livewire/               # Component views
│       └── reports/                # PDF templates
├── routes/
│   ├── api.php                     # Public API routes
│   ├── web.php                     # Auth routes
│   ├── admin.php                   # Admin routes
│   └── settings.php               # Settings routes
└── tests/
    ├── Feature/                    # Feature tests
    │   ├── Admin/                  # ReportsTest, PurgeTest
    │   ├── Api/                    # PublicApiTest
    │   ├── Auth/                   # Authentication tests
    │   └── Settings/               # Settings tests
    └── Unit/                       # Unit tests
```

---

# 📐 Design Patterns / Patrones de Diseño

## Action Pattern

Cada operación atómica (crear paciente, registrar encuesta, crear respuestas) se encapsula en una **Action class**:

```
app/Actions/
├── Survey/CreateSurveyAction.php       # Create survey header
├── Survey/UpdateSurveyAction.php       # Update survey metadata
├── SurveyAnswer/CreateSurveyAnswersAction.php  # Bulk insert answers
├── Patient/CreatePatientAction.php     # Register new patient
├── Patient/UpdatePatientAction.php     # Update patient data
├── Insurer/CreateInsurerAction.php     # Register new insurer
├── Insurer/UpdateInsurerAction.php     # Update insurer data
├── SurveyTemplate/CreateSurveyTemplateAction.php
├── SurveyTemplate/UpdateSurveyTemplateAction.php
├── SurveyQuestion/CreateSurveyQuestionAction.php
├── SurveyQuestion/UpdateSurveyQuestionAction.php
└── User/{Create,Update,Delete}UserAction.php
```

**Beneficio:** Cada acción es testeable, reutilizable y orquestable desde servicios.

## Service Layer

Los **Services** orquestan múltiples Actions dentro de transacciones de base de datos y aplican lógica de negocio compleja:

| Service                          | Responsibility                                          |
| -------------------------------- | ------------------------------------------------------- |
| `SurveyProcessorService`         | Internal survey processing (admin flow)                 |
| `SurveyPublicProccessorService`  | Public API submission (patient data, signature, rating) |
| `SurveyTemplateBuilderService`   | Creates templates with questions in one transaction     |
| `ExcelReportService`             | Generates XLSX with per-question sheets                 |
| `SurveyReportService`            | Generates PDF reports (surveys + statistics)            |
| `MinistryReportGeneratorService` | Generates pipe-separated TXT for Ministry               |
| `DashboardMetricsService`        | Metrics aggregation for dashboard charts                |

## Singleton Configuration

`SystemSetting` y `MinistryReportConfig` usan un **patrón Singleton** con `firstOrCreate(['id' => 1])` y caché en memoria:

```php
SystemSetting::set();  // Returns cached singleton
```

Las settings se cachean en `global_system_settings` y se invalidan automáticamente al guardar.

## Livewire Component Architecture

El dashboard usa un patrón **parent → child** con eventos:

```
Dashboard (parent)
├── StatsCards          (recibe: dashboard-filter-updated)
├── ChartsSection       (recibe: dashboard-filter-updated)
└── RecentSurveysTable  (recibe: surveys list)
```

El filtro de periodo (`week`, `month`, `quarter`, `year`) se sincroniza entre componentes mediante `dispatch` y atributo `#[On]`.

---

# 🔌 API Endpoints

| Method | Endpoint                           | Description                                         | Auth |
| ------ | ---------------------------------- | --------------------------------------------------- | ---- |
| `GET`  | `/api/config`                      | Get public config (Turnstile key, maintenance mode) | None |
| `GET`  | `/api/survey-templates/{id}`       | Get template with questions                         | None |
| `POST` | `/api/surveys/{templateId}/submit` | Submit a survey response                            | None |
| `GET`  | `/api/insurers`                    | List active insurers                                | None |
| `GET`  | `/admin/dashboard`                 | Dashboard                                           | Auth |
| `GET`  | `/admin/surveys`                   | Survey list & reports                               | Auth |
| `GET`  | `/admin/survey-templates`          | Template management                                 | Auth |
| `GET`  | `/admin/settings`                  | System settings                                     | Auth |
| `GET`  | `/admin/ministry-settings`         | Ministry config                                     | Auth |

## API Request Example (Submit Survey)

```json
POST /api/surveys/1/submit

{
  "patient": {
    "name": "Juan Pérez",
    "dni": "1234567890",
    "document_type": "CC",
    "email": "juan@example.com",
    "insurer_id": 1
  },
  "signature": "data:image/png;base64,iVBOR...",
  "answers": [
    { "question_id": 1, "value": "Muy Buena" },
    { "question_id": 2, "value": "4" }
  ],
  "cf_turnstile_token": "0.abc123..."
}
```

## API Response Example

```json
{
    "success": true,
    "message": "Survey and patient registration processed successfully.",
    "data": {
        "rating_assigned": 4.5
    }
}
```

---

# 📦 Reports System / Sistema de Reportes

## 1. Excel Survey Report / Reporte Excel de Encuestas

**Generated by:** `ExcelReportService::generate()`

- Creates one sheet **per question** named `PREGUNTA 1`, `PREGUNTA 2`, etc.
- Each sheet contains: ID, patient name, X-marks per option, weighted value, date, observations.
- Uses vertical header with `Aptos Narrow` font, properly sized columns.
- Output: `.xlsx` file.

## 2. Statistics PDF Report / Reporte PDF Estadístico

**Generated by:** `SurveyReportService::generateStatisticsReport()`

- Company header with styling
- Period info (date range, period type)
- Summary cards: total surveys, average rating
- Breakdown by template (with progress bar)
- Breakdown by insurer (with progress bar)
- Daily trend table
- Output: `.pdf` file (landscape for surveys, portrait for statistics)

## 3. Ministry of Health Report (Type 3) / Reporte Ministerio de Salud (Tipo 3)

**Generated by:** `MinistryReportGeneratorService::generate()`

Output format (pipe-separated TXT):

```
TIPO_REGISTRO|CONSECUTIVO|TIPO_ENTIDAD|NIT_ENTIDAD|CONTADOR1|CONTADOR2|...|CONTADOR10
```

| Position | Field            | Description                                               |
| -------- | ---------------- | --------------------------------------------------------- |
| 1        | `registry_type`  | Default: `3` (Type 3 report)                              |
| 2        | `consecutive`    | Sequential number provided by the user                    |
| 3        | `entity_type`    | e.g. `NI` (healthcare entity type)                        |
| 4        | `company_dni`    | Tax ID / NIT                                              |
| 5-14     | `counter[1..10]` | 10 counters mapped to question options via `pipe_mapping` |

**Experience mapping** (legacy method in `SurveyReportService`):

```
MUY BUENA | BUENA | REGULAR | MALA | MUY MALA | No Answer |
DEFINITIVAMENTE SÍ | PROBABLEMENTE SÍ | DEFINITIVAMENTE NO | PROBABLEMENTE NO | No Answer
```

**Dynamic mapping** (new method via `MinistryReportGeneratorService`):

Uses `MinistryReportConfig.pipe_mapping` (JSON field) to map questions options to exact pipe positions. Options without mapping are auto-assigned to the next available slot.

---

# ⚙️ Setup & Installation / Instalación y Configuración

## Prerequisites / Requisitos

- **PHP** >= 8.3
- **Composer** >= 2.x
- **Node.js** >= 18.x + **npm** / **pnpm**
- **SQLite** (included with PHP) or **MySQL** / **PostgreSQL**

## Quick Start (1-command setup)

```bash
composer setup
```

This runs:

```bash
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
php artisan migrate --force
npm install
npm run build
```

## Manual Step-by-Step / Paso a Paso Manual

### 1. Clone the repository

```bash
git clone https://github.com/your-org/health-survey-system.git
cd health-survey-system
```

### 2. Configure environment

```bash
cp .env.example .env
```

Edit `.env` and configure your database and app settings:

```env
APP_NAME="Sistema de Encuestas de Salud"
APP_URL=http://localhost

DB_CONNECTION=sqlite
# Or for MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=health_surveys
# DB_USERNAME=root
# DB_PASSWORD=

# Admin credentials (used by seeder)
ADMIN_NAME="Administrador"
ADMIN_EMAIL="admin@admin.com"
ADMIN_PASSWORD="your-secure-password"

# Cloudflare Turnstile (optional)
# TURNSTILE_SITE_KEY=0x4...
# TURNSTILE_SECRET_KEY=0x4...
```

### 3. Install dependencies

```bash
composer install
npm install
```

### 4. Generate app key

```bash
php artisan key:generate
```

### 5. Run migrations

```bash
php artisan migrate
```

### 6. Seed the database (optional)

```bash
php artisan db:seed
```

This creates:

- Admin user (`admin@admin.com` / password from `.env`)
- Roles and permissions (admin role)
- Sample data: insurers, survey templates with questions, and more

### 7. Build frontend assets

```bash
npm run build
```

### 8. Start the development server

```bash
# Run all services concurrently (server + queue + logs + vite)
composer dev
```

Or individually:

```bash
# Terminal 1: Laravel server
php artisan serve --host=localhost

# Terminal 2: Queue worker (for queued jobs)
php artisan queue:listen --tries=1 --timeout=0

# Terminal 3: Vite dev server (hot reload)
npm run dev

# Terminal 4: Log viewer
php artisan pail --timeout=0
```

### 9. Access the application

| URL                                       | Description           |
| ----------------------------------------- | --------------------- |
| `http://localhost`                        | Login page            |
| `http://localhost/admin/dashboard`        | Admin dashboard       |
| `http://localhost/admin/surveys`          | Survey list & reports |
| `http://localhost/admin/survey-templates` | Template management   |
| `http://localhost/admin/settings`         | System settings       |

### 10. Run tests

```bash
# Run all tests
composer test

# Or individually:
php artisan test
```

---

# 🔐 Authentication / Autenticación

The system uses **Laravel Fortify** for authentication with:

- Login with email and password
- Email verification
- Password confirmation for sensitive actions
- Two-factor authentication support
- Password reset via email

**Default admin credentials after seeding:**

| Field    | Value                                 |
| -------- | ------------------------------------- |
| Email    | `admin@admin.com`                     |
| Password | As defined in `.env` `ADMIN_PASSWORD` |

---

# 🤖 Frontend (Headless API)

The system is designed as a **headless backend** for survey collection:

1. An external application (or any SPA) consumes the public API endpoints
2. The frontend fetches the template structure via `GET /api/survey-templates/{id}`
3. Patients fill out the survey and submit via `POST /api/surveys/{templateId}/submit`
4. The backend processes everything (patient creation, signature storage, rating calculation)
5. Administrators log into the admin panel to view surveys, generate reports, and manage templates

**Template response format:**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Encuesta de Satisfacción",
        "questions": [
            {
                "id": 1,
                "question_text": "¿Cómo califica la atención recibida?",
                "field_type": "radio",
                "is_required": true,
                "options": [
                    { "label": "Muy Buena", "weight": 5 },
                    { "label": "Buena", "weight": 4 },
                    { "label": "Regular", "weight": 3 },
                    { "label": "Mala", "weight": 2 },
                    { "label": "Muy Mala", "weight": 1 }
                ]
            }
        ]
    }
}
```

---

# 🧪 Testing / Pruebas

## Running Tests / Ejecutar Pruebas

```bash
# Full test suite
php artisan test

# Specific test files
php artisan test tests/Feature/Api/PublicApiTest.php
php artisan test tests/Feature/Admin/ReportsTest.php
php artisan test tests/Unit/Services/SurveyReportServiceTest.php

# With coverage (requires Xdebug)
php artisan test --coverage
```

**Test categories:**

| Test file                     | What it covers                                                           |
| ----------------------------- | ------------------------------------------------------------------------ |
| `PublicApiTest.php`           | API endpoints, Turnstile validation, maintenance mode, survey submission |
| `ReportsTest.php`             | Excel/PDF/TXT report generation, validation, error handling              |
| `DashboardTest.php`           | Dashboard page loads, metrics                                            |
| `SurveyReportServiceTest.php` | Ministry report generation, rating calculation                           |
| `SystemSettingPurgeTest.php`  | Survey purge logic                                                       |
| `Middleware/*Test.php`        | Maintenance mode, locale                                                 |
| `Auth/*Test.php`              | Login, password reset, confirmation                                      |

---

# 🗑️ Data Lifecycle / Ciclo de Vida de Datos

## Survey Purge System / Sistema de Purga de Encuestas

The system includes an automatic purge mechanism for surveys older than 6 months:

1. Surveys with `status = 'completed'` and `created_at < 6 months ago` are selected
2. Related signatures are deleted from storage
3. Answers are hard-deleted (`forceDelete`)
4. Surveys are hard-deleted
5. Orphan patients (no remaining surveys) are hard-deleted
6. The `surveys_purge_last_run` timestamp is updated

Accessible from: **Settings → Purge Old Surveys** in the admin panel.

---

# 🛡️ Security / Seguridad

## Cloudflare Turnstile Integration

All public survey submissions require a valid Turnstile token to prevent bot submissions. The token is verified server-side against Cloudflare's API.

Configuration:

- `turnstile_site_key`: Public key for the frontend widget
- `turnstile_secret_key`: Secret key for server-side verification

If no secret key is configured, Turnstile validation is skipped (useful for development).

## Rate Limiting / Limitación de Peticiones

Configurable rate limit for API endpoints (default: 60 requests per minute). Configured in the admin panel under **System Settings**.

## Maintenance Mode / Modo Mantenimiento

When enabled, the public API returns `503 Service Unavailable` for all endpoints except `/api/config` (which still responds to allow the frontend to detect maintenance mode).

---

# 📄 PRD — Product Requirements Document / Documento de Requisitos del Producto

## 1. Purpose / Propósito

**🇪🇸** Crear un sistema web que automatice la recolección y reporte de encuestas de satisfacción de pacientes en clínicas colombianas, permitiendo generar el reporte tipo 3 requerido por el Ministerio de Salud y Protección Social.

**🇬🇧** Create a web system that automates the collection and reporting of patient satisfaction surveys in Colombian clinics, enabling the generation of the Type 3 report required by the Ministry of Health.

## 2. Scope / Alcance

**In scope / Incluye:**

- Public API for survey submission from external frontends
- Admin panel for survey management, template creation, and report generation
- Excel tabulated reports
- Statistics PDF reports
- Ministry of Health Type 3 TXT reports
- Role-based access control (admin users)
- Cloudflare Turnstile bot protection
- Survey data purge after 6 months

**Out of scope / No incluye:**

- Patient-facing survey UI
- Real-time notifications
- Integration with external EHR/EMR systems
- Multi-language for survey questions

## 3. Functional Requirements / Requisitos Funcionales

| ID    | Requirement / Requisito                                               | Module   |
| ----- | --------------------------------------------------------------------- | -------- |
| FR-01 | Public API to fetch survey template with questions                    | API      |
| FR-02 | Public API to submit survey with patient data, signature, and answers | API      |
| FR-03 | Cloudflare Turnstile validation on submissions                        | API      |
| FR-04 | Admin login with email and password                                   | Auth     |
| FR-05 | Dashboard with metrics (total surveys, goal, rating, trends)          | Admin    |
| FR-06 | Filter dashboard by period (week, month, quarter, year)               | Admin    |
| FR-07 | CRUD for survey templates with dynamic questions                      | Admin    |
| FR-08 | Import/export survey templates as JSON                                | Admin    |
| FR-09 | View completed surveys with patient details                           | Admin    |
| FR-10 | Download Excel report with per-question sheets                        | Reports  |
| FR-11 | Download statistics PDF report                                        | Reports  |
| FR-12 | Download Ministry of Health Type 3 TXT report                         | Reports  |
| FR-13 | Configure entity data (NIT, entity type, registry type)               | Settings |
| FR-14 | Configure monthly survey goal                                         | Settings |
| FR-15 | Configure Cloudflare Turnstile keys                                   | Settings |
| FR-16 | Configure question-to-pipe mapping for Ministry report                | Settings |
| FR-17 | Purge surveys older than 6 months                                     | Settings |
| FR-18 | Configure maintenance mode                                            | Settings |

## 4. Non-Functional Requirements / Requisitos No Funcionales

| ID     | Requirement / Requisito                                                             |
| ------ | ----------------------------------------------------------------------------------- |
| NFR-01 | System must handle 100+ concurrent survey submissions per minute                    |
| NFR-02 | Database queries should use indexes on `created_at`, `status`, `survey_question_id` |
| NFR-03 | System settings should be cached with automatic invalidation                        |
| NFR-04 | All public endpoints must respect maintenance mode                                  |
| NFR-05 | Survey submission must use database transactions for atomicity                      |
| NFR-06 | Report generation should handle empty datasets gracefully                           |
| NFR-07 | System must support SQLite (default) and MySQL                                      |

## 5. User Roles / Roles de Usuario

| Role      | Permissions                                                                   |
| --------- | ----------------------------------------------------------------------------- |
| **Admin** | Full access: manage surveys, templates, settings, users, generate all reports |
| **User**  | (Future) View surveys and generate reports, no settings management            |

## 6. Glossary / Glosario

| Term / Término      | Definition / Definición                                                                         |
| ------------------- | ----------------------------------------------------------------------------------------------- |
| **IPS**             | Institución Prestadora de Servicios de Salud (Healthcare Provider)                              |
| **EPS**             | Entidad Promotora de Salud (Health Insurance Company)                                           |
| **NIT**             | Número de Identificación Tributaria (Tax ID)                                                    |
| **CC**              | Cédula de Ciudadanía (Colombian National ID)                                                    |
| **CE**              | Cédula de Extranjería (Foreigner ID)                                                            |
| **Reporte Tipo 3**  | Reporte de encuestas de satisfacción según normativa del Ministerio de Salud                    |
| **Turnstile**       | Cloudflare's privacy-preserving CAPTCHA alternative                                             |
| **Survey Template** | Predefined set of questions grouped into a survey form                                          |
| **Weighted Value**  | Numeric weight assigned to a survey option (0-5 scale)                                          |
| **Pipe Mapping**    | Configuration that maps question options to pipe-delimited positions in the Ministry TXT report |

---

# 🤝 Contributing / Contribución

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Commit your changes: `git commit -am 'Add new feature'`
4. Push to the branch: `git push origin feature/my-feature`
5. Submit a Pull Request

Before submitting, ensure:

- [ ] Tests pass: `php artisan test`
- [ ] Code style passes: `composer lint`
- [ ] Static analysis passes: `composer types:check`
- [ ] No new warnings introduced

---

# 📝 License / Licencia

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

---

<p align="center">
  <strong>Health Survey System</strong> — Automatización de encuestas de salud para Colombia 🇨🇴<br>
  Built with ❤️ using Laravel, Livewire, and Flux
</p>
