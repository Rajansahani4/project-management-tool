# Project Management Tool — Claude Standards

## Project Structure

- **Backend**: Laravel 13 API (`backend/`) — PHP 8.3, Pest tests, JWT auth
- **Frontend**: Vue 3 (`frontend/`) — not yet scaffolded

## Backend Standards

### General
- All work is done inside `backend/`
- Use `cd backend && <command>` for all artisan/composer/pest commands
- API is stateless; all endpoints under `/api/v1/`

### PHP / Laravel
- PHP 8.3+ features encouraged (readonly, enums, named args, union types)
- Use `#[Fillable]` / `#[Hidden]` PHP 8 attributes on models (not array properties)
- Controllers are thin: delegate logic to Form Requests and Models
- Return consistent JSON responses: `{ data, message, status }`
- HTTP status codes must be explicit — never rely on defaults
- Use `App\Http\Requests` for all validation (never validate in controllers)
- No inline SQL; use Eloquent or Query Builder

### Authentication
- JWT via `php-open-source-saver/jwt-auth`
- Auth guard: `api` (configured in `config/auth.php`)
- Protected routes use `auth:api` middleware
- Token returned on login as `{ token, token_type: 'bearer', expires_in }`

### Testing
- **Always use Pest** (never PHPUnit style directly)
- Use `RefreshDatabase` trait in Feature tests
- Tests are located in `tests/Feature/` (integration) and `tests/Unit/`
- Name test files `<Subject>Test.php` under a matching subdirectory
- Cover: happy path, validation errors, edge cases, auth failures
- Use `pest()->extend(Tests\TestCase::class)` in `Pest.php`

### Migrations
- Migration filenames: `YYYY_MM_DD_HHMMSS_<verb>_<table>_table.php`
- Always implement `down()` correctly
- Avoid modifying existing migrations; create new ones for changes

### Naming Conventions
- Controllers: `<Resource>Controller` (singular resource noun)
- Form Requests: `<Action><Resource>Request` (e.g. `RegisterUserRequest`)
- Models: singular PascalCase
- Routes: plural kebab-case (`/users`, `/project-tasks`)

## Running Tests

```bash
cd backend && php artisan test
# or
cd backend && ./vendor/bin/pest
```

## Running Dev Server

```bash
cd backend && php artisan serve
```
