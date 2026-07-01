# Como correr SisPRO

## Requisitos

- **PHP** >= 8.2
- **Composer**
- **Node.js** + npm
- **MySQL** (base de datos `prosarc7_sisprodb`)
- **Laragon** (recomendado para gestionar MySQL localmente)

## Instalacion

```bash
# 1. Instalar dependencias PHP
composer install

# 2. Instalar dependencias JS
npm install

# 3. Copiar archivo de entorno (si no existe .env)
copy .env.example .env

# 4. Generar APP_KEY
php artisan key:generate

# 5. Crear enlace simbolico de storage
php artisan storage:link

# 6. Compilar assets frontend
npm run dev
```

## Iniciar el servidor

```bash
php artisan serve
```

Por defecto corre en `http://127.0.0.1:8000`.

La base de datos `prosarc7_sisprodb` debe estar corriendo en MySQL (Laragon en `127.0.0.1:3306`).

---

## Cloudflare Turnstile (Proteccion del login)

El proyecto usa **Cloudflare Turnstile** como CAPTCHA en los formularios de login, registro y recuperacion de password. Sustituye a reCAPTCHA de Google y es gratuito, respetando la privacidad del usuario.

### Configuracion

Las claves se configuran en `.env`:

```env
TURNSTILE_SITE_KEY=0x4AAAAAAC8Rjh6foeyQ0Its
TURNSTILE_SECRET_KEY=0x4AAAAAAC8RjvzRYiVLErM54sz45lhkiwQ
```

- **TURNSTILE_SITE_KEY**: clave publica (se expone en el frontend)
- **TURNSTILE_SECRET_KEY**: clave secreta (solo backend)

Se cargan via `config/services.php`:

```php
'turnstile' => [
    'site_key' => env('TURNSTILE_SITE_KEY'),
    'secret_key' => env('TURNSTILE_SECRET_KEY'),
],
```

### Flujo de validacion

1. **Frontend** (`resources/views/layouts/appauth.blade.php:164-166`): carga el script JS de Cloudflare solo si hay `site_key` configurada. El widget `<div class="cf-turnstile">` se renderiza en login, registro y recuperacion de password.

2. **Validacion backend**:
   - `LoginController::validateLogin()` (linea 136-158)
   - `RegisterController::validator()` (linea 53-76)
   - `ForgotPasswordController::validateEmail()` (linea 100-122)

   Todos verifican que `cf-turnstile-response` este presente y llaman a `passesTurnstile()`.

3. **Verificacion del token** (`passesTurnstile()`): usa `Http` de Laravel para enviar `POST` a `https://challenges.cloudflare.com/turnstile/v0/siteverify` con `secret`, `response` (token del widget) y `remoteip`. Si Cloudflare responde `success: true`, el CAPTCHA es valido.

### Paginas protegidas

| Pagina | Archivo |
|--------|---------|
| Login | `resources/views/auth/login.blade.php:43-54` |
| Registro | `resources/views/auth/register.blade.php:65-72` |
| Recuperar password | `resources/views/auth/passwords/email.blade.php:33-40` |

### Desactivar Turnstile

Para desactivarlo, dejar las variables vacias en `.env`. El codigo verifica `config('services.turnstile.secret_key')` antes de exigir el CAPTCHA, y si esta vacio lo trata como opcional.

---

## Nota: PHP 8.5 y PDO::MYSQL_ATTR_SSL_CA

Si usas PHP >= 8.5, la constante `PDO::MYSQL_ATTR_SSL_CA` esta deprecada. Se reemplazo en `vendor/laravel/framework/config/database.php` por `Pdo\Mysql::ATTR_SSL_CA`. Si restauras el vendor con `composer install`, tendras que re-aplicar este cambio.
