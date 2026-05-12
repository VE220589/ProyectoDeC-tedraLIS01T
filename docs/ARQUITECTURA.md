# Arquitectura del proyecto

## Vision general

**TMNotes** es una aplicacion web de gestion de soporte tecnico construida con **CodeIgniter 4** (PHP 8.1+) y **PostgreSQL 16**. Permite administrar usuarios, roles, permisos, servicios, tickets de soporte y notas de actividad.

```
┌─────────────┐     HTTP      ┌──────────────────────┐      SQL       ┌─────────────┐
│   Browser   │ ◄───────────► │   CodeIgniter 4      │ ◄────────────► │ PostgreSQL  │
│  (Frontend) │   JSON / HTML │   (PHP 8.3 + Apache) │   pdo_pgsql    │     16      │
└─────────────┘               └──────────────────────┘                └─────────────┘
```

---

## Estructura de directorios

```
ProyectoDeC-tedraLIS01T/
├── app/
│   ├── Config/           # Configuracion CI4 (Routes, Database, Filters, etc.)
│   ├── Controllers/      # Controladores REST y vistas
│   ├── Filters/          # Middleware de autenticacion y permisos
│   ├── Helpers/          # Funciones auxiliares (database.php, validator.php, etc.)
│   ├── Models/           # Modelos Eloquent-style de CI4
│   ├── Views/            # Vistas PHP (layouts y dashboard)
│   ├── Language/         # Archivos de idioma
│   ├── Libraries/        # Librerias personalizadas
│   ├── ThirdParty/       # Codigo de terceros
│   └── Database/         # Migraciones y seeds
├── public/               # Punto de entrada web (index.php, assets)
├── writable/             # Logs, cache, sesiones (CI4)
├── tests/                # Tests PHPUnit
├── docker/               # Configuracion Apache para Docker
├── k8s/                  # Manifiestos Kubernetes
├── docs/                 # Documentacion
├── scriptdelabase.sql    # Script de creacion de BD + seed
├── docker-compose.yml    # Orquestacion local
├── Dockerfile            # Imagen de produccion
├── composer.json         # Dependencias PHP
└── .env.example          # Variables de entorno de referencia
```

---

## Capas de la aplicacion

### 1. Rutas (`app/Config/Routes.php`)

Define todas las rutas de la aplicacion organizadas por modulo:

| Prefijo | Controlador | Descripcion |
|---|---|---|
| `/` y `/dashboard` | `Dashboard` | Pagina de login (filtro `guest`) |
| `/main` | `Main` | Dashboard principal (filtro `auth`) |
| `/api/auth/` | `Auth` | Login, logout, Google login |
| `/api/usuarios/` | `Usuarios` | CRUD de usuarios |
| `/api/services/` | `Services` | CRUD de servicios |
| `/api/tickets/` | `Tickets` | CRUD de tickets |
| `/api/notas/` | `Notas` | Notas de actividad |
| `/api/rolest/` | `Rolest` | Consulta de roles |
| `/api/permisos/` | `Permisos` | Gestion de permisos por rol |
| `/usuarios1` | `Usuarios1` | Vista de usuarios |
| `/roles` | `Roles` | Vista de roles |
| `/perfil` | `Perfil` | Vista de perfil |
| `/servicios` | `Servicios` | Vista de servicios |
| `/ticket` | `Ticket` | Vista de tickets |

### 2. Controladores (`app/Controllers/`)

Todos los controladores API extienden `ResourceController` y retornan JSON (`$format = 'json'`).

| Controlador | Tabla principal | Responsabilidades |
|---|---|---|
| `Auth` | `users`, `roles` | Login local/Google, logout, sesion |
| `Usuarios` | `users` | CRUD usuarios, perfil, busqueda |
| `Services` | `services` | CRUD servicios, busqueda |
| `Tickets` | `tickets` | CRUD tickets, asignacion, cierre |
| `Notas` | `ticket_activities` | Notas, solicitud de cierre |
| `Rolest` | `roles` | Listado de roles |
| `Permisos` | `role_permissions` | Permisos CRUD por modulo y rol |

### 3. Modelos (`app/Models/`)

