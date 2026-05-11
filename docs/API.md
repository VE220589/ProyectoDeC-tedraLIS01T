# API REST — Referencia completa

Todas las rutas protegidas usan la sesion creada por `/api/auth/login` o `/api/auth/google`.
Las respuestas se entregan en JSON con la estructura:

```json
{
  "status": true,
  "message": "Descripcion del resultado",
  "dataset": []
}
```

En caso de error se incluye `exception` (solo en modo `development`).

---

## Convenciones

| Elemento | Detalle |
|---|---|
| Content-Type | `application/x-www-form-urlencoded` (POST con formularios) o `application/json` |
| Autenticacion | Cookie de sesion CI4 (`ci_session`) |
| Codigos HTTP | `200` exito, `400` datos faltantes, `401` no autenticado, `403` sin permisos, `404` no encontrado, `500` error interno |

---

## 1. Autenticacion (`Auth`)

### `POST /api/auth/login`

Login con usuario y contrasena.

| Parametro | Tipo | Requerido | Descripcion |
|---|---|---|---|
| `alias_usuario` | string | Si | Nombre de usuario |
| `clave_usuario` | string | Si | Contrasena |

**Respuestas:**

- `200` — `{"status": true, "message": "Autenticacion exitosa"}`
- `400` — Faltan campos.
- `401` — Credenciales incorrectas.
- `403` — Usuario inactivo.

### `POST /api/auth/google`

Login con Google Identity Services.

| Parametro | Tipo | Requerido | Descripcion |
|---|---|---|---|
| `credential` / `id_token` | string | Si | Token JWT de Google |

Requiere la variable de entorno `GOOGLE_CLIENT_ID`. Si el correo no existe en la BD se crea un usuario nuevo con rol `end_user`.

**Respuestas:**

- `200` — `{"status": true, "message": "Autenticacion con Google exitosa"}`
- `400` — No se recibio credencial.
- `401` — Token invalido.
- `403` — Cuenta inactiva.

### `GET /api/auth/logOut`

Cierra la sesion activa. **Requiere autenticacion.**

**Respuesta:** `{"status": true, "message": "Sesion cerrada correctamente"}`

### `GET /api/auth/exists`

Verifica si existen usuarios registrados (publica).

**Respuesta:** `{"status": true/false, "message": "..."}`

### `GET /api/auth/index`

Lista todos los usuarios (datos basicos). **Permiso:** `users.view`.

**Respuesta:** `{"status": true, "dataset": [...]}`

---

## 2. Usuarios (`Usuarios`)

Todas las rutas estan bajo el prefijo `/api/usuarios/`.

### `GET /api/usuarios/index`

Lista todos los usuarios activos (excepto el usuario autenticado) con JOIN a roles.

**Permiso:** `users.view`

**Respuesta:**
```json
{
  "status": true,
  "dataset": [
    {
      "id": 1,
      "username": "admin1",
      "email": "admin1@sistema.com",
      "name": "Oscar",
      "last_name": "Villalobos",
      "role_id": 1,
      "is_active": true,
      "tipo": "admin"
    }
  ]
}
```

### `POST /api/usuarios/readOne`

Lee un usuario por ID.

| Parametro | Tipo | Requerido |
|---|---|---|
| `id_usuario` | int | Si |

**Permiso:** `users.view`

### `GET /api/usuarios/readPerfil`

Lee el perfil del usuario autenticado (sin `password_hash`). **Requiere sesion activa.**

### `POST /api/usuarios/create`

Crea un nuevo usuario.

| Parametro | Tipo | Requerido | Validacion |
|---|---|---|---|
| `nombres_usuario` | string | Si | Solo letras y espacios, 3-50 chars |
| `apellidos_usuario` | string | Si | Solo letras y espacios, 3-50 chars |
| `correo_usuario` | string | Si | Email valido, max 100 chars |
| `alias_usuario` | string | Si | Alfanumerico, 3-20 chars |
| `clave_usuario` | string | Si | Min 8 chars |
| `confirmar_clave` | string | Si | Debe coincidir con `clave_usuario` |
| `id_tipo` | int | Si | ID del rol |

**Permiso:** `users.create`

### `POST /api/usuarios/update`

Actualiza un usuario existente.

| Parametro | Tipo | Requerido | Descripcion |
|---|---|---|---|
| `id_usuario` | int | Si | ID del usuario a modificar |
| `nombres_usuario` | string | Si | Nombres |
| `apellidos_usuario` | string | Si | Apellidos |
| `correo_usuario` | string | Si | Email |
| `alias_usuario` | string | No | Username |
| `id_tipo` | int | Si | ID del rol |
| `clave_usuario` | string | No | Nueva contrasena (opcional) |

**Permiso:** `users.update`

### `POST /api/usuarios/updatePerfil`

Permite al usuario autenticado actualizar su propio perfil. Requiere `clave_actual` si se desea cambiar la contrasena (excepto usuarios de Google).

