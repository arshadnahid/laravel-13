# Laravel 13 — JWT Auth with Repository / Service / DTO Pattern

A Laravel 13 REST API showing a layered architecture:

```
Request → FormRequest (validate) → DTO → Controller → Service → Interface → Repository → Model / Auth
                                                                                         ↓
Response ← Resource (shape JSON) ← ApiResponses trait (envelope) ←───────────────────────┘
Errors  → ApiExceptionRenderer / exception render() → same JSON envelope
```

| Layer | Folder | Job |
|---|---|---|
| Form Request | `app/Http/Requests/Api/Auth` | Validate input, build the DTO |
| DTO | `app/DTOs/Auth` | Typed, immutable data passed between layers |
| Controller | `app/Http/Controllers/Api/v1/Auth` | Thin: take the request, call the service, return a response |
| Service | `app/Services/Auth` | Business rules (e.g. throw when login fails) |
| Interface | `app/Repositories/Auth/AuthInterfaces` | Contract the service depends on |
| Repository | `app/Repositories/Auth` | Talks to the database / auth guard |
| Provider | `app/Providers/RepositoryServiceProvider.php` | Binds each interface to its repository |
| Resource | `app/Http/Resources/Auth` | Shapes model data into JSON |
| Trait | `app/Traits/ApiResponses.php` | One JSON envelope for every response |
| Exceptions | `app/Exceptions` | Turns errors into the same envelope |

---

## 1. Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret          # writes JWT_SECRET to .env
php artisan migrate --seed      # UserSeeder creates a test user
```

---

## 2. Install the JWT package (tymon/jwt-auth)

```bash
composer require tymon/jwt-auth
```

Publish the package config (creates `config/jwt.php`):

```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

Generate the signing secret (adds `JWT_SECRET=` to `.env`):

```bash
php artisan jwt:secret
```

### 2.1 Add the `api` guard — `config/auth.php`

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'jwt',      // provided by tymon/jwt-auth
        'provider' => 'users',
    ],
],
```

To make `auth()` use JWT by default (so `auth()->attempt()` returns a token), set in `.env`:

```env
AUTH_GUARD=api
```

### 2.2 Make the User model a JWT subject — `app/Models/User.php`

```php
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    // Value stored in the token's "sub" claim
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // Extra claims to add to the token
    public function getJWTCustomClaims()
    {
        return [];
    }
}
```

### 2.3 API routes

Laravel 13 has no `routes/api.php` by default. Create it with:

```bash
php artisan install:api
```

It is registered in `bootstrap/app.php` → `withRouting(api: __DIR__.'/../routes/api.php')`, and every route in it gets the `/api` prefix.

`routes/api.php`:

```php
use App\Http\Controllers\Api\v1\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('me', [AuthController::class, 'me']);
    });
});
```

> **Why the middleware is on the route and not in the controller:** since Laravel 11,
> controllers no longer have `$this->middleware()` (calling it gives
> `Call to undefined method ...::middleware()`). Either put it on the routes as above,
> or have the controller implement `Illuminate\Routing\Controllers\HasMiddleware`:
>
> ```php
> use Illuminate\Routing\Controllers\HasMiddleware;
> use Illuminate\Routing\Controllers\Middleware;
>
> class AuthController extends Controller implements HasMiddleware
> {
>     public static function middleware(): array
>     {
>         return [new Middleware('auth:api', except: ['login'])];
>     }
> }
> ```

---

## 3. Custom Service Provider

### 3.1 Create it

```bash
php artisan make:provider RepositoryServiceProvider
```

Creates `app/Providers/RepositoryServiceProvider.php`.

### 3.2 Register it — `bootstrap/providers.php`

In Laravel 11+ providers are listed in `bootstrap/providers.php` (not `config/app.php`).
`make:provider` adds the line automatically; if it doesn't, add it yourself:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class,
];
```

### 3.3 Bind interfaces to repositories

```php
namespace App\Providers;

use App\Repositories\Auth\AuthInterfaces\AuthInterface;
use App\Repositories\Auth\AuthRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Interface => implementation. To change storage, write a new class
     * for the interface and swap it here.
     */
    public array $bindings = [
        AuthInterface::class => AuthRepository::class,
    ];
}
```