| Modelo | Tabla | Campos permitidos |
|---|---|---|
| `UsuarioModel` | `users` | `username`, `email`, `password_hash`, `name`, `last_name`, `role_id`, `is_active` |
| `ServicesModel` | `services` | `description`, `idservice_classification`, `is_active` |
| `TicketsModel` | `tickets` | `ticket_number`, `title`, `description`, `ticket_type`, `status`, `priority`, `service_id`, `created_by`, `assigned_to`, `closed_by` |
| `NotasModel` | `ticket_activities` | `ticket_id`, `actor_id`, `action`, `note_type` |
| `RolesModel` | `roles` | `name`, `description` |
| `PermisosModel` | `role_permissions` | `role_id`, `permission_id`, `status` |

### 4. Filtros / Middleware (`app/Filters/`)

| Filtro | Alias | Descripcion |
|---|---|---|
| `AuthFilter` | `auth` | Verifica sesion activa y permisos. Soporta timeout por inactividad. |
| `GuestFilter` | `guest` | Redirige a `/main` si el usuario ya tiene sesion. |

### 5. Vistas (`app/Views/`)

| Vista | Ruta | Descripcion |
|---|---|---|
| `dashboard/index.php` | `/`, `/dashboard` | Pagina de login |
| `dashboard/main.php` | `/main` | Dashboard principal |
| `dashboard/usuarios.php` | `/usuarios1` | Gestion de usuarios |
| `dashboard/roles.php` | `/roles` | Gestion de roles y permisos |
| `dashboard/perfil.php` | `/perfil` | Perfil del usuario |
| `dashboard/servicios.php` | `/servicios` | Gestion de servicios |
| `dashboard/ticket.php` | `/ticket` | Gestion de tickets |
| `Layouts/dashboard_main.php` | — | Layout base autenticado |
| `Layouts/dashboard_public.php` | — | Layout base publico |

---

## Flujo de autenticacion

```
  Usuario                    Auth Controller              Base de datos
    │                              │                            │
    │  POST /api/auth/login        │                            │
    │  {alias_usuario, clave}      │                            │
    │─────────────────────────────►│                            │
    │                              │  SELECT users + roles      │
    │                              │───────────────────────────►│
    │                              │◄───────────────────────────│
    │                              │                            │
    │                              │  password_verify()         │
    │                              │  SELECT permissions        │
    │                              │───────────────────────────►│
    │                              │◄───────────────────────────│
    │                              │                            │
    │                              │  session()->set(...)       │
    │  {status: true}              │                            │
    │◄─────────────────────────────│                            │
```

### Datos almacenados en sesion

| Clave | Descripcion |
|---|---|
| `id_usuario` | ID del usuario |
| `alias_usuario` | Nombre de usuario |
| `tipo_usuario` | Nombre del rol |
| `role_id` | ID del rol |
| `permissions` | Array de permisos activos (ej. `['users.view', 'users.create']`) |
| `auth_provider` | `'local'` o `'google'` |
| `login` | `true` |
| `last_activity_at` | Timestamp de ultima actividad |

---

## Flujo de vida de un ticket

```
                  ┌──────────┐
                  │   open   │
                  └────┬─────┘
                       │ Se agrega nota
                       ▼
               ┌──────────────┐
               │ in_progress  │
               └──────┬───────┘
                      │ Usuario solicita cierre
                      ▼
               ┌──────────────┐
               │  mitigated   │
               └──────┬───────┘
                      │ Soporte/Admin aprueba cierre
                      ▼
               ┌──────────────┐
               │   closed     │
               └──────────────┘
```

- **open → in_progress**: Automatico al agregar la primera nota (`Notas::create`).
- **in_progress → mitigated**: El usuario solicita cierre (`Notas::createRequest`).
- **mitigated → closed**: Soporte/Admin cierra el ticket (`Tickets::deletelogic`).

---

## Sistema de permisos

Los permisos siguen el patron `modulo.accion`:

| Modulo | Acciones |
|---|---|
| `users` | `create`, `view`, `update`, `delete` |
| `roles` | `create`, `view`, `update`, `delete` |
| `services` | `create`, `view`, `update`, `delete` |
| `tickets` | `create`, `view`, `update`, `delete` |

### Permisos por defecto

| Rol | Permisos |
|---|---|
| `admin` | Todos los permisos activos |
| `support` | `tickets.view`, `tickets.update`, `services.view` |
| `end_user` | `tickets.view`, `tickets.create`, `tickets.update`, `services.view` |

Los permisos se verifican en `AuthFilter` al procesar cada request. Se almacenan en la tabla `role_permissions` y se pueden modificar dinamicamente desde la interfaz de roles.