| Parametro | Tipo | Requerido |
|---|---|---|
| `id_usuario` | int | Si |
| `nombres_usuario` | string | Si |
| `apellidos_usuario` | string | Si |
| `correo_usuario` | string | Si |
| `alias_usuario` | string | Si |
| `clave_actual` | string | Solo si cambia clave |
| `clave_usuario` | string | No |
| `confirmar_clave` | string | Solo si cambia clave |

**Requiere sesion activa.** Solo puede modificar su propio perfil (`403` si intenta otro).

### `POST /api/usuarios/deletelogic`

Baja logica: marca `is_active = false`.

| Parametro | Tipo | Requerido |
|---|---|---|
| `id_usuario` | int | Si |

**Permiso:** `users.delete`

### `POST /api/usuarios/delete`

Eliminacion fisica del registro.

| Parametro | Tipo | Requerido |
|---|---|---|
| `id_usuario` | int | Si |

**Permiso:** `users.delete`

### `POST /api/usuarios/search`

Busca usuarios por nombre, apellido o username.

| Parametro | Tipo | Requerido |
|---|---|---|
| `search` | string | Si |

**Permiso:** `users.view`

### `GET /api/usuarios/getTipo`

Devuelve la lista de roles disponibles (`id`, `nombre`). **Permiso:** `users.view`.

### `GET /api/usuarios/getEstado`

Devuelve la lista de estados de usuario. **Permiso:** `users.view`.

---

## 3. Servicios (`Services`)

Todas las rutas estan bajo el prefijo `/api/services/`.

### `GET /api/services/index`

Lista servicios activos con JOIN a `services_classification`.

**Permiso:** `services.view`

**Respuesta:**
```json
{
  "status": true,
  "dataset": [
    {
      "id": 1,
      "description": "Red corporativa",
      "idservice_classification": 1,
      "is_active": true,
      "tipo": "infrastructure"
    }
  ]
}
```

### `POST /api/services/readOne`

| Parametro | Tipo | Requerido |
|---|---|---|
| `id_servicio` | int | Si |

**Permiso:** `services.view`

### `POST /api/services/create`

| Parametro | Tipo | Requerido | Validacion |
|---|---|---|---|
| `desc` | string | Si | 3-50 chars |
| `id_tipo` | int | Si | ID clasificacion |

**Permiso:** `services.create`

### `POST /api/services/update`

| Parametro | Tipo | Requerido |
|---|---|---|
| `id_servicio` | int | Si |
| `desc` | string | Si |
| `id_tipo` | int | Si |

**Permiso:** `services.update`

### `POST /api/services/deletelogic`

Baja logica del servicio (`is_active = false`).

| Parametro | Tipo | Requerido |
|---|---|---|
| `id_servicio` | int | Si |

**Permiso:** `services.delete`

### `POST /api/services/search`

Busca servicios por descripcion.

| Parametro | Tipo | Requerido |
|---|---|---|
| `search` | string | Si |

**Permiso:** `services.view`

### `GET /api/services/getTipo`

Devuelve clasificaciones de servicio (`id`, `nombre`). **Permiso:** `services.view`.

---

## 4. Tickets (`Tickets`)

Todas las rutas estan bajo el prefijo `/api/tickets/`.

### `GET /api/tickets/index`

Lista todos los tickets con JOINs a usuarios (creador, asignado, cerrador) y servicio.

**Permiso:** `tickets.view`

**Respuesta:**
```json
{
  "status": true,
  "dataset": [
    {
      "id": 1,
      "ticket_number": "TCK-000001",
      "title": "Falla al conectarse a la VPN",
      "description": "...",
      "ticket_type": "incident",
      "status": "open",
      "priority": "A",
      "service_name": "Red corporativa",
      "creado_por": "Carlos Lopez",
      "asignado_a": "Juan Perez",
      "cerrado_por": "Aun no cerrado"
    }
  ]
}
```

### `GET /api/tickets/supporTickets`

Lista tickets asignados al usuario de soporte autenticado. **Permiso:** `tickets.view`.

### `GET /api/tickets/userTickets`

Lista tickets creados por el usuario autenticado. **Permiso:** `tickets.view`.

### `POST /api/tickets/readOne`

| Parametro | Tipo | Requerido |
|---|---|---|
| `id_ticket` | int | Si |

**Permiso:** `tickets.view`

### `POST /api/tickets/create`

Crea un ticket y genera automaticamente el numero (`TCK-XXXXXX`).

| Parametro | Tipo | Requerido | Validacion |
|---|---|---|---|
| `title` | string | Si | 5-250 chars |
| `desc` | string | Si | 3-250 chars |
| `id_servicio` | int | Si | ID del servicio |
| `id_tipo_ticket` | string | No | `incident`, `problem`, `change` |
| `prioridad` | string | No | `C`, `B`, `A`, `S` |
| `id_asignado` | int | No | ID del usuario soporte |

**Permiso:** `tickets.create`

**Respuesta:** `{"status": true, "message": "Ticket generado correctamente", "ticket_number": "TCK-000001"}`

### `POST /api/tickets/update`

Actualiza un ticket. Si el ticket ya tiene un usuario asignado y no se envia `id_asignado`, se mantiene el actual.

