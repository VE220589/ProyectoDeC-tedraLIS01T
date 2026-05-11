# Base de datos

## Motor

PostgreSQL 16 (Alpine en Docker/K8s). Nombre de la base de datos: `tmnotes`.

El script de inicializacion completo esta en `scriptdelabase.sql` (raiz del proyecto).

---

## Diagrama entidad-relacion

```
┌──────────────┐       ┌──────────────────┐       ┌──────────────────────────┐
│    roles     │       │   permissions    │       │    role_permissions      │
├──────────────┤       ├──────────────────┤       ├──────────────────────────┤
│ id (PK)      │◄──┐   │ id (PK)          │◄──┐   │ role_id (PK, FK→roles)   │
│ name         │   │   │ name             │   │   │ permission_id (PK, FK)   │
│ description  │   │   │ description      │   │   │ status                   │
│ created_at   │   │   └──────────────────┘   │   └──────────────────────────┘
└──────────────┘   │                          │
                   │                          │
       ┌───────────┘                          │
       │                                      │
┌──────┴───────┐                              │
│    users     │                              │
├──────────────┤                              │
│ id (PK)      │◄─────────────────────────────┘
│ username     │
│ email        │       ┌──────────────────────────────┐
│ password_hash│       │ services_classification      │
│ name         │       ├──────────────────────────────┤
│ last_name    │       │ id (PK)                      │
│ role_id (FK) │       │ name                         │
│ is_active    │       │ description                  │
│ created_at   │       │ created_at                   │
│ updated_at   │       └───────────┬──────────────────┘
└──────┬───────┘                   │
       │                           │
       │              ┌────────────┴──────┐
       │              │    services       │
       │              ├───────────────────┤
       │              │ id (PK)           │
       │              │ description       │
       │              │ idservice_class.. │
       │              │ is_active         │
       │              │ created_at        │
       │              │ updated_at        │
       │              └────────┬──────────┘
       │                       │
       │          ┌────────────┘
       │          │
┌──────┴──────────┴───────────────────────────┐
│                 tickets                      │
├──────────────────────────────────────────────┤
│ id (PK)                                      │
│ ticket_number (UNIQUE)                       │
│ title                                        │
│ description                                  │
│ ticket_type   (ENUM: incident/problem/change)│
│ status        (ENUM: open/in_progress/       │
│                mitigated/closed)             │
│ priority      (ENUM: C/B/A/S)               │
│ service_id    (FK → services)               │
│ created_by    (FK → users, NOT NULL)        │
│ assigned_to   (FK → users, NULL)            │
│ closed_by     (FK → users, NULL)            │
│ created_at                                   │
│ updated_at                                   │
│ closed_at                                    │
└──────────┬───────────────────────────────────┘
           │
           │
┌──────────┴──────────────────────────────────┐
│           ticket_activities                  │
├──────────────────────────────────────────────┤
│ id (PK)                                      │
│ ticket_id   (FK → tickets, ON DELETE CASCADE)│
│ actor_id    (FK → users)                    │
│ action      (VARCHAR 100)                   │
│ note_type   (ENUM: comment/request/approved)│
│ created_at                                   │
└──────────────────────────────────────────────┘
```

---

## Tablas

### `roles`

| Columna | Tipo | Restriccion |
|---|---|---|
| `id` | SERIAL | PK |
| `name` | VARCHAR(50) | NOT NULL, UNIQUE |
| `description` | TEXT | — |
| `created_at` | TIMESTAMPTZ | DEFAULT now() |

**Seed inicial:** `admin`, `support`, `end_user`.

### `users`

| Columna | Tipo | Restriccion |
|---|---|---|
| `id` | SERIAL | PK |
| `username` | VARCHAR(25) | NOT NULL, UNIQUE |
| `email` | VARCHAR(100) | UNIQUE |
| `password_hash` | TEXT | NOT NULL |
| `name` | VARCHAR(30) | NOT NULL |
| `last_name` | VARCHAR(30) | NOT NULL |
| `role_id` | INT | FK → roles(id), NOT NULL |
| `is_active` | BOOLEAN | DEFAULT TRUE |
| `created_at` | TIMESTAMPTZ | DEFAULT now() |
| `updated_at` | TIMESTAMPTZ | DEFAULT now() |

**Seed inicial:** 4 admins, 6 soporte, 10 usuarios finales. Contrasena de seed: hash bcrypt comun.

### `permissions`

| Columna | Tipo | Restriccion |
|---|---|---|
| `id` | SERIAL | PK |
| `name` | VARCHAR(100) | NOT NULL, UNIQUE |
| `description` | TEXT | — |

