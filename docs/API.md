# API REST

Todas las rutas protegidas usan la sesion creada por `/api/auth/login` o `/api/auth/google`.
Las respuestas se entregan en JSON con `status`, `message` y, cuando aplica, `dataset`.

## Autenticacion

- `POST /api/auth/login`: recibe `alias_usuario` y `clave_usuario`.
- `POST /api/auth/google`: recibe `credential` o `id_token` de Google Identity Services. Requiere `GOOGLE_CLIENT_ID`.
- `GET /api/auth/logOut`: cierra la sesion.

## Usuarios

- `GET /api/usuarios/index`: lista usuarios activos. Permiso: `users.view`.
- `POST /api/usuarios/readOne`: lee un usuario por `id_usuario`. Permiso: `users.view`.
- `POST /api/usuarios/create`: crea usuarios. Permiso: `users.create`.
- `POST /api/usuarios/update`: actualiza usuarios. Permiso: `users.update`.
- `POST /api/usuarios/deletelogic`: baja logica. Permiso: `users.delete`.
- `POST /api/usuarios/delete`: elimina fisicamente. Permiso: `users.delete`.

## Servicios

- `GET /api/services/index`: lista servicios activos. Permiso: `services.view`.
- `POST /api/services/readOne`: lee un servicio por `id_servicio`. Permiso: `services.view`.
- `POST /api/services/create`: crea servicio. Permiso: `services.create`.
- `POST /api/services/update`: actualiza servicio. Permiso: `services.update`.
- `POST /api/services/deletelogic`: baja logica. Permiso: `services.delete`.

## Tickets y notas

- `GET /api/tickets/index`: lista todos los tickets. Permiso: `tickets.view`.
- `GET /api/tickets/supporTickets`: lista tickets asignados al usuario. Permiso: `tickets.view`.
- `GET /api/tickets/userTickets`: lista tickets creados por el usuario. Permiso: `tickets.view`.
- `POST /api/tickets/create`: crea ticket. Permiso: `tickets.create`.
- `POST /api/tickets/update`: actualiza ticket. Permiso: `tickets.update`.
- `POST /api/tickets/deletelogic`: cierra ticket y registra actividad. Permiso: `tickets.delete`.
- `POST /api/notas/index`: lista notas de un ticket. Permiso: `tickets.view`.
- `POST /api/notas/create`: agrega nota y mueve tickets abiertos a `in_progress`. Permiso: `tickets.update`.
- `POST /api/notas/createRequest`: solicita cierre y mueve ticket a `mitigated`. Permiso: `tickets.update`.

## Despliegue

Local con contenedores:

```bash
docker compose up --build
```

Aplicacion: `http://localhost:8080`

Kubernetes:

```bash
kubectl apply -f k8s/namespace.yaml
kubectl create configmap postgres-init --from-file=01-schema.sql=scriptdelabase.sql -n tmnotes --dry-run=client -o yaml | kubectl apply -f -
kubectl apply -f k8s/postgres.yaml
kubectl apply -f k8s/app.yaml
```
