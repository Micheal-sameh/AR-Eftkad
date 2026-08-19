# AR-Eftkad

A Laravel 10 REST API backend for a church (Coptic Orthodox) **pastoral visitation / care tracking system**.

"Eftkad" (افتقاد) is Arabic for a pastoral visit. The app lets priests ("Father") and lay ministers ("Servant") log and browse home/phone visits made to congregation members, tracking mass attendance, confession status, specific needs, and how the member was reached.

## Tech Stack

- **PHP** 8.1, **Laravel** 10.10
- **MySQL** (via `DB_CONNECTION=mysql`)
- **Laravel Sanctum** — token-based API authentication
- **L5-Swagger** (`darkaonline/l5-swagger`) — OpenAPI/Swagger documentation
- **Guzzle** — HTTP client
- Dev tooling: **Laravel Pint** (code style), **Laravel Sail**, **PHPUnit**, **Faker**, **Mockery**

The project is API-only: `routes/web.php` is just the default Laravel welcome page — there is no Blade/SPA frontend beyond the Swagger UI.

## Architecture

A layered structure rather than default Laravel scaffolding:

```
Controller (app/Http/Controllers/Api)
    -> Service (app/Services)
        -> Repository (app/Repository)
            -> Model (app/Models)
```

- **DTOs** (`app/DTOs`) — typed request input objects (e.g. `EftkadCreateDTO`)
- **Resources** (`app/Http/Resources`) — typed, localized API output, including a generic `EnumResource` that pairs a raw enum value with its localized label
- **Enums** (`app/Enums`) — plain PHP classes (not native PHP 8.1 enums) exposing bilingual (`en`/`ar`) labels via `all()`, `getStringValue()`, `isValid()`
- **BaseController** — standard JSON envelope (`status_code`, `message`, `data`, `additional_data`) via a `ResponseHandler` trait

## Localization

The app is bilingual Arabic/English (`resources/lang/ar`, `resources/lang/en`). A custom `SetLocale` middleware sets the locale per-request (Arabic by default). All routes are wrapped in this middleware group.

## Authentication

- **Laravel Sanctum**, token-based (`personal_access_tokens` table)
- Login is by **`membership_code` + password**, not email
- On login, all of the user's existing tokens are revoked and a single fresh token is issued (one active session per user)
- Membership codes follow a structured format, e.g. `E1C1F1NR1`

## Domain Models

### `User`
Represents a priest or servant. Fields: `name`, `email`, `membership_code`, `phone`, `type`, `password`.
- `type`: `UserType` enum — `Father` (priest, 1) or `Servant` (lay minister, 2)

### `Eftkad`
The core entity — a record of a single pastoral visit. Fields:
- `membership_code`, `date`, `correspondence_address`, `location`
- `mass_attendence` — `MassAttendanceType` enum (Unknown / Regular / Irregular)
- `needs` — JSON array of `NeedType` values (Seniors, Patient, Job, Studying Help, Tnawel/home communion, Emergency Calls)
- `communication_means` — JSON array of `CommunicationType` values (WhatsApp CMC, Facebook Group, Kenisty App, No Communication)
- `attend_meetings`, `need_eftkad_from_meeting`, `need_eftkad_by_father` — boolean-style flags (`BoolType` enum)
- `father_confession`, `mother_confession`, `children_confession` — confession status fields
- `father_membership_code`, `servant_membership_code` — who conducted the visit
- `type` — `EftkadType` enum (Call = 1, Home = 2)

## API Surface (`routes/api.php`)

All routes are locale-aware (`setlocale` middleware). Sanctum-protected routes require a bearer token.

| Method | Endpoint | Controller | Description |
|---|---|---|---|
| POST | `/auth/login` | `AuthController@login` | Authenticate via `membership_code` + password, revoke old tokens, issue a new one |
| POST | `/auth/logout` | `AuthController@logout` | Revoke the current token |
| GET | `/eftkads/{all?}` | `EftkadController@index` | Paginated list of visits (10/page); pass `all` to get the full list unpaginated |
| POST | `/eftkads` | `EftkadController@create` | Create a new visit record (wrapped in a DB transaction) |
| GET | `/settings/enums` | `SettingController@enums` | Returns all enum value/label lists, for populating frontend dropdowns |
| GET | `/settings/filters` | `SettingController@filters` | Returns lists of Fathers/Servants for filter UIs |
| GET | `/user` | — | Default Sanctum stub, returns the authenticated user |
| GET | `/documentation` | — | Swagger UI |

## API Documentation

L5-Swagger is installed and configured (`config/l5-swagger.php`, docs served at `/api/documentation`), but no `@OA\...` annotations exist yet in the controllers — the infrastructure is wired up but not yet populated.

## Database

Migrations, in order:
1. `create_users_table` (stock Laravel — extended with `membership_code`, `phone`, `type` on the model, worth double-checking against actual schema)
2. `create_password_reset_tokens_table` (stock)
3. `create_failed_jobs_table` (stock)
4. `create_personal_access_tokens_table` (Sanctum)
5. `create_eftkads_table` — the only app-specific migration; defines the `eftkads` table described above

### Seed Data (`UserSeeder`)
Three sample users, all with password `123456`:
- ابونا مينا (Father Mina) — `Father`, `E1C1F1NR1`
- ميشيل (Michel) — `Servant`, `E1C1F1NR3`
- مارك (Mark) — `Servant`, `E1C1F1NR3`

## Environment / Integrations

`.env.example` is still the unmodified Laravel default:
- Queue: `sync` (no async driver configured)
- Broadcast: `log` driver (Pusher config present but empty/unused)
- Cache/Session: `file` driver
- AWS S3 variables present but empty
- No active third-party integrations — the "Kenisty App"/WhatsApp options in `CommunicationType` are descriptive metadata, not live integrations

## Current State / Gaps

This looks like an early-stage / MVP codebase:
- `README.md` and `.env.example` are still unmodified Laravel defaults
- No Swagger annotations written yet despite the docs infrastructure being present
- Only one feature-specific migration (`eftkads`) exists so far
