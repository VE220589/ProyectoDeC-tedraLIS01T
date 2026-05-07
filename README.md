# ProyectoCatedraNIT104

Aplicacion web en CodeIgniter 4 para gestion de usuarios, roles, servicios, tickets y notas de soporte.

## Requisitos cubiertos de la fase II

- Backend y frontend en PHP con CodeIgniter 4.
- Login con usuario/contrasena y soporte para Google Identity Services mediante `GOOGLE_CLIENT_ID`.
- CRUD JSON para usuarios, servicios, tickets, notas, roles y permisos.
- Rutas API protegidas por sesion y permisos (`users.*`, `services.*`, `tickets.*`, `roles.*`).
- Sesiones con expiracion por inactividad configurable con `app.sessionInactivityTimeout`.
- Mensajes JSON amigables para errores de autenticacion, permisos y validacion.
- Base de datos PostgreSQL con script inicial `scriptdelabase.sql`.
- Docker, Docker Compose y manifiestos Kubernetes en `k8s/`.

## Ejecucion local con Docker

```bash
docker compose up --build
```

La aplicacion queda disponible en `http://localhost:8080`.

## Variables importantes

Copie `.env.example` a `.env` para ejecucion local sin contenedores y ajuste:

- `app.baseURL`
- `database.default.*`
- `GOOGLE_CLIENT_ID`

## API

La documentacion de endpoints, permisos y despliegue esta en `docs/API.md`.
