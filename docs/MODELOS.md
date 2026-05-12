# Modelos — Referencia detallada

Todos los modelos extienden `CodeIgniter\Model` y retornan arrays (`$returnType = 'array'`).

---

## UsuarioModel (`app/Models/UsuarioModel.php`)

| Propiedad | Valor |
|---|---|
| Tabla | `users` |
| Clave primaria | `id` |
| Timestamps | `created_at`, `updated_at` |

### Campos permitidos

`username`, `email`, `password_hash`, `name`, `last_name`, `role_id`, `is_active`

### Metodos personalizados

#### `getUsuariosConJoin($idExcluir = null)`

Lista todos los usuarios activos con JOIN a `roles` para obtener el nombre del rol (`tipo`). Excluye opcionalmente un usuario por ID (generalmente el usuario autenticado).

```sql
SELECT users.*, roles.name AS tipo
FROM users
JOIN roles ON roles.id = users.role_id
WHERE users.is_active = TRUE
  AND users.id != $idExcluir
```

---

## ServicesModel (`app/Models/ServicesModel.php`)

| Propiedad | Valor |
|---|---|
| Tabla | `services` |
| Clave primaria | `id` |
| Timestamps | `created_at`, `updated_at` |

### Campos permitidos

`description`, `idservice_classification`, `is_active`

### Metodos personalizados

#### `getServiciosConJoin()`

Lista servicios activos con JOIN a `services_classification` para obtener el tipo.

```sql
SELECT services.*, services_classification.name AS tipo
FROM services
JOIN services_classification ON services_classification.id = services.idservice_classification
WHERE services.is_active = TRUE
```

---

## TicketsModel (`app/Models/TicketsModel.php`)

| Propiedad | Valor |
|---|---|
| Tabla | `tickets` |
| Clave primaria | `id` |
| Timestamps | `created_at`, `updated_at` |

### Campos permitidos

`ticket_number`, `title`, `description`, `ticket_type`, `status`, `priority`, `service_id`, `created_by`, `assigned_to`, `closed_by`

### Metodos personalizados

#### `getTicketsConJoin()`

Lista todos los tickets con JOINs a:
- `users AS creator` (INNER JOIN — siempre existe)
- `users AS assigned` (LEFT JOIN — puede ser NULL)
- `users AS closed` (LEFT JOIN — puede ser NULL)
- `services AS s` (LEFT JOIN)

Campos calculados:
- `creado_por`: nombre completo del creador o `'Desconocido'`
- `asignado_a`: nombre del asignado o `'Aun no asignado'`
- `cerrado_por`: nombre de quien cerro o `'Aun no cerrado'`
- `service_name`: descripcion del servicio

#### `getUserticketConJoin($idusuario)`

Igual que `getTicketsConJoin()` pero filtrado por `tickets.created_by = $idusuario`.

#### `getSupportConJoin($idusuario)`

Igual que `getTicketsConJoin()` pero filtrado por `tickets.assigned_to = $idusuario`.

---

## NotasModel (`app/Models/NotasModel.php`)

| Propiedad | Valor |
|---|---|
| Tabla | `ticket_activities` |
| Clave primaria | `id` |
| Timestamps | Solo `created_at` (sin `updated_at`) |

### Campos permitidos

`ticket_id`, `actor_id`, `action`, `note_type`

### Metodos personalizados

#### `getNotasConJoin($ticketId)`

Lista las actividades de un ticket con JOINs a `tickets` (para `ticket_number`) y `users` (para `actor_name`). Ordenado por `created_at DESC`.

```sql
SELECT ticket_activities.*,
       tickets.ticket_number,
       CONCAT(users.name, ' ', users.last_name) AS actor_name
FROM ticket_activities
JOIN tickets ON tickets.id = ticket_activities.ticket_id
JOIN users ON users.id = ticket_activities.actor_id
WHERE ticket_activities.ticket_id = $ticketId
ORDER BY ticket_activities.created_at DESC
```

---

## RolesModel (`app/Models/RolesModel.php`)

| Propiedad | Valor |
|---|---|
| Tabla | `roles` |
| Clave primaria | `id` |
| Timestamps | No |

### Campos permitidos

`name`, `description`

---

## PermisosModel (`app/Models/PermisosModel.php`)

| Propiedad | Valor |
|---|---|
| Tabla | `role_permissions` |
| Timestamps | No |

### Campos permitidos

`role_id`, `permission_id`, `status`

---

## Modelos legacy (no utilizados activamente)

Los siguientes modelos estan presentes en el directorio `app/Models/` pero parecen pertenecer a una version anterior del proyecto:

| Modelo | Tabla | Observacion |
|---|---|---|
| `ClassServices.php` | — | Clase auxiliar anterior |
| `clientes.php` | `clientes` | No referenciado en controladores actuales |
| `pedidos.php` | `pedidos` | No referenciado en controladores actuales |
| `productos.php` | `productos` | No referenciado en controladores actuales |
| `categorias.php` | `categorias` | No referenciado en controladores actuales |
| `usuarios.php` | — | Modelo alternativo anterior |
| `ListServiciosModel.php` | — | Modelo anterior de servicios |
| `TiposUsuariosModel.php` | `tipos_usuarios` | Tabla reemplazada por `roles` |
| `EstadosUsuariosModel.php` | `estados_usuarios` | Tabla no presente en el esquema actual |
