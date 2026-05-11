# Filtros (Middleware)

Los filtros se registran en `app/Config/Filters.php` con los alias `auth` y `guest`.

---

## AuthFilter (`app/Filters/AuthFilter.php`)

Filtro de autenticacion y autorizacion. Se aplica a rutas protegidas.

### Comportamiento

1. **Verificacion de sesion**: comprueba que `session()->get('login')` sea `true`.
2. **Timeout por inactividad**: si la sesion existe, verifica que no haya expirado comparando `last_activity_at` con el timestamp actual. El timeout se configura con la variable `app.sessionInactivityTimeout` (default: 1800 segundos / 30 minutos).
3. **Verificacion de permisos**: si la ruta define argumentos (ej. `'filter' => 'auth:users.view'`), verifica que el permiso este en el array `permissions` de la sesion.
4. **Respuesta segun tipo de request**:
   - Requests API (ruta empieza con `api/` o headers JSON): respuesta JSON con codigo HTTP apropiado.
   - Requests web: redireccion a `/dashboard` (login) o `/main` (sin permisos).

### Uso en rutas

```php
// Solo requiere sesion activa
$routes->get('/main', 'Main::main', ['filter' => 'auth']);

// Requiere sesion + permiso especifico
$routes->get('/api/usuarios/index', 'Usuarios::index', ['filter' => 'auth:users.view']);
```

### Codigos de respuesta

| Codigo | Situacion |
|---|---|
| `401` | No autenticado o sesion expirada |
| `403` | Autenticado pero sin permisos |

---

## GuestFilter (`app/Filters/GuestFilter.php`)

Filtro inverso: redirige usuarios autenticados lejos de paginas publicas (login).

### Comportamiento

Si `session()->get('login')` es `true`, redirige a `/main`.

### Uso en rutas

```php
$routes->get('/', 'Dashboard::index', ['filter' => 'guest']);
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'guest']);
```

---

## Filtros globales (CI4 built-in)

Registrados pero no activos por defecto en `$globals`:

| Filtro | Descripcion | Estado |
|---|---|---|
| `csrf` | Proteccion CSRF | Desactivado |
| `honeypot` | Deteccion de bots | Desactivado |
| `invalidchars` | Caracteres invalidos | Desactivado |
| `secureheaders` | Headers de seguridad | Desactivado |
| `cors` | CORS | Registrado |
| `forcehttps` | Forzar HTTPS | Activo en `required.before` |
| `pagecache` | Cache de paginas | Activo en `required` |
| `toolbar` | Debug toolbar (dev) | Activo en `required.after` |
| `performance` | Metricas de rendimiento | Activo en `required.after` |