The `$bindings` property is shorthand for writing this in `register()`:

```php
$this->app->bind(AuthInterface::class, AuthRepository::class);
```

Now whenever a class asks for `AuthInterface` in its constructor, Laravel gives it an `AuthRepository`.

---

## 4. Repository + Interface

### 4.1 Create them

```bash
php artisan make:interface Repositories/Auth/AuthInterfaces/AuthInterface
php artisan make:class Repositories/Auth/AuthRepository
```

(Or create the files by hand.)

```
app/Repositories/
└── Auth/
    ├── AuthInterfaces/
    │   └── AuthInterface.php     ← the contract
    └── AuthRepository.php        ← the implementation
```

### 4.2 Interface — what the repository must do

`app/Repositories/Auth/AuthInterfaces/AuthInterface.php`

```php
namespace App\Repositories\Auth\AuthInterfaces;

use App\DTOs\Auth\LoginCredentialDTO;

interface AuthInterface
{
    /**
     * Attempt to log in and return a JWT, or null when credentials are invalid.
     */
    public function login(LoginCredentialDTO $loginCredentialDTO): ?string;
}
```

### 4.3 Repository — how it is done

`app/Repositories/Auth/AuthRepository.php`

```php
namespace App\Repositories\Auth;

use App\DTOs\Auth\LoginCredentialDTO;
use App\Repositories\Auth\AuthInterfaces\AuthInterface;

class AuthRepository implements AuthInterface
{
    public function login(LoginCredentialDTO $loginCredentialDTO): ?string
    {
        return auth()->attempt($loginCredentialDTO->toArray()) ?: null;
    }
}
```

### 4.4 Connect them

Add the pair to `$bindings` in `RepositoryServiceProvider` (section 3.3). For every new repository:

1. Make the interface
2. Make the repository class that `implements` it
3. Add `Interface::class => Repository::class` to `$bindings`

---

## 5. Service — uses the interface

```bash
php artisan make:class Services/Auth/AuthService
```

`app/Services/Auth/AuthService.php`

```php
namespace App\Services\Auth;

use App\DTOs\Auth\LoginCredentialDTO;
use App\Exceptions\UserNotFoundException;
use App\Repositories\Auth\AuthInterfaces\AuthInterface;

class AuthService
{
    // Ask for the INTERFACE, not the repository. The provider decides which class is injected.
    public function __construct(
        private readonly AuthInterface $auth
    ) {}

    /**
     * @throws UserNotFoundException
     */
    public function login(LoginCredentialDTO $loginCredentialDTO): string
    {
        return $this->auth->login($loginCredentialDTO) ?? throw new UserNotFoundException();
    }
}
```

**Why the interface:** the service doesn't know or care where the data comes from.
Swap `AuthRepository` for another implementation (or a fake in tests) by changing one line in the provider.

---

## 6. Controller — loads the service

```bash
php artisan make:controller Api/v1/Auth/AuthController
```

`app/Http/Controllers/Api/v1/Auth/AuthController.php`

```php
class AuthController extends Controller
{
    use ApiResponses;

    // Laravel's container builds AuthService (and its AuthInterface) automatically.
    public function __construct(private readonly AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login($request->toDTO());

        return $this->sendResponse('Login successful', new TokenResource($token));
    }

    public function me(): JsonResponse
    {
        return $this->sendResponse(null, ['user' => new UserResource(auth()->user())]);
    }

    public function logout(): JsonResponse
    {
        auth()->logout();

        return $this->sendResponse('Successfully logged out', null);
    }

    public function refresh(): JsonResponse
    {
        return $this->sendResponse('Token refreshed', new TokenResource(auth()->refresh()));
    }
}
```

The controller stays thin: no validation (Form Request does it), no business logic (Service does it), no JSON shaping (Resource + trait do it).

---

## 7. Form Request — validation

```bash
php artisan make:request Api/Auth/LoginRequest
```

Creates `app/Http/Requests/Api/Auth/LoginRequest.php`.