**Seed:** se generan 16 permisos (`modulo.accion`) para los modulos `users`, `roles`, `services`, `tickets`.

### `role_permissions`

| Columna | Tipo | Restriccion |
|---|---|---|
| `role_id` | INT | PK, FK → roles(id), ON DELETE CASCADE |
| `permission_id` | INT | PK, FK → permissions(id), ON DELETE CASCADE |
| `status` | BOOLEAN | DEFAULT FALSE |

### `services_classification`

| Columna | Tipo | Restriccion |
|---|---|---|
| `id` | SERIAL | PK |
| `name` | VARCHAR(50) | NOT NULL, UNIQUE |
| `description` | TEXT | — |
| `created_at` | TIMESTAMPTZ | DEFAULT now() |

**Seed:** `infrastructure`, `applications`, `hardware`.

### `services`

| Columna | Tipo | Restriccion |
|---|---|---|
| `id` | SERIAL | PK |
| `description` | VARCHAR(50) | — |
| `idservice_classification` | INT | FK → services_classification(id), NOT NULL |
| `is_active` | BOOLEAN | DEFAULT TRUE |
| `created_at` | TIMESTAMPTZ | DEFAULT now() |
| `updated_at` | TIMESTAMPTZ | DEFAULT now() |

**Seed:** 8 servicios de ejemplo.

### `tickets`

| Columna | Tipo | Restriccion |
|---|---|---|
| `id` | SERIAL | PK |
| `ticket_number` | VARCHAR(30) | UNIQUE, DEFAULT NULL |
| `title` | VARCHAR(255) | NOT NULL |
| `description` | TEXT | — |
| `ticket_type` | ENUM | `incident`, `problem`, `change`. Default: `incident` |
| `status` | ENUM | `open`, `in_progress`, `mitigated`, `closed`. Default: `open` |
| `priority` | ENUM | `C`, `B`, `A`, `S`. Default: `C` |
| `service_id` | INT | FK → services(id) |
| `created_by` | INT | FK → users(id), NOT NULL |
| `assigned_to` | INT | FK → users(id), NULL |
| `closed_by` | INT | FK → users(id), NULL |
| `created_at` | TIMESTAMPTZ | DEFAULT now() |
| `updated_at` | TIMESTAMPTZ | DEFAULT now() |
| `closed_at` | TIMESTAMPTZ | NULL |

**Indices:** `created_by`, `assigned_to`, `status`, `priority`, `service_id`, `ticket_number`.

### `ticket_activities`

| Columna | Tipo | Restriccion |
|---|---|---|
| `id` | SERIAL | PK |
| `ticket_id` | SERIAL | FK → tickets(id), ON DELETE CASCADE, NOT NULL |
| `actor_id` | SERIAL | FK → users(id) |
| `action` | VARCHAR(100) | NOT NULL |
| `note_type` | ENUM | `comment`, `request`, `approved`. Default: `comment` |
| `created_at` | TIMESTAMPTZ | DEFAULT now() |

### `ci_sessions`

Tabla para sesiones persistentes de CodeIgniter (si se usa `DatabaseHandler`).

| Columna | Tipo | Restriccion |
|---|---|---|
| `id` | VARCHAR(128) | PK (compuesta con `ip_address`) |
| `ip_address` | VARCHAR(45) | PK |
| `timestamp` | INT | DEFAULT 0, NOT NULL |
| `data` | BYTEA | NOT NULL |

---

## Tipos enumerados (ENUM)

| Nombre | Valores |
|---|---|
| `ticket_type` | `incident`, `problem`, `change` |
| `ticket_status` | `open`, `in_progress`, `mitigated`, `closed` |
| `ticket_priority` | `C`, `B`, `A`, `S` |
| `note_type` | `comment`, `request`, `approved` |

---

## Datos de seed

El script `scriptdelabase.sql` carga datos iniciales para facilitar pruebas:

| Entidad | Cantidad | Detalle |
|---|---|---|
| Roles | 3 | admin, support, end_user |
| Permisos | 16 | 4 modulos x 4 acciones |
| Clasificaciones de servicio | 3 | infrastructure, applications, hardware |
| Servicios | 8 | Red, correo, servidor, apps, impresoras, hardware |
| Usuarios admin | 4 | admin1, admin2, admin, admin4 |
| Usuarios soporte | 6 | soporte1 a soporte6 |
| Usuarios finales | 10 | usuario1 a usuario10 |
| Tickets | 10 | Diversos tipos, estados y prioridades |
| Actividades | ~20 | Notas de ejemplo para los tickets |
