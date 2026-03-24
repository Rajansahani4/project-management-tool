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
- Return consistent JSON responses: `{ data, message, status }`
- HTTP status codes must be explicit — never rely on defaults
- No inline SQL; use Eloquent or Query Builder

### Architecture Layers (strictly enforced)

**Controllers — thin only**
- Responsibilities: authorize, call service, return JSON response
- Must NOT contain: Eloquent queries, model mutations, event dispatching, relation loading
- Inject the service via constructor; call one service method per action

**Service Layer — all business logic**
- Every resource has a corresponding `App\Services\<Resource>Service`
- Responsibilities: Eloquent operations, relation loading, event/job dispatching, multi-step logic
- Services receive typed model and scalar arguments — never raw request objects
- Return the mutated/created model (or `void` for deletes)

**Form Requests — validation only**
- Validate input in `App\Http\Requests\<Action><Resource>Request`
- Never validate inside controllers or services
- `authorize()` always returns `true` (authorization lives in policies, not requests)

**API Resources — response formatting only**
- Format every response through `App\Http\Resources\<Resource>Resource`
- Use `whenLoaded()` for relations; never lazy-load inside a resource

**Policies — authorization only**
- One policy per model, registered in `AppServiceProvider`
- Controllers call `$this->authorize()` before delegating to the service

**No Repositories**
- Do NOT introduce a repository layer; Eloquent + service layer is sufficient

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
- **Enums: must end with `Enum` suffix** (e.g. `TaskStatusEnum`, `ProjectRoleEnum`)
  - File name must match class name: `TaskStatusEnum.php`
  - All enum `use` statements and references must use the `Enum`-suffixed class name

### Migration Safety Rules
- **Table creation**: always guard with `if (! Schema::hasTable('table_name'))` before `Schema::create()`
- **Column addition**: always guard with `if (! Schema::hasColumn('table', 'column'))` before adding a column to an existing table
- These checks prevent errors when migrations are re-run or applied on pre-seeded environments
- Example:
  ```php
  if (! Schema::hasTable('tasks')) {
      Schema::create('tasks', function (Blueprint $table) { ... });
  }
  ```

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