```php
namespace App\Http\Requests\Api\Auth;

use App\DTOs\Auth\LoginCredentialDTO;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    // authorize() removed → defaults to true (anyone may try to log in)

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    // Turn validated input into a DTO for the service
    public function toDTO(): LoginCredentialDTO
    {
        return new LoginCredentialDTO(
            email: $this->validated('email'),
            password: $this->validated('password'),
        );
    }
}
```

How it works:

- Type-hint it in a controller method (`login(LoginRequest $request)`) and Laravel validates **before** the method runs.
- If validation fails, a `ValidationException` is thrown → returns **422** with field errors (see section 10).
- `authorize()` returning `false` → **403**. The generated stub returns `false`, so remove it or return `true`.
- Use `$this->validated()` — only validated fields, never raw input.

> Make sure the controller imports the right namespace: `App\Http\Requests\Api\Auth\LoginRequest`.
> A wrong `use` gives `Class "..." does not exist`.

---

## 8. DTO — Data Transfer Object

```bash
php artisan make:class DTOs/Auth/LoginCredentialDTO
```

`app/DTOs/Auth/LoginCredentialDTO.php`

```php
namespace App\DTOs\Auth;

class LoginCredentialDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {}

    public function toArray(): array
    {
        return [
            'email'    => $this->email,
            'password' => $this->password,
        ];
    }
}
```

**Why a DTO instead of passing `$request` or an array:**

- Typed — the service knows exactly which fields exist; IDE autocomplete works.
- `readonly` — nobody can change the data on the way through.
- Services and repositories don't depend on HTTP, so they can be reused from jobs, commands or tests.

**Flow:** `LoginRequest::toDTO()` → `AuthService::login(DTO)` → `AuthInterface::login(DTO)` → `AuthRepository` uses `$dto->toArray()`.

---

## 9. Responses — `app/Traits/ApiResponses.php`

Every response (success or error) has the same shape:

```json
{
    "data": {},
    "message": "Login successful",
    "errors": [],
    "success": true,
    "status": "success"
}
```

| Method | Code |
|---|---|
| `sendResponse($message, $data)` | 200 (404 if data is an empty collection; adds `pagination` for paginators) |
| `sendCreatedResponse()` | 201 |
| `sendUpdatedResponse()` | 200 |
| `dataNotFoundJsonResponse()` | 404 |
| `sendErrors($message, $errors, $code)` | 422 by default |
| `validationErrorsResponse()` | 422 |
| `unauthenticatedResponse()` | 401 |

Default messages come from `lang/en/apiresponse.php` (e.g. `__('apiresponse.TEXT_ERROR')`).

---

## 10. Error handling

**Goal:** an API client must never see Laravel's raw error (message, file, line, stack trace).
That leaks internals and is a security risk. Every error returns the same JSON envelope as section 9.

### 10.1 Global renderer — `app/Exceptions/ApiExceptionRenderer.php`

```php
class ApiExceptionRenderer
{
    use ApiResponses;

    public function __invoke(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*') && ! $request->expectsJson()) {
            return null; // not an API request → let Laravel handle it normally
        }

        return match (true) {
            $e instanceof ValidationException => $this->validationErrorsResponse(null, $e->errors(), $e->status),
            $e instanceof AuthenticationException => $this->unauthenticatedResponse(),
            $e instanceof HttpExceptionInterface => $this->httpErrorResponse($e->getStatusCode()),
            default => $this->sendErrors(__('apiresponse.TEXT_SERVER_ERROR'), [], 500),
        };
    }
}
```

### 10.2 Register it — `bootstrap/app.php`

```php
use App\Exceptions\ApiExceptionRenderer;

->withExceptions(function (Exceptions $exceptions): void {
    // Always answer API requests with JSON, never an HTML error page
    $exceptions->shouldRenderJsonWhen(
        fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
    );

    // Render every API exception with our own envelope
    $exceptions->render(new ApiExceptionRenderer);
})
```

### 10.3 What each error returns

