# Guia de despliegue

## Requisitos

- **PHP** >= 8.1 (con extensiones `intl`, `pdo_pgsql`, `pgsql`)
- **Composer** 2.x
- **PostgreSQL** 16+
- **Docker** y **Docker Compose** (para despliegue con contenedores)
- **kubectl** (para despliegue en Kubernetes)

---

## 1. Ejecucion local (sin Docker)

### 1.1 Clonar el repositorio

```bash
git clone https://github.com/VE220589/ProyectoDeC-tedraLIS01T.git
cd ProyectoDeC-tedraLIS01T
```

### 1.2 Instalar dependencias

```bash
composer install
```

### 1.3 Configurar variables de entorno

```bash
cp .env.example .env
```

Editar `.env` con los datos de conexion:

```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'
app.sessionInactivityTimeout = 1800

database.default.hostname = localhost
database.default.database = tmnotes
database.default.username = postgres
database.default.password = postgres
database.default.port = 5432

GOOGLE_CLIENT_ID =
```

### 1.4 Crear la base de datos

```bash
createdb -U postgres tmnotes
psql -U postgres -d tmnotes -f scriptdelabase.sql
```

### 1.5 Iniciar el servidor de desarrollo

```bash
php spark serve --port 8080
```

La aplicacion estara disponible en `http://localhost:8080`.

---

## 2. Docker Compose

### 2.1 Construir y levantar

```bash
docker compose up --build
```

Esto levanta dos servicios:

| Servicio | Contenedor | Puerto |
|---|---|---|
| `app` | `tmnotes_app` | `8080 → 80` |
| `db` | `tmnotes_db` | `5432 → 5432` |

La BD se inicializa automaticamente con `scriptdelabase.sql` montado en `/docker-entrypoint-initdb.d/`.

### 2.2 Detener

```bash
docker compose down
```

Para eliminar tambien los volumenes de datos:

```bash
docker compose down -v
```

### 2.3 Variables de entorno

Las variables se definen en `docker-compose.yml`. Para Google Login, pasar la variable al levantar:

```bash
GOOGLE_CLIENT_ID=tu-client-id docker compose up --build
```

### 2.4 Estructura Docker

- **Dockerfile**: imagen basada en `php:8.3-apache` con extensiones `intl`, `pdo_pgsql`, `pgsql` y modulos Apache `rewrite`, `headers`.
- **docker/apache.conf**: configuracion de VirtualHost de Apache.
- **Volumenes**:
  - `app_writable` — directorio `writable/` de CI4 (logs, cache).
  - `postgres_data` — datos persistentes de PostgreSQL.

---

## 3. Kubernetes

### 3.1 Prerequisitos

- Cluster Kubernetes funcional.
- `kubectl` configurado.
- Imagen Docker construida y accesible (registro o carga local).

### 3.2 Desplegar

```bash
# 1. Crear namespace
kubectl apply -f k8s/namespace.yaml

# 2. Crear ConfigMap con el script de inicializacion de la BD
kubectl create configmap postgres-init \
  --from-file=01-schema.sql=scriptdelabase.sql \
  -n tmnotes --dry-run=client -o yaml | kubectl apply -f -

# 3. Desplegar PostgreSQL
kubectl apply -f k8s/postgres.yaml

# 4. Desplegar la aplicacion
kubectl apply -f k8s/app.yaml
```

### 3.3 Componentes Kubernetes

| Recurso | Archivo | Descripcion |
|---|---|---|
| Namespace `tmnotes` | `k8s/namespace.yaml` | Aislamiento del proyecto |
| Secret `postgres-secret` | `k8s/postgres.yaml` | Contrasena de PostgreSQL |
| PVC `postgres-data` | `k8s/postgres.yaml` | 1 Gi para datos de BD |
| Deployment `postgres` | `k8s/postgres.yaml` | 1 replica PostgreSQL 16 Alpine |
| Service `postgres` | `k8s/postgres.yaml` | ClusterIP en puerto 5432 |
| ConfigMap `tmnotes-config` | `k8s/app.yaml` | Variables de entorno de la app |
| Deployment `tmnotes-app` | `k8s/app.yaml` | 2 replicas de la app |
| Service `tmnotes-app` | `k8s/app.yaml` | NodePort 30080 |

### 3.4 Probes

La aplicacion define:

- **readinessProbe**: `GET /` cada 10s (inicio a los 10s).
- **livenessProbe**: `GET /` cada 20s (inicio a los 30s).

### 3.5 Acceso

La aplicacion queda expuesta en `http://<NODE_IP>:30080`.

---

## 4. Variables de entorno

| Variable | Descripcion | Valor por defecto |
|---|---|---|
| `CI_ENVIRONMENT` | Entorno CI4 (`development` / `production`) | `development` |
| `app.baseURL` | URL base de la aplicacion | `http://localhost:8080/` |
| `app.sessionInactivityTimeout` | Timeout de sesion por inactividad (segundos) | `1800` (30 min) |
| `database.default.hostname` | Host de PostgreSQL | `localhost` |
| `database.default.database` | Nombre de la BD | `tmnotes` |
| `database.default.username` | Usuario de BD | `postgres` |
| `database.default.password` | Contrasena de BD | `postgres` |
| `database.default.port` | Puerto de BD | `5432` |
| `GOOGLE_CLIENT_ID` | Client ID para Google Identity Services | (vacio) |

Las variables `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_PORT` y `APP_BASE_URL` son alias usados en contextos de Docker/K8s.
