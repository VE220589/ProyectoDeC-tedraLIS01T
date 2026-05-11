# ProyectoCatedraNIT104 — TMNotes

Aplicacion web de gestion de soporte tecnico construida con **CodeIgniter 4** (PHP 8.1+) y **PostgreSQL 16**.
Permite administrar usuarios, roles, permisos, servicios, tickets de soporte y notas de actividad.

## Funcionalidades principales

- Backend y frontend en PHP con CodeIgniter 4.
- Login con usuario/contrasena y soporte para Google Identity Services mediante `GOOGLE_CLIENT_ID`.
- CRUD JSON para usuarios, servicios, tickets, notas, roles y permisos.
- Rutas API protegidas por sesion y permisos (`users.*`, `services.*`, `tickets.*`, `roles.*`).
- Sesiones con expiracion por inactividad configurable con `app.sessionInactivityTimeout`.
- Mensajes JSON amigables para errores de autenticacion, permisos y validacion.
- Base de datos PostgreSQL con script inicial `scriptdelabase.sql`.
- Docker, Docker Compose y manifiestos Kubernetes en `k8s/`.

## Inicio rapido

### Docker Compose

```bash
docker compose up --build
```

La aplicacion queda disponible en `http://localhost:8080`.

### Sin Docker

```bash
composer install
cp .env.example .env          # editar con datos de conexion a PostgreSQL
psql -U postgres -d tmnotes -f scriptdelabase.sql
php spark serve --port 8080
```

## Variables importantes

Copie `.env.example` a `.env` para ejecucion local sin contenedores y ajuste:

- `app.baseURL` — URL base de la aplicacion.
- `database.default.*` — Conexion a PostgreSQL.
- `GOOGLE_CLIENT_ID` — Client ID de Google para login social.
- `app.sessionInactivityTimeout` — Timeout de sesion en segundos (default: 1800).

## Documentacion

| Documento | Descripcion |
|---|---|
| [`docs/API.md`](docs/API.md) | Referencia completa de la API REST (endpoints, parametros, respuestas) |
| [`docs/ARQUITECTURA.md`](docs/ARQUITECTURA.md) | Arquitectura general, flujos de autenticacion, ciclo de vida de tickets y sistema de permisos |
| [`docs/BASE_DE_DATOS.md`](docs/BASE_DE_DATOS.md) | Diagrama ER, tablas, tipos enumerados y datos de seed |
| [`docs/CONTROLADORES.md`](docs/CONTROLADORES.md) | Referencia detallada de cada controlador, metodos y validaciones |
| [`docs/MODELOS.md`](docs/MODELOS.md) | Modelos de datos, campos permitidos, metodos con JOINs y queries |
| [`docs/FILTROS.md`](docs/FILTROS.md) | Middleware de autenticacion (AuthFilter) y redireccion (GuestFilter) |
| [`docs/DESPLIEGUE.md`](docs/DESPLIEGUE.md) | Guia de despliegue local, Docker Compose y Kubernetes |

## Tests

```bash
composer test
```

## Licencia

MIT — ver [LICENSE](LICENSE).