| Exception | Status | Message |
|---|---|---|
| `ValidationException` (Form Request failed) | 422 | "The given data was invalid." + field `errors` |
| `AuthenticationException` (no/invalid token) | 401 | "Unauthenticated." |
| `UserNotFoundException` (wrong email/password) | 401 | "Invalid email or password." |
| `AuthorizationException` / 403 | 403 | "You are not authorized to perform this action." |
| `ModelNotFoundException` / unknown route | 404 | "The requested resource was not found." |
| Wrong HTTP method | 405 | "Method not allowed." |
| Rate limited | 429 | "Too many requests. Please try again later." |
| **Anything else** (bugs, DB down, undefined method…) | 500 | "Something went wrong. Please try again later." |

Laravel converts `ModelNotFoundException` and `AuthorizationException` into HTTP exceptions
before render callbacks run, so they are caught by the `HttpExceptionInterface` branch.

The real error is **still logged** to `storage/logs/laravel.log` — it is hidden from the client, not lost.
This holds even when `APP_DEBUG=true`.

### 10.4 Custom exceptions with their own response

For an error the service throws on purpose, give the exception a `render()` method.
Laravel calls it before the global renderer.

```bash
php artisan make:exception UserNotFoundException
```

`app/Exceptions/UserNotFoundException.php`

```php
class UserNotFoundException extends Exception
{
    use ApiResponses;

    public function render(): JsonResponse
    {
        return $this->sendErrors(__('apiresponse.TEXT_INVALID_CREDENTIALS'), [], Response::HTTP_UNAUTHORIZED);
    }
}
```

Without `render()`, it would fall to the `default` branch and return a generic 500.

### 10.5 Messages — `lang/en/apiresponse.php`

All messages live here so they can be changed or translated in one place:

```php
return [
    'TEXT_GET_DATA' => 'Data retrieved successfully.',
    'TXT_DATA_NOT_FOUND' => 'Data not found.',
    'TEXT_CREATED_SUCCESSFULLY' => 'Created successfully.',
    'TEXT_UPDATED_SUCCESSFULLY' => 'Updated successfully.',
    'TEXT_ERROR' => 'Something went wrong.',
    'TEXT_ERROR_VALIDATION' => 'The given data was invalid.',
    'TEXT_UNAUTHENTICATED' => 'Unauthenticated.',
    'TEXT_INVALID_CREDENTIALS' => 'Invalid email or password.',
    'TEXT_FORBIDDEN' => 'You are not authorized to perform this action.',
    'TEXT_NOT_FOUND' => 'The requested resource was not found.',
    'TEXT_METHOD_NOT_ALLOWED' => 'Method not allowed.',
    'TEXT_TOO_MANY_REQUESTS' => 'Too many requests. Please try again later.',
    'TEXT_SERVER_ERROR' => 'Something went wrong. Please try again later.',
];
```

---

## 11. Endpoints

| Method | URL | Auth | Body |
|---|---|---|---|
| POST | `/api/auth/login` | — | `email`, `password` |
| POST | `/api/auth/me` | Bearer token | — |
| POST | `/api/auth/refresh` | Bearer token | — |
| POST | `/api/auth/logout` | Bearer token | — |

Send `Accept: application/json` and `Authorization: Bearer <access_token>`.
A Bruno collection for these requests is in `bruno/`.

---

## 12. Adding a new feature (checklist)

Example: `Post`.

```bash
php artisan make:model Post -m
php artisan make:request Api/Post/StorePostRequest
php artisan make:class DTOs/Post/PostDTO
php artisan make:interface Repositories/Post/PostInterfaces/PostInterface
php artisan make:class Repositories/Post/PostRepository
php artisan make:class Services/Post/PostService
php artisan make:resource Post/PostResource
php artisan make:controller Api/v1/Post/PostController
```

1. Form Request: `rules()` + `toDTO()`
2. Interface: declare the methods
3. Repository: `implements PostInterface`
4. Add `PostInterface::class => PostRepository::class` to `RepositoryServiceProvider::$bindings`
5. Service: inject `PostInterface` in the constructor
6. Controller: inject `PostService`, `use ApiResponses`, return a Resource
7. Add routes in `routes/api.php` (inside `auth:api` if protected)
