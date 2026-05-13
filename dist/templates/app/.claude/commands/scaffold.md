Generate complete domain scaffolding for: $ARGUMENTS

## What to do

If `$ARGUMENTS` is empty, ask the user: "¿Qué dominio quieres generar? (e.g. Products, Orders, Invoices)"

Read `CLAUDE.md` to understand project conventions, then follow the two-phase process below.

---

## Phase 1 — Generate skeleton with Artisan (fast, no tokens)

Infer names from `$ARGUMENTS`:
- **Domain** = plural StudlyCase  (e.g. `Products`)
- **Model**  = singular StudlyCase (e.g. `Product`)
- **table**  = snake_case plural   (e.g. `products`)

Run these commands inside `backend/` (adjust path if the project has no `backend/` subdirectory):

```bash
php artisan innertia:make:model {Model} --domain={Domain} --migration --factory
php artisan innertia:make:usecase Create{Model} --domain={Domain} --model={Model}
php artisan innertia:make:usecase Update{Model} --domain={Domain} --model={Model}
php artisan innertia:make:usecase Delete{Model} --domain={Domain} --model={Model}
php artisan innertia:make:controller {Model}Controller --app=BackOffice --domain={Domain} --model={Model}
```

---

## Phase 2 — Enrich the generated files

Read every file that was just created and improve it based on the domain name and project context:

### Migration
- Replace the placeholder `name` column with fields that actually make sense for the domain.
- Add `uuid`, `timestamps`, `softDeletes` if not present.
- Add obvious columns (e.g. for `Invoices`: `number`, `amount`, `status`, `due_date`).

### Model
- Update `$fillable` to match the migration fields.
- Add `casts()` for booleans, dates, enums.
- Add obvious relationships (e.g. `Invoice belongsTo Client`).

### Create{Model} UseCase
- Add constructor parameters matching the migration fields (required ones).
- Validate uniqueness if there is an obvious unique field (e.g. `number`, `email`, `code`).
  Use `ConflictException` from `Innertia\Exceptions\ConflictException`.
- Call `{Model}::create([...])` with all fields and return the model.

### Update{Model} UseCase
- Constructor: `string $id`, then nullable params for each field.
- Throw `NotFoundException` (`Innertia\Exceptions\NotFoundException`) if not found.
- Only update non-null params (`array_filter` or explicit checks).
- Return the updated model.

### Delete{Model} UseCase
- Constructor: `string $id`.
- Throw `NotFoundException` if not found.
- Soft-delete and return void.

### Controller
- Update `index` columns list to match real fields.
- Update `store` / `update` validation rules to match the fields.
- Wire UseCase constructor calls with real field names from `$request->validated()`.

---

## Rules

- IDs are always UUID strings — never integers in signatures.
- Never put business logic in controllers — delegate to UseCases.
- Follow every pattern in `CLAUDE.md` exactly.
- After both phases, print a summary table of created/modified files.
