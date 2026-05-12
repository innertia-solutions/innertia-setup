Generate complete domain scaffolding for: $ARGUMENTS

## What to do

Read `CLAUDE.md` first to understand the architecture rules, then generate all files for the requested domain/feature.

If `$ARGUMENTS` is empty, ask the user: "¿Qué dominio quieres generar? (e.g. Products, Orders, Invoices)"

---

## Files to generate

Given domain **$ARGUMENTS** (infer model name = singular, domain = plural):

### 1. Migration
`database/migrations/{timestamp}_create_{table}_table.php`
- Use `$table->uuid('id')->primary()`
- Add `$table->string('name')` and any other obvious fields based on the domain name
- Add `$table->timestamps()` and `$table->softDeletes()`

### 2. Model
`app/Domains/{Domain}/Models/{Model}.php`
- Extend `Illuminate\Database\Eloquent\Model`
- Use traits: `Auditable`, `HasHistory`, `HasUuid`, `SoftDeletes` (all from `Innertia\Traits\`)
- Set `$fillable` with the migration fields
- Add `casts()` method for boolean/date fields

### 3. UseCases
`app/Domains/{Domain}/UseCases/Create{Model}.php`
- Constructor with typed params matching model fields
- Validates uniqueness if there's an obvious unique field (e.g. name, email, code)
- Creates and returns model

`app/Domains/{Domain}/UseCases/Update{Model}.php`
- Constructor: `string $id`, then nullable params for each field
- Throws `NotFoundException` if not found
- Only updates non-null fields
- Returns updated model

`app/Domains/{Domain}/UseCases/Delete{Model}.php`
- Constructor: `string $id`
- Throws `NotFoundException` if not found
- Soft-deletes and returns void

### 4. Controller
`app/Apps/BackOffice/{Domain}/Controllers/{Model}Controller.php`
- Full CRUD: `index`, `show`, `store`, `update`, `destroy`
- `index` uses `DataTable::create('{table}')->columns([...])->render({Model}::class, $request)`
- `store` / `update` use the generated UseCases
- Proper validation rules inferred from model fields
- All methods return `JsonResponse`

### 5. Routes
Suggest the lines to add to `routes/api.php`:
```php
Route::middleware('auth:api')->group(function () {
    Route::apiResource('{table}', \App\Apps\BackOffice\{Domain}\Controllers\{Model}Controller::class);
});
```

---

## Rules

- Follow the patterns in `CLAUDE.md` exactly
- Use `Innertia\Exceptions\NotFoundException` and `ConflictException`
- Use `Innertia\Platform\Contracts\UseCase` as base for all use cases
- IDs are always UUID strings — never integers in method signatures
- Never put business logic in controllers — delegate to UseCases
- After generating all files, print a summary table of what was created
