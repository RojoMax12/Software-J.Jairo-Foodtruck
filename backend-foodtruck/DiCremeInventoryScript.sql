-- 1. Tablas Maestras (No dependen de nadie)
CREATE TABLE rol (
    ID SERIAL PRIMARY KEY,
    nombre_rol VARCHAR(40)
);

CREATE TABLE categoria (
    ID SERIAL PRIMARY KEY,
    nombre_categoria VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255)
);

CREATE TABLE formato (
    ID SERIAL PRIMARY KEY,
    nombre_formato VARCHAR(50) NOT NULL
);

CREATE TABLE estado_pedido (
    ID SERIAL PRIMARY KEY,
    nombre_estado_pedido VARCHAR(40)
);

-- 2. Tablas de Usuarios (Dependen de Rol)
CREATE TABLE usuario_dicreme (
    ID SERIAL PRIMARY KEY,
    ID_rol INT NOT NULL REFERENCES rol(ID),
    nombre_usuario VARCHAR(30) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    correo VARCHAR(50) NOT NULL
);

CREATE TABLE usuario_distribuidores (
    ID SERIAL PRIMARY KEY,
    ID_rol INT NOT NULL REFERENCES rol(ID),
    rut_empresa VARCHAR(30) NOT NULL,
    nombre_usuario VARCHAR(30) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    direccion VARCHAR(100) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    correo VARCHAR(50) NOT NULL,
    comuna VARCHAR(30) NOT NULL
);

-- 3. Infraestructura y Productos
CREATE TABLE bodega(
    ID SERIAL PRIMARY KEY,
    nombre_bodega VARCHAR(50), -- Se añade nombre para identificarla
    descripcion VARCHAR(255),
    cantidad_de_elemtos INT NOT NULL DEFAULT 0
);

CREATE TABLE stock (
    ID SERIAL PRIMARY KEY,
    cantidad INT NOT NULL DEFAULT 0
);


CREATE TABLE lote (
    ID SERIAL PRIMARY KEY,
    ID_producto INT REFERENCES producto(ID),
    ID_stock INT REFERENCES stock(ID),
    ID_bodega INT REFERENCES bodega(ID),
    cantidad_producto INT NOT NULL,
    fecha_emision DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,    
);

CREATE TABLE producto (
    ID SERIAL PRIMARY KEY,
    ID_categoria INT NOT NULL REFERENCES categoria(ID),
    ID_formato INT NOT NULL REFERENCES formato(ID),
    nombre_producto VARCHAR(100) NOT NULL,
    precio INT NOT NULL
);



-- 4. Pedidos y Logística
CREATE TABLE pedido (
    ID SERIAL PRIMARY KEY,
    ID_usuario_distribuidores INT NOT NULL REFERENCES usuario_distribuidores(ID),
    ID_usuario_dicreme INT REFERENCES usuario_dicreme(ID),
    ID_estado INT REFERENCES estado_pedido(ID),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE despacho (
    ID SERIAL PRIMARY KEY,
    ID_pedido INT REFERENCES pedido(ID),
    direccion_entrega VARCHAR(255),
    persona_receptora VARCHAR(40),
    fecha_de_entrega DATE,
    comuna VARCHAR(255),
    estado_despacho VARCHAR(40)
);

CREATE TABLE pedido_producto ( 
    ID_producto INT REFERENCES producto(ID),
    ID_pedido INT REFERENCES pedido(ID),
    precio_unitario INT NOT NULL,
    cantidad INT NOT NULL,
    PRIMARY KEY (ID_producto, ID_pedido)
);

CREATE TABLE venta (
    ID SERIAL PRIMARY KEY,
    ID_pedido INT REFERENCES pedido(ID),
    fecha DATE DEFAULT CURRENT_DATE,
    numero_factura INT,
    glosa VARCHAR(255),
    estado_pago VARCHAR(40),
    monto_total INT
);