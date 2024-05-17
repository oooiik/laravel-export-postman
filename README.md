# Laravel Export to Postman

Package allows you to automatically generate a Postman collection based on your routes.

## Installation

```bash
composer require oooiik/laravel-export-postman
```

## Configuration

You can modify any of the `export-postman.php` config values to suit your export requirements.

## Php Comments Documentation for API Annotations

### Authentication

`@AuthNo`
No authentication is required for the annotated API endpoint.

`@AuthParent`
Inherits the authentication settings from the parent endpoint.

`@AuthBearer [token]`
Requires Bearer token authentication. Replace `[token]` with the actual token. Example:
```
@AuthBearer abc123
```

### Headers

`@Header [key] => [value]`
Specifies a custom header for the API request. 
Replace `[key]` with the header name and `[value]` with the header value.
```
@Header Accept => application/json
@Header Content-Type => application/json
```

### Descriptions

`@DescriptionContext [context]`
Provides a brief description or context for the API request. Replace `[context]` with the description.

`@DescriptionBasePath [path]`
Specifies the base path description for the API endpoint. Replace `[path]` with the base path. Example:
```
@DescriptionBasePath /dir/file
```

`@DescriptionResourcePath [path]`
Specifies the resource path description for the API endpoint. Replace `[path]` with the resource path.

### Pre-Request Scripts
`@PreRequestScriptContext [path]`
Defines a pre-request script to be executed before the API request. Replace `[path]` with the context or location of the script.

`@PreRequestScriptFileBasePath [path]`
Specifies the base path to the file containing the pre-request script. Replace `[path]` with the file location relative to the base directory.

`@PreRequestScriptFileResourcePath [path]`
Specifies the resource path to the file containing the pre-request script. Replace `[path]` with the specific file location.

### Test Scripts
`@TestScriptContext [context]`
Defines a test script to be executed after the API request. Replace `[context]` with the context or description of the test script.

`@TestScriptFileBasePath [path]`
Specifies the base path to the file containing the test script. Replace `[path]` with the file location relative to the base directory.

`@TestScriptFileResourcePath [path]`
Specifies the resource path to the file containing the test script. Replace `[path]` with the specific file location.

Example:
```php
class Container ...
{
    /**
     * @AuthNo
     * @Header Accept => application/json
     * @DescriptionContext description for postman
     * @PreRequestScriptContext console.log('pre-request index method') 
     * @TestScriptContext console.log('test index method') 
     */
    public function index(...) {...}
    
    /**
     * @AuthBearer {{TOKEN}}
     * @PreRequestScriptFileBasePath ./dir/filename
     * @TestScriptFileResourcePath ./dir/filename
     */
    public function Show(...) {...}
}


```

## Usage

```bash
php artisan export:postman
```

