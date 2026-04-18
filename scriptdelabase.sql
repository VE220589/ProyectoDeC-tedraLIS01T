-- Enums
CREATE TYPE ticket_type AS ENUM ('incident','problem','change');
CREATE TYPE ticket_status AS ENUM ('open','in_progress','mitigated','closed');
CREATE TYPE ticket_priority AS ENUM ('C','B','A','S');
CREATE TYPE note_type AS ENUM ('comment','request', 'approved');

-- Roles
CREATE TABLE roles (
  id SERIAL PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE, -- admin, support, end_user
  description TEXT,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Users
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  username VARCHAR(25) NOT NULL UNIQUE,
  email VARCHAR(100) UNIQUE,
  password_hash TEXT NOT NULL,
  name VARCHAR(30) NOT NULL,
  last_name VARCHAR(30) NOT NULL,
  role_id INT NOT NULL REFERENCES roles(id),
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

CREATE TABLE services_classification (
  id SERIAL PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Services
CREATE TABLE services (
  id SERIAL PRIMARY KEY,
  description VARCHAR(50),
  idservice_classification INT NOT NULL REFERENCES services_classification (id),
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);


---Gestión de permisos--

CREATE TABLE permissions (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT
);

CREATE TABLE role_permissions (
  role_id INT REFERENCES roles(id) ON DELETE CASCADE,
  permission_id INT REFERENCES permissions(id) ON DELETE CASCADE,
  status BOOLEAN DEFAULT FALSE,   -- TRUE = activo, FALSE = inactivo
  PRIMARY KEY (role_id, permission_id)
);

-- Tickets
CREATE TABLE tickets (
  id SERIAL PRIMARY KEY,
  ticket_number VARCHAR(30) UNIQUE DEFAULT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  ticket_type ticket_type NOT NULL DEFAULT 'incident',
  status ticket_status NOT NULL DEFAULT 'open',
  priority ticket_priority NOT NULL DEFAULT 'C',
  service_id INT REFERENCES services(id),

  -- Campos de usuario
  created_by INT NOT NULL REFERENCES users(id),
  assigned_to INT REFERENCES users(id),   -- ahora permite NULL correctamente
  closed_by INT REFERENCES users(id),     -- ahora permite NULL correctamente

  created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  closed_at TIMESTAMP WITH TIME ZONE
);

-- Activities
CREATE TABLE ticket_activities (
  id SERIAL PRIMARY KEY,
  ticket_id SERIAL NOT NULL REFERENCES tickets(id) ON DELETE CASCADE,
  actor_id SERIAL REFERENCES users(id),
  action VARCHAR(100) NOT NULL,
  note_type note_type NOT NUll default 'comment',
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Índices
CREATE INDEX idx_tickets_created_by ON tickets(created_by);
CREATE INDEX idx_tickets_assigned_to ON tickets(assigned_to);
CREATE INDEX idx_tickets_status ON tickets(status);
CREATE INDEX idx_tickets_priority ON tickets(priority);
CREATE INDEX idx_tickets_service ON tickets(service_id);
CREATE INDEX idx_tickets_ticket_number ON tickets(ticket_number);

-- --------------------------------------------------------------------------------

-- Seed

-- --------------------------------------------------------------------------------

INSERT INTO roles (name, description) VALUES
  ('admin', 'Administrador del sistema con todas las funciones'),
  ('support', 'Técnico de soporte encargado de resolver tickets'),
  ('end_user', 'Usuario final que puede reportar incidentes');

WITH modules AS (
  SELECT 'users' AS module UNION
  SELECT 'roles' UNION
  SELECT 'services' UNION
  SELECT 'tickets' 
)
INSERT INTO permissions (name, description)
SELECT 
  module || '.' || action AS name,
  'Permiso para ' || action || ' en módulo ' || module AS description
FROM modules,
LATERAL (VALUES ('create'), ('view'), ('update'), ('delete')) AS a(action);

INSERT INTO role_permissions (role_id, permission_id, status)
SELECT r.id, p.id, TRUE
FROM roles r
CROSS JOIN permissions p
WHERE r.name = 'admin';

INSERT INTO role_permissions (role_id, permission_id, status)
SELECT r.id, p.id, FALSE
FROM roles r
CROSS JOIN permissions p
WHERE r.name <> 'admin';

INSERT INTO services_classification (name, description) VALUES
  ('infrastructure', 'Servicios ajustados a infraestructura'),
  ('applications', 'Servicios ajustados para aplicaciones'),
  ('hardware', 'Servicios ajustados a hardware');

INSERT INTO services (description, idservice_classification)
VALUES
  ('Red corporativa', 1),
  ('Correo institucional', 2),
  ('Servidor central', 1),
  ('Aplicación de RRHH', 2),
  ('Aplicación de ventas', 2),
  ('Impresoras de oficina', 3),
  ('Hardware de oficina', 3),
  ('Aplicación contable', 2);

-- ===========================
--        ADMINS (4)
-- ===========================

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('admin1','admin1@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Oscar','Villalobos', (SELECT id FROM roles WHERE name = 'admin'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('admin2','admin2@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Erick','Trujillo', (SELECT id FROM roles WHERE name = 'admin'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('admin','admin@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Walter','Sanchez', (SELECT id FROM roles WHERE name = 'admin'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('admin4','admin4@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Karl','Marx', (SELECT id FROM roles WHERE name = 'admin'));


-- ===========================
--        SOPORTE (6)
-- ===========================

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('soporte1','soporte1@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Juan','Pérez', (SELECT id FROM roles WHERE name = 'support'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('soporte2','soporte2@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'María','López', (SELECT id FROM roles WHERE name = 'support'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('soporte3','soporte3@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Carlos','García', (SELECT id FROM roles WHERE name = 'support'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('soporte4','soporte4@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Lucía','Martínez', (SELECT id FROM roles WHERE name = 'support'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('soporte5','soporte5@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'David','Ramírez', (SELECT id FROM roles WHERE name = 'support'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('soporte6','soporte6@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Sofía','Morales', (SELECT id FROM roles WHERE name = 'support'));


-- ===========================
--      END USERS (10)
-- ===========================

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('usuario1','usuario1@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Carlos','López', (SELECT id FROM roles WHERE name = 'end_user'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('usuario2','usuario2@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Daniela','Rivas', (SELECT id FROM roles WHERE name = 'end_user'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('usuario3','usuario3@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Fernando','Cruz', (SELECT id FROM roles WHERE name = 'end_user'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('usuario4','usuario4@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Rebeca','Torres', (SELECT id FROM roles WHERE name = 'end_user'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('usuario5','usuario5@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Andrés','Molina', (SELECT id FROM roles WHERE name = 'end_user'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('usuario6','usuario6@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Patricia','Herrera', (SELECT id FROM roles WHERE name = 'end_user'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('usuario7','usuario7@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Diego','Navarro', (SELECT id FROM roles WHERE name = 'end_user'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('usuario8','usuario8@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Valeria','Castro', (SELECT id FROM roles WHERE name = 'end_user'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('usuario9','usuario9@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Javier','Méndez', (SELECT id FROM roles WHERE name = 'end_user'));

INSERT INTO users (username, email, password_hash, name, last_name, role_id) VALUES
('usuario10','usuario10@sistema.com',
 '$2y$10$WmwI47ZxP19CR7axFvV1uucqggQjWf0eDztgp.Hkrl0p42cSjtzD6',
 'Alejandra','Santos', (SELECT id FROM roles WHERE name = 'end_user'));


-- =======================
-- Tickets
-- =======================
select * from tickets;

INSERT INTO tickets (
  ticket_number, title, description, ticket_type, status, priority,
  service_id, created_by, assigned_to, created_at, updated_at
) VALUES (
  'TCK-000001',
  'Falla al conectarse a la VPN',
  'El usuario reporta que la VPN rechaza sus credenciales.',
  'incident',
  'open',
  'A',
  1,
  12,
  5,
  now() - interval '28 days',
  now() - interval '28 days'
);

INSERT INTO ticket_activities (ticket_id, actor_id, action, note_type) VALUES
(1, 5, 'El ticket fue asignado al técnico Juan Pérez.', 'comment'),
(1, 5, 'El ticket fue cambiado al estado "En progreso".', 'comment');

-- =====

INSERT INTO tickets (
    ticket_number, title, description, ticket_type, status, priority,
    service_id, created_by, created_at, updated_at
) VALUES (
  'TCK-000002',
  'Error no carga',
  'Error al cargar la interfaz principal.',
  'incident',
  'open',
  'B',
  4,
  18,
  now() - interval '25 days',
  now() - interval '25 days'
);

select * from ticket_activities;
INSERT INTO ticket_activities (ticket_id, actor_id, action, note_type) VALUES
(2, 6, 'El ticket fue asignado al técnico María López.', 'comment');

-- ===

INSERT INTO tickets (
    ticket_number, title, description, ticket_type, status, priority,
    service_id, created_by, assigned_to, created_at, updated_at
)  VALUES (
  'TCK-000003',
  'Intermitencia en conexión de red',
  'Pérdida de conexión cada 5 minutos.',
  'problem',
  'in_progress',
  'S',
  1,
  15,
  7,
  now() - interval '23 days',
  now() - interval '23 days'
);

INSERT INTO ticket_activities (ticket_id, actor_id, action, note_type) VALUES
(3, 7, 'El ticket fue asignado al técnico Carlos García.', 'comment'),
(3, 7, 'Diagnóstico inicial realizado.', 'comment');

-- ===========================

INSERT INTO tickets(
    ticket_number, title, description, ticket_type, status, priority,
    service_id, created_by, assigned_to, created_at, updated_at
) VALUES (
  'TCK-000004',
  'Problema Impresora',
  'Impresora HP tiene atasco de papel constantemente.',
  'incident',
  'open',
  'C',
  6,
  11,
  NULL,
  now() - interval '21 days',
  now() - interval '21 days'
);

INSERT INTO ticket_activities (ticket_id, actor_id, action, note_type) VALUES
(4, 8, 'El ticket fue asignado al técnico Lucía Martínez.', 'comment');

-- ===========================

/* ============================================================*/
INSERT INTO tickets(
  ticket_number, title, description, ticket_type, status, priority,
  service_id, created_by, assigned_to, created_at, updated_at
) VALUES (
  'TCK-000005',
  'Solicitud de cambio de equipo',
  'Equipo presenta daños físicos y requiere reemplazo.',
  'change',
  'open',
  'B',
  7,
  14,
  NULL,
  now() - interval '20 days',
  now() - interval '20 days'
);

INSERT INTO ticket_activities (ticket_id, actor_id, action, note_type)
VALUES (5, 1, 'Solicitud recibida por administración.', 'comment');


/* ============================================================*/
INSERT INTO tickets(
  ticket_number, title, description, ticket_type, status, priority,
  service_id, created_by, assigned_to, created_at, updated_at
) VALUES (
  'TCK-000006',
  'Lentitud en servidor central',
  'Servidor responde lentamente en horas pico.',
  'problem',
  'in_progress',
  'A',
  3,
  19,
  9,
  now() - interval '18 days',
  now() - interval '18 days'
);

INSERT INTO ticket_activities (ticket_id, actor_id, action, note_type)
VALUES (6, 9, 'El ticket fue asignado al técnico David Ramírez.', 'comment');


/* ============================================================ */
INSERT INTO tickets(
  ticket_number, title, description, ticket_type, status, priority,
  service_id, created_by, assigned_to, created_at, updated_at
) VALUES (
  'TCK-000007',
  'Error 503 en aplicación de ventas',
  'La aplicación muestra error 503 al iniciar sesión.',
  'incident',
  'open',
  'S',
  5,
  13,
  10,
  now() - interval '17 days',
  now() - interval '17 days'
);

INSERT INTO ticket_activities (ticket_id, actor_id, action, note_type)
VALUES (7, 10, 'El ticket fue asignado a la técnica Sofía Morales.', 'comment');


/* ============================================================ */
INSERT INTO tickets(
  ticket_number, title, description, ticket_type, status, priority,
  service_id, created_by, assigned_to, created_at, updated_at
) VALUES (
  'TCK-000008',
  'Correo institucional no envía',
  'Outlook rechaza el envío de correos.',
  'incident',
  'open',
  'B',
  2,
  16,
  NULL,
  now() - interval '16 days',
  now() - interval '16 days'
);

INSERT INTO ticket_activities (ticket_id, actor_id, action, note_type)
VALUES (8, 6, 'El ticket fue asignado a María López.', 'comment');


/* ============================================================*/
INSERT INTO tickets(
  ticket_number, title, description, ticket_type, status, priority,
  service_id, created_by, assigned_to, created_at, updated_at
) VALUES (
  'TCK-000009',
  'Aplicación contable no carga',
  'Pantalla en blanco al abrir la aplicación contable.',
  'incident',
  'open',
  'A',
  8,
  17,
  7,
  now() - interval '13 days',
  now() - interval '13 days'
);

INSERT INTO ticket_activities (ticket_id, actor_id, action, note_type)
VALUES (9, 7, 'El ticket fue asignado al técnico Carlos García.', 'comment');


/* ============================================================*/
INSERT INTO tickets(
  ticket_number, title, description, ticket_type, status, priority,
  service_id, created_by, assigned_to, created_at, updated_at
) VALUES (
  'TCK-000010',
  'Solicitud de permisos temporales',
  'Usuario requiere acceso temporal al servidor principal.',
  'change',
  'open',
  'B',
  3,
  12,
  NULL,
  now() - interval '11 days',
  now() - interval '11 days'
);

INSERT INTO ticket_activities (ticket_id, actor_id, action, note_type)
VALUES (10, 1, 'Solicitud recibida por administración.', 'comment');

/* ============================================================*/
INSERT INTO tickets(
  ticket_number, title, description, ticket_type, status, priority,
  service_id, created_by, assigned_to, created_at, updated_at
) VALUES (
  'TCK-000011',
  'Conexión WiFi lenta',
  'La red inalámbrica presenta baja velocidad.',
  'incident',
  'open',
  'C',
  1,
  18,
  8,
  now() - interval '10 days',
  now() - interval '10 days'
);

INSERT INTO ticket_activities (ticket_id, actor_id, action, note_type)
VALUES (11, 8, 'El ticket fue asignado a Lucía Martínez.', 'comment');


/* ===========================================================*/
INSERT INTO tickets(
  ticket_number, title, description, ticket_type, status, priority,
  service_id, created_by, assigned_to, created_at, updated_at
) VALUES (
  'TCK-000012',
  'Falla en hardware del CPU',
  'El CPU presenta ruido fuerte y reinicios inesperados.',
  'incident',
  'open',
  'B',
  7,
  20,
  9,
  now() - interval '5 days',
  now() - interval '5 days'
);

INSERT INTO ticket_activities (ticket_id, actor_id, action, note_type)
VALUES (12, 9, 'El ticket fue asignado a David Ramírez.', 'comment');


/* ============================================================*/
INSERT INTO tickets(
  ticket_number, title, description, ticket_type, status, priority,
  service_id, created_by, assigned_to, created_at, updated_at
) VALUES (
  'TCK-000013',
  'Configuración de impresora',
  'Nuevo empleado requiere configuración de impresión.',
  'change',
  'open',
  'C',
  6,
  11,
  5,
  now() - interval '4 days',
  now() - interval '4 days'
);

INSERT INTO ticket_activities (ticket_id, actor_id, action, note_type)
VALUES (13, 5, 'Asignado a Juan Pérez.', 'comment');


/* ============================================================*/
INSERT INTO tickets(
  ticket_number, title, description, ticket_type, status, priority,
  service_id, created_by, assigned_to, created_at, updated_at
) VALUES (
  'TCK-000014',
  'Desconexiones aleatorias',
  'Usuarios pierden conexión cada cierto tiempo.',
  'problem',
  'in_progress',
  'A',
  1,
  16,
  10,
  now() - interval '3 days',
  now() - interval '3 days'
);

INSERT INTO ticket_activities (ticket_id, actor_id, action, note_type)
VALUES (14, 10, 'El ticket fue asignado a Sofía Morales.', 'comment');


/* ============================================================*/
INSERT INTO tickets(
  ticket_number, title, description, ticket_type, status, priority,
  service_id, created_by, assigned_to, created_at, updated_at
) VALUES (
  'TCK-000015',
  'Error de credenciales en RRHH',
  'El sistema rechaza credenciales válidas.',
  'incident',
  'open',
  'B',
  4,
  14,
  6,
  now() - interval '2 days',
  now() - interval '2 days'
);

INSERT INTO ticket_activities (ticket_id, actor_id, action, note_type)
VALUES (15, 6, 'El ticket fue asignado a María López.', 'comment');

-- =====================

-- Check recent tickets
SELECT title as Titulo, priority as Severidad, (created_at AT TIME ZONE 'UTC-6') as Fecha FROM tickets ORDER BY created_at DESC LIMIT 5;

-- Ticket not assigned yet
SELECT * FROM tickets WHERE assigned_to IS NULL;

-- 

select * from tickets;