| Parametro | Tipo | Requerido |
|---|---|---|
| `id_ticket` | int | Si |
| `title` | string | Si |
| `desc` | string | Si |
| `id_servicio` | int | Si |
| `id_tipo_ticket` | string | No |
| `prioridad` | string | No |
| `estado` | string | No |
| `id_asignado` | int | No |

**Permiso:** `tickets.update`

### `POST /api/tickets/deletelogic`

Cierra el ticket (`status = closed`, `closed_by = usuario actual`) y registra una nota de cierre con `note_type = approved`.

| Parametro | Tipo | Requerido |
|---|---|---|
| `id_ticket` | int | Si |

**Permiso:** `tickets.delete`

### `POST /api/tickets/delete`

Eliminacion fisica del ticket.

| Parametro | Tipo | Requerido |
|---|---|---|
| `id_ticket` | int | Si |

**Permiso:** `tickets.delete`

### `POST /api/tickets/search`

Busca tickets por nombre, apellido o username del creador.

| Parametro | Tipo | Requerido |
|---|---|---|
| `search` | string | Si |

**Permiso:** `tickets.view`

### `GET /api/tickets/getServices`

Devuelve lista de servicios disponibles (`id`, `desc`). **Permiso:** `tickets.view`.

### `GET /api/tickets/getUsuarios`

Devuelve lista de usuarios con rol `support` (role_id = 2). **Permiso:** `tickets.view`.

---

## 5. Notas / Actividades de ticket (`Notas`)

Todas las rutas estan bajo el prefijo `/api/notas/`.

### `POST /api/notas/index`

Lista las notas (actividades) de un ticket con JOINs a usuario y ticket.

| Parametro | Tipo | Requerido |
|---|---|---|
| `id_ticketnota` | int | Si |

**Permiso:** `tickets.view`

**Respuesta:**
```json
{
  "status": true,
  "dataset": [
    {
      "id": 1,
      "ticket_id": 1,
      "actor_id": 5,
      "action": "El ticket fue asignado al tecnico Juan Perez.",
      "note_type": "comment",
      "ticket_number": "TCK-000001",
      "actor_name": "Juan Perez"
    }
  ]
}
```

### `POST /api/notas/create`

Agrega una nota al ticket. Si el ticket esta en estado `open`, lo cambia automaticamente a `in_progress`.

| Parametro | Tipo | Requerido |
|---|---|---|
| `id_ticketnota` | int | Si |
| `descnote` | string | Si |

**Permiso:** `tickets.update`

### `POST /api/notas/createRequest`

Solicita el cierre del ticket: crea una nota con `note_type = request` y cambia el estado a `mitigated`.

| Parametro | Tipo | Requerido |
|---|---|---|
| `id_ticketnota` | int | Si |

**Permiso:** `tickets.update`

---

## 6. Roles (`Rolest`)

### `GET /api/rolest/index`

Devuelve todos los roles registrados. **Permiso:** `roles.view`.

---

## 7. Permisos (`Permisos`)

Todas las rutas estan bajo el prefijo `/api/permisos/`.

### `POST /api/permisos/readByRoleAndModule`

Consulta los permisos de un rol para un modulo especifico.

| Parametro | Tipo | Requerido | Ejemplo |
|---|---|---|---|
| `role_id` | int | Si | `1` |
| `module` | string | Si | `users`, `tickets`, `services`, `roles` |

**Permiso:** `roles.view`

**Respuesta:**
```json
{
  "status": true,
  "dataset": [
    {"permission_id": 1, "name": "users.create", "status": true},
    {"permission_id": 2, "name": "users.view", "status": true},
    {"permission_id": 3, "name": "users.update", "status": true},
    {"permission_id": 4, "name": "users.delete", "status": false}
  ]
}
```

### `POST /api/permisos/updateByRoleAndModule`

Actualiza los permisos de un rol para un modulo. Si el rol modificado es el del usuario autenticado, los permisos en sesion se actualizan inmediatamente.

| Parametro | Tipo | Requerido | Descripcion |
|---|---|---|---|
| `role_id` | int | Si | ID del rol |
| `modulo` | string | Si | Nombre del modulo |
| `create` | `"1"` / `"0"` | Si | Permiso crear |
| `view` | `"1"` / `"0"` | Si | Permiso ver |
| `update` | `"1"` / `"0"` | Si | Permiso actualizar |
| `delete` | `"1"` / `"0"` | Si | Permiso eliminar |

**Permiso:** `roles.update`

---

## Despliegue

### Docker Compose (local)

```bash
docker compose up --build
```

Aplicacion disponible en `http://localhost:8080`.

### Kubernetes

```bash
kubectl apply -f k8s/namespace.yaml
kubectl create configmap postgres-init \
  --from-file=01-schema.sql=scriptdelabase.sql \
  -n tmnotes --dry-run=client -o yaml | kubectl apply -f -
kubectl apply -f k8s/postgres.yaml
kubectl apply -f k8s/app.yaml
```

La aplicacion queda expuesta en el `NodePort 30080`.
