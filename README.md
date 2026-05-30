# Laravel Export to Postman

[![Latest Version on Packagist](https://img.shields.io/packagist/v/oooiik/laravel-export-postman.svg?style=flat-square)](https://packagist.org/packages/oooiik/laravel-export-postman)
[![Total Downloads](https://img.shields.io/packagist/dt/oooiik/laravel-export-postman.svg?style=flat-square)](https://packagist.org/packages/oooiik/laravel-export-postman)
[![PHP Version](https://img.shields.io/packagist/php-v/oooiik/laravel-export-postman.svg?style=flat-square)](https://packagist.org/packages/oooiik/laravel-export-postman)
[![License](https://img.shields.io/packagist/l/oooiik/laravel-export-postman.svg?style=flat-square)](LICENSE)

Generate a [Postman Collection v2.1](https://schema.postman.com/) from your Laravel API routes — with a single Artisan command.

Annotate your controllers with simple PHPDoc tags to control authentication, headers, descriptions, and even Postman pre-request and test scripts. The output is a ready-to-import JSON collection — no manual route copying, no drift between code and docs.

## Features

- 🚀 **One-command export** — `php artisan postman:export` and you're done
- 🔐 **Auth support** — Bearer tokens, no-auth, parent-inherited, per-endpoint
- 📝 **Rich annotations** — descriptions, headers, scripts via standard PHPDoc
- 📂 **External file sources** — load descriptions and scripts from `base_path()` or `resource_path()`
- ⚡ **Pre-request & test scripts** — inject Postman JavaScript directly from your code
- 🎯 **Folder grouping** — collections organized by route prefix
- 🧩 **Laravel 6 → 12** — broad compatibility, PHP 7.1+ through 8.x

## Requirements

- PHP 7.1 or higher
- Laravel 6.x — 12.x

## Installation

```bash
composer require oooiik/laravel-export-postman
```

Publish the config (optional):

```bash
php artisan vendor:publish --provider="Oooiik\LaravelExportPostman\ExportPostmanServiceProvider"
```

## Quick Start

Export your routes to a Postman collection:

```bash
php artisan postman:export
```

The collection file will be created in your configured storage path. Import it into Postman → **File → Import**.

## Configuration

Edit `config/export-postman.php` after publishing:

```php
return [
    'name'        => env('APP_NAME', 'Laravel API'),
    'output_path' => storage_path('app/postman'),
    'base_url'    => env('APP_URL', 'http://localhost'),
    // ... and more
];
```

## Annotation Reference

Add PHPDoc annotations to your controller methods or closures to enrich the generated collection.

### Authentication

| Annotation | Description |
|---|---|
| `@AuthNo` | No authentication for this endpoint |
| `@AuthParent` | Inherit authentication from the parent folder |
| `@AuthBearer [token]` | Use a Bearer token (literal value or Postman variable) |

```php
/**
 * @AuthBearer {{access_token}}
 */
public function show($id) { /* ... */ }
```

### Headers

Use `@Header key => value` for custom request headers. Multiple `@Header` annotations are supported.

```php
/**
 * @Header Accept => application/json
 * @Header X-Tenant-Id => {{tenant_id}}
 */
public function index() { /* ... */ }
```

### Descriptions

Three flavors — inline, file from `base_path()`, file from `resource_path()`. They can be combined.

| Annotation | Source |
|---|---|
| `@DescriptionContext [text]` | Inline markdown text |
| `@DescriptionBasePath [path]` | File path relative to `base_path()` |
| `@DescriptionResourcePath [path]` | File path relative to `resource_path()` |

```php
/**
 * @DescriptionContext Returns a paginated list of orders for the authenticated user.
 * @DescriptionResourcePath docs/api/orders-index.md
 */
public function index() { /* ... */ }
```

### Pre-request Scripts

Inject Postman JavaScript that runs **before** the request is sent. Useful for token refresh, signature generation, or dynamic variables.

| Annotation | Source |
|---|---|
| `@PreRequestScriptContext [js]` | Inline JavaScript |
| `@PreRequestScriptFileBasePath [path]` | JS file relative to `base_path()` |
| `@PreRequestScriptFileResourcePath [path]` | JS file relative to `resource_path()` |

```php
/**
 * @PreRequestScriptFileResourcePath postman/scripts/refresh-token.js
 */
public function transfer() { /* ... */ }
```

### Test Scripts

Inject Postman JavaScript that runs **after** the response is received. Use for assertions or extracting values into environment variables.

| Annotation | Source |
|---|---|
| `@TestScriptContext [js]` | Inline JavaScript |
| `@TestScriptFileBasePath [path]` | JS file relative to `base_path()` |
| `@TestScriptFileResourcePath [path]` | JS file relative to `resource_path()` |

```php
/**
 * @TestScriptContext pm.test("Status 200", () => pm.response.to.have.status(200));
 */
public function login() { /* ... */ }
```

## Full Example

```php
/**
 * @AuthBearer {{access_token}}
 * @Header Accept => application/json
 * @Header X-Tenant-Id => {{tenant_id}}
 * @DescriptionContext Creates a new order for the authenticated user.
 * @DescriptionResourcePath docs/api/orders-create.md
 * @PreRequestScriptFileResourcePath postman/scripts/sign-request.js
 * @TestScriptContext pm.test("Created", () => pm.response.to.have.status(201));
 */
public function store(StoreOrderRequest $request)
{
    // ...
}
```

## Compatibility Matrix

| Laravel | PHP | Status |
|---|---|---|
| 12.x | 8.2+ | ✅ Supported |
| 11.x | 8.2+ | ✅ Supported |
| 10.x | 8.1+ | ✅ Supported |
| 9.x  | 8.0+ | ✅ Supported |
| 8.x  | 7.3+ | ✅ Supported |
| 7.x  | 7.2.5+ | ✅ Supported |
| 6.x  | 7.2+ | ✅ Supported |

## Contributing

Pull requests are welcome. For larger changes, please open an issue first to discuss the direction.

Feature ideas, bug reports, and questions → [GitHub Issues](https://github.com/oooiik/laravel-export-postman/issues).

## Credits

- [Obidjon Toshev](https://oooiik.com) — author & maintainer
- All [contributors](https://github.com/oooiik/laravel-export-postman/graphs/contributors)

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.