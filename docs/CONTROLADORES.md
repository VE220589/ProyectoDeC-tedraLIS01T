# Controladores — Referencia detallada

Todos los controladores API extienden `CodeIgniter\RESTful\ResourceController` con `$format = 'json'`.
Los controladores de vistas extienden `BaseController`.

---

## Auth (`app/Controllers/Auth.php`)

Gestiona autenticacion local y con Google.

### Metodos publicos

| Metodo | Ruta | Descripcion |
|---|---|---|
| `login()` | `POST /api/auth/login` | Login con `alias_usuario` y `clave_usuario` |
| `google()` | `POST /api/auth/google` | Login con token JWT de Google |
| `logOut()` | `GET /api/auth/logOut` | Cierre de sesion |
| `exists()` | `GET /api/auth/exists` | Verifica si hay usuarios registrados |
| `index()` | `GET /api/auth/index` | Lista todos los usuarios |

### Metodos privados

| Metodo | Descripcion |
|---|---|
| `startUserSession()` | Regenera sesion, carga permisos del rol y establece datos de sesion |
| `verifyGoogleCredential()` | Valida token con Google tokeninfo API |
| `getDefaultRoleId()` | Obtiene ID del rol `end_user` para nuevos usuarios Google |
| `buildGoogleUsername()` | Genera username unico a partir del email |
| `isActive()` | Normaliza el campo `is_active` (bool, string, int) |

### Flujo de login con Google

1. Recibe el `credential` (JWT) del frontend.
2. Valida el token contra `https://oauth2.googleapis.com/tokeninfo`.
3. Verifica que `aud` coincida con `GOOGLE_CLIENT_ID` y que el email este verificado.
4. Si el usuario no existe, lo crea con rol `end_user`.
5. Inicia sesion con `startUserSession()`.

---

## Usuarios (`app/Controllers/Usuarios.php`)

CRUD completo de usuarios.

### Metodos

| Metodo | Ruta | Permiso | Descripcion |
|---|---|---|---|
| `index()` | `GET /api/usuarios/index` | `users.view` | Lista usuarios activos (excluye al usuario actual) |
| `readOne()` | `POST /api/usuarios/readOne` | `users.view` | Lee usuario por `id_usuario` |
| `readPerfil()` | `GET /api/usuarios/readPerfil` | sesion | Lee perfil propio (sin password_hash) |
| `create()` | `POST /api/usuarios/create` | `users.create` | Crea usuario con validacion completa |
| `update()` | `POST /api/usuarios/update` | `users.update` | Actualiza datos de usuario |
| `updatePerfil()` | `POST /api/usuarios/updatePerfil` | sesion | Actualiza perfil propio (valida clave actual) |
| `deletelogic()` | `POST /api/usuarios/deletelogic` | `users.delete` | Baja logica (`is_active = false`) |
| `delete()` | `POST /api/usuarios/delete` | `users.delete` | Eliminacion fisica |
| `search()` | `POST /api/usuarios/search` | `users.view` | Busca por nombre, apellido o username |
| `getTipo()` | `GET /api/usuarios/getTipo` | `users.view` | Lista roles disponibles |
| `getEstado()` | `GET /api/usuarios/getEstado` | `users.view` | Lista estados de usuario |

### Validaciones de creacion

| Campo | Regla |
|---|---|
| `nombres_usuario` | Requerido, solo letras Unicode y espacios, 3-50 chars |
| `apellidos_usuario` | Requerido, solo letras Unicode y espacios, 3-50 chars |
| `correo_usuario` | Requerido, email valido, max 100 chars |
| `alias_usuario` | Requerido, alfanumerico, 3-20 chars |
| `clave_usuario` | Requerido, min 8 chars |
| `confirmar_clave` | Requerido, debe coincidir con `clave_usuario` |
| `id_tipo` | Requerido, entero |

---

## Services (`app/Controllers/Services.php`)

CRUD de servicios de TI.

### Metodos

| Metodo | Ruta | Permiso | Descripcion |
|---|---|---|---|
| `index()` | `GET /api/services/index` | `services.view` | Lista servicios activos con clasificacion |
| `readOne()` | `POST /api/services/readOne` | `services.view` | Lee servicio por `id_servicio` |
| `create()` | `POST /api/services/create` | `services.create` | Crea servicio |
| `update()` | `POST /api/services/update` | `services.update` | Actualiza servicio |
| `deletelogic()` | `POST /api/services/deletelogic` | `services.delete` | Baja logica |
| `search()` | `POST /api/services/search` | `services.view` | Busca por descripcion |
| `getTipo()` | `GET /api/services/getTipo` | `services.view` | Lista clasificaciones |

---

## Tickets (`app/Controllers/Tickets.php`)

Gestion de tickets de soporte.

### Metodos

| Metodo | Ruta | Permiso | Descripcion |
|---|---|---|---|
| `index()` | `GET /api/tickets/index` | `tickets.view` | Lista todos los tickets con JOINs |
| `supporTickets()` | `GET /api/tickets/supporTickets` | `tickets.view` | Tickets asignados al usuario |
| `userTickets()` | `GET /api/tickets/userTickets` | `tickets.view` | Tickets creados por el usuario |
| `readOne()` | `POST /api/tickets/readOne` | `tickets.view` | Lee ticket por `id_ticket` |
| `create()` | `POST /api/tickets/create` | `tickets.create` | Crea ticket y genera `TCK-XXXXXX` |
| `update()` | `POST /api/tickets/update` | `tickets.update` | Actualiza ticket |
| `deletelogic()` | `POST /api/tickets/deletelogic` | `tickets.delete` | Cierra ticket con nota `approved` |
| `delete()` | `POST /api/tickets/delete` | `tickets.delete` | Eliminacion fisica |
| `search()` | `POST /api/tickets/search` | `tickets.view` | Busca por datos del creador |
| `getServices()` | `GET /api/tickets/getServices` | `tickets.view` | Lista servicios para formulario |
| `getUsuarios()` | `GET /api/tickets/getUsuarios` | `tickets.view` | Lista usuarios soporte (role_id=2) |

### Logica de asignacion en `update()`

1. Si el ticket ya tiene `assigned_to` y no se envia `id_asignado`: se mantiene el actual.
2. Si se envia `id_asignado`: se asigna el nuevo usuario.
3. Si no tenia y no viene: queda `NULL`.

---

## Notas (`app/Controllers/Notas.php`)

Gestion de actividades/notas de tickets.

### Metodos

| Metodo | Ruta | Permiso | Descripcion |
|---|---|---|---|
| `index()` | `POST /api/notas/index` | `tickets.view` | Lista notas de un ticket |
| `create()` | `POST /api/notas/create` | `tickets.update` | Agrega nota (open → in_progress) |
| `createRequest()` | `POST /api/notas/createRequest` | `tickets.update` | Solicita cierre (→ mitigated) |

---

## Rolest (`app/Controllers/Rolest.php`)

### Metodos

| Metodo | Ruta | Permiso | Descripcion |
|---|---|---|---|
| `index()` | `GET /api/rolest/index` | `roles.view` | Lista todos los roles |

---

## Permisos (`app/Controllers/Permisos.php`)

Gestion de permisos por rol y modulo.

### Metodos

| Metodo | Ruta | Permiso | Descripcion |
|---|---|---|---|
| `readByRoleAndModule()` | `POST /api/permisos/readByRoleAndModule` | `roles.view` | Lee permisos de un rol para un modulo |
| `updateByRoleAndModule()` | `POST /api/permisos/updateByRoleAndModule` | `roles.update` | Actualiza permisos y refresca sesion si aplica |

---

## Controladores de vistas

Estos controladores simplemente renderizan las vistas PHP:

| Controlador | Metodo | Ruta | Vista |
|---|---|---|---|
| `Dashboard` | `index()` | `/`, `/dashboard` | `dashboard/index` |
| `Main` | `main()` | `/main` | `dashboard/main` |
| `Usuarios1` | `usuarios1()` | `/usuarios1` | `dashboard/usuarios` |
| `Roles` | `roles()` | `/roles` | `dashboard/roles` |
| `Perfil` | `perfil()` | `/perfil` | `dashboard/perfil` |
| `Servicios` | `servicios()` | `/servicios` | `dashboard/servicios` |
| `Ticket` | `ticket()` | `/ticket` | `dashboard/ticket` |
