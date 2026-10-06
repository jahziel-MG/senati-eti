CREATE DATABASE IF NOT EXISTS senati_eti
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE senati_eti;

-- ==========================================
-- TABLA: usuarios
-- ==========================================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    correo VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('ADMIN', 'EMPLEADO') NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- TABLA: empleados
-- ==========================================
CREATE TABLE empleados (
    id_empleado INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    codigo_empleado VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(80) NOT NULL,
    apellidos VARCHAR(80) NOT NULL,
    cargo VARCHAR(100),
    estado TINYINT(1) NOT NULL DEFAULT 1,

    CONSTRAINT fk_empleado_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
);

-- ==========================================
-- TABLA: visitantes
-- ==========================================
CREATE TABLE visitantes (
    id_visitante INT AUTO_INCREMENT PRIMARY KEY,
    documento VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(80) NOT NULL,
    apellidos VARCHAR(80) NOT NULL,
    telefono VARCHAR(20),
    correo VARCHAR(100),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- TABLA: asuntos
-- ==========================================
CREATE TABLE asuntos (
    id_asunto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    estado TINYINT(1) NOT NULL DEFAULT 1
);

-- ==========================================
-- TABLA: visitas
-- ==========================================
CREATE TABLE visitas (
    id_visita INT AUTO_INCREMENT PRIMARY KEY,
    id_visitante INT NOT NULL,
    id_empleado INT NULL,
    id_asunto INT NOT NULL,

    prioridad ENUM('BAJA', 'MEDIA', 'ALTA', 'URGENTE')
        NOT NULL DEFAULT 'MEDIA',

    estado ENUM(
        'REGISTRADA',
        'ESPERANDO',
        'EN_ATENCION',
        'RESUELTA',
        'CANCELADA'
    ) NOT NULL DEFAULT 'REGISTRADA',

    consulta TEXT NOT NULL,
    respuesta TEXT,

    registrado_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tomado_at DATETIME NULL,
    inicio_at DATETIME NULL,
    resuelto_at DATETIME NULL,
    finalizado_at DATETIME NULL,

    CONSTRAINT fk_visita_visitante
        FOREIGN KEY (id_visitante)
        REFERENCES visitantes(id_visitante),

    CONSTRAINT fk_visita_empleado
        FOREIGN KEY (id_empleado)
        REFERENCES empleados(id_empleado),

    CONSTRAINT fk_visita_asunto
        FOREIGN KEY (id_asunto)
        REFERENCES asuntos(id_asunto)
);

-- ==========================================
-- TABLA: auditoria
-- ==========================================
CREATE TABLE auditoria (
    id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    accion VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(100),
    id_registro INT,
    descripcion TEXT,
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_auditoria_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
);