create database TenTen;

use TenTen;

CREATE TABLE usuarios (
id_usuarios INT AUTO_INCREMENT,
username VARCHAR(30) NOT NULL,
pass_user VARCHAR(255) NOT NULL,
perfil VARCHAR(50) NOT NULL DEFAULT 'usuario',
foto_perfil VARCHAR(255) NULL,
PRIMARY KEY(id_usuarios)
);

   
create table clientes(
   id_cliente int auto_increment,
   nombre_cliente varchar(30),
   apellido_cliente varchar(30),
   dni_cliente int(30),
   anio_cliente YEAR(5),
   PRIMARY KEY (id_cliente));

   -- Tabla para los items que el administrador puede cargar
CREATE TABLE items (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    nombre_item VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla para los envíos de los usuarios
CREATE TABLE envios (
    id_envio INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    destino VARCHAR(255) NOT NULL,
    estado VARCHAR(50) NOT NULL DEFAULT 'Pendiente',
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuarios)
);