# 📦 Módulo de Pedidos — UVA3 Logística

Sistema web de gestión de pedidos desarrollado en **PHP puro** con arquitectura MVC, base de datos **MySQL** (PDO), integración con una API externa de transportadora y un endpoint JSON propio.

---

## 🗂️ Tabla de contenidos

1. [Descripción general](#descripción-general)
2. [Características](#características)
3. [Tecnologías](#tecnologías)
4. [Arquitectura del proyecto](#arquitectura-del-proyecto)
5. [Estructura de carpetas](#estructura-de-carpetas)
6. [Base de datos](#base-de-datos)
7. [Configuración e instalación](#configuración-e-instalación)
8. [Uso de la aplicación](#uso-de-la-aplicación)
9. [API REST](#api-rest)
10. [Servicio de Transportadora](#servicio-de-transportadora)
11. [Reglas de negocio](#reglas-de-negocio)
12. [Variables de entorno](#variables-de-entorno)

---

## Descripción general

**Módulo de Pedidos** es una aplicación web interna para la empresa **UVA3 Logística** que permite:

- Registrar y consultar pedidos asociados a clientes.
- Gestionar el ciclo de vida de un pedido: **pendiente → despachado → entregado**.
- Calcular automáticamente el **costo de envío** al despachar, consultando una API de tarifas externa (con fallback simulado si la API no está disponible).
- Generar un **reporte mensual** de ventas por cliente.
- Exponer los pedidos a sistemas externos vía un **endpoint JSON** (REST).

---

## Características

| Funcionalidad | Detalle |
|---|---|
| ✅ Listado de pedidos | Filtrable por estado (pendiente, despachado, entregado) |
| ✅ Crear pedido | Formulario con validación servidor |
| ✅ Cambiar estado | Con reglas de negocio (entregados bloqueados) |
| ✅ Costo de envío | Calculado automáticamente al despachar |
| ✅ Reporte mensual | Total vendido y cantidad de pedidos por cliente en el mes |
| ✅ API REST JSON | `GET /api/pedidos?estado=` |
| ✅ Fallback de tarifa | Si la API de transportadora no responde, usa tarifa calculada localmente |

---

## Tecnologías

- **PHP 8.1+** — Backend, lógica de negocio y renderizado de vistas
- **MySQL 8+** — Base de datos relacional (vía PDO con prepared statements)
- **Apache + mod_rewrite** — Servidor web con rutas amigables (`.htaccess`)
- **cURL** — Consumo de la API externa de transportadora
- **XAMPP** — Entorno de desarrollo recomendado (Apache + MySQL + PHP)
- **HTML / CSS** — Interfaz de usuario (sin frameworks frontend externos)

---

## Arquitectura del proyecto

El proyecto sigue el patrón **MVC (Model–View–Controller)** sin framework, implementado de forma artesanal:

```
Petición HTTP
     │
     ▼
 .htaccess (reescritura de URLs)
     │
     ▼
 index.php (Router principal)
     │
     ├──► /api/pedidos → api/pedidos.php (responde JSON)
     │
     ├──► POST → PedidoController::handle() → Model → respuesta flash + redirect
     │
     └──► GET page=* → Vista PHP correspondiente
```

- **Router**: `index.php` actúa como front controller, detecta la página solicitada (`?page=`) y las acciones POST.
- **Models**: clases estáticas con métodos PDO (no ORM). Separan toda la lógica de acceso a datos.
- **Controllers**: reciben el `$_POST`, validan, llaman al modelo y devuelven un array con `msg`, `type` y `redirect`.
- **Views**: archivos PHP que reciben variables del router y renderizan el HTML.
- **Services**: lógica de integración con sistemas externos (API de transportadora).
- **API**: endpoint independiente que responde JSON directamente.

---

## Estructura de carpetas

```
modulo-pedidos/
│
├── index.php                  # Front controller / Router principal
├── .htaccess                  # Reescritura de URLs para Apache
├── .gitignore
│
├── config/
│   ├── app.php                # Constantes globales (APP_NAME, BASE_URL, estados, API key)
│   └── database.php           # Conexión PDO singleton (función db())
│
├── models/
│   ├── Pedido.php             # CRUD de pedidos + reporte mensual
│   └── Cliente.php            # Consulta de clientes (listado y por ID)
│
├── controllers/
│   └── PedidoController.php   # Maneja acciones POST: crear y cambiar-estado
│
├── services/
│   └── TransportadoraService.php  # Integración con API de tarifas + fallback simulado
│
├── api/
│   └── pedidos.php            # Endpoint REST: GET /api/pedidos
│
├── views/
│   ├── layout.php             # Cabecera HTML común (nav, estilos)
│   ├── layout_end.php         # Cierre HTML común (footer)
│   ├── pedidos/
│   │   ├── listado.php        # Vista: tabla de pedidos con filtros
│   │   ├── crear.php          # Vista: formulario de nuevo pedido
│   │   └── cambiar_estado.php # Vista: formulario para cambiar estado
│   └── reporte/
│       └── mensual.php        # Vista: reporte de ventas del mes
│
├── database/
│   └── schema.sql             # DDL completo + datos de prueba
│
└── public/
    └── css/                   # Hojas de estilo CSS
```

---

## Base de datos

### Diagrama de tablas

```
┌─────────────┐         ┌──────────────────────────────┐
│  clientes   │         │          pedidos             │
├─────────────┤         ├──────────────────────────────┤
│ id (PK)     │◄──────┐ │ id (PK)                      │
│ nombre      │       └─│ cliente_id (FK)              │
│ email       │         │ descripcion                  │
│ telefono    │         │ total (DECIMAL 12,2)         │
│ direccion   │         │ estado (ENUM)                │
│ ciudad      │         │ costo_envio (DECIMAL 10,2)  │
│ creado_en   │         │ ciudad_destino               │
│ actualizado_en│        │ creado_en                    │
└─────────────┘         │ actualizado_en               │
                        └──────────────────────────────┘

┌──────────────────────┐
│       usuarios       │
├──────────────────────┤
│ id (PK)              │
│ nombre               │
│ email (UNIQUE)       │
│ password (bcrypt)    │
│ rol (admin/operador) │
│ creado_en            │
└──────────────────────┘
```

### Estados del pedido

```
pendiente  ──►  despachado  ──►  entregado
```

> ⚠️ Un pedido en estado **entregado** no puede modificarse (salvo usuarios con rol `admin`).

### Script SQL

El archivo `database/schema.sql` contiene:
- Creación de la base de datos `modulo_pedidos`.
- DDL completo de las tres tablas (`clientes`, `pedidos`, `usuarios`).
- **Datos de prueba**: 5 clientes, 12 pedidos de distintos meses y estados, y 2 usuarios de ejemplo.

---

## Configuración e instalación

### Requisitos previos

- [XAMPP](https://www.apachefriends.org/) con **Apache** y **MySQL** activos
- **PHP 8.1** o superior (con extensiones `pdo_mysql` y `curl` habilitadas)
- **mod_rewrite** de Apache habilitado

### Pasos de instalación

**1. Clonar el repositorio**
```bash
git clone <url-del-repositorio> C:\xampp\htdocs\modulo-pedidos
```

**2. Importar la base de datos**

Abre **phpMyAdmin** (`http://localhost/phpmyadmin`) y ejecuta el script SQL:
```
database/schema.sql
```
O desde la terminal:
```bash
mysql -u root -p < database/schema.sql
```

**3. Configurar credenciales de base de datos**

Edita `config/database.php` si tus credenciales son distintas:
```php
define('DB_USER', 'root');   // Tu usuario de MySQL
define('DB_PASS', 'root');   // Tu contraseña de MySQL
```

**4. Configurar la URL base**

En `config/app.php`, ajusta la URL base si tu entorno es diferente:
```php
define('BASE_URL', 'http://localhost/modulo-pedidos');
```

**5. Configurar la clave de la API de transportadora** *(opcional)*

```powershell
# En Windows (PowerShell), antes de iniciar Apache:
$env:TRANSPORTADORA_API_KEY = "tu_clave_real_aqui"
```
O bien, edita directamente `config/app.php`:
```php
define('TRANSPORTADORA_API_KEY', 'tu_clave_real_aqui');
```

**6. Habilitar mod_rewrite en XAMPP**

Asegúrate de que en `C:\xampp\apache\conf\httpd.conf` esté descomentado:
```apache
LoadModule rewrite_module modules/mod_rewrite.so
```
Y que el `AllowOverride` del directorio `htdocs` sea `All`.

**7. Acceder a la aplicación**

```
http://localhost/modulo-pedidos/
```

---

## Uso de la aplicación

### Páginas disponibles

| URL | Descripción |
|---|---|
| `http://localhost/modulo-pedidos/` | Listado de todos los pedidos |
| `?page=crear` | Formulario para crear un nuevo pedido |
| `?page=cambiar-estado&id={id}` | Formulario para cambiar el estado de un pedido |
| `?page=reporte` | Reporte mensual de ventas por cliente |
| `?estado=pendiente` | Listado filtrado por estado |

### Flujo principal

```
1. Crear pedido (estado inicial: "pendiente")
        │
        ▼
2. Cambiar a "despachado"
   └── El sistema consulta la API de transportadora
   └── Se guarda el costo de envío calculado automáticamente
        │
        ▼
3. Cambiar a "entregado"
   └── El pedido queda bloqueado (no se puede modificar)
```

---

## API REST

El módulo expone un endpoint JSON para integraciones externas.

### `GET /api/pedidos`

Retorna todos los pedidos. Opcionalmente se puede filtrar por estado.

**Parámetros de query string:**

| Parámetro | Valores posibles | Descripción |
|---|---|---|
| `estado` | `pendiente`, `despachado`, `entregado` | Filtra pedidos por estado (opcional) |

**Ejemplo de petición:**
```
GET http://localhost/modulo-pedidos/api/pedidos?estado=pendiente
```

**Ejemplo de respuesta:**
```json
{
  "success": true,
  "filtro": "pendiente",
  "total": 2,
  "data": [
    {
      "id": 1,
      "cliente": "Almacenes El Progreso",
      "cliente_ciudad": "Bogotá",
      "descripcion": "Caja de electrodomésticos mixtos (50 unidades)",
      "total": 1500000.00,
      "costo_envio": null,
      "estado": "pendiente",
      "ciudad_destino": "Medellín",
      "creado_en": "2026-10-21 00:00:00",
      "actualizado_en": "2026-10-21 00:00:00"
    }
  ]
}
```

**Cabeceras de respuesta:**
- `Content-Type: application/json; charset=utf-8`
- `Access-Control-Allow-Origin: *` (CORS habilitado)

> ⚠️ Solo acepta método **GET**. Cualquier otro método devuelve `405 Method Not Allowed`.

---

## Servicio de Transportadora

`services/TransportadoraService.php` encapsula la comunicación con la API externa de tarifas de envío.

### Funcionamiento

Cuando un pedido cambia a estado **"despachado"**, el controlador invoca:
```php
$servicio   = new TransportadoraService();
$cotizacion = $servicio->cotizar(origen: 'Bogotá', destino: $ciudad_destino, peso: 5.0);
```

**Petición a la API externa (POST JSON):**
```json
{
  "origen":  "Bogotá",
  "destino": "Medellín",
  "peso_kg": 5.0
}
```
Con cabecera: `Authorization: Bearer {TRANSPORTADORA_API_KEY}`

### Fallback simulado

Si la API no está disponible (timeout, error HTTP, dominio `.test` en desarrollo), el servicio calcula una **tarifa local simulada** con la siguiente fórmula:

```
costo = tarifa_base($15.000) + (distancia_km × $10/km) + (peso_kg × $2.000/kg)
```

| Ciudad destino | Distancia estimada |
|---|---|
| Bogotá | 0 km |
| Pereira / Manizales | ~315 km |
| Bucaramanga | 400 km |
| Medellín | 410 km |
| Cali | 460 km |
| Barranquilla | 1.000 km |
| Cartagena | 1.050 km |
| Otras ciudades | 500 km (por defecto) |

---

## Reglas de negocio

1. **Estado inicial fijo**: todo pedido nuevo se crea con estado `pendiente` (no configurable desde el formulario).
2. **Pedidos entregados bloqueados**: un pedido en estado `entregado` **no puede modificarse**, salvo que se envíe el campo `es_admin = true` en el POST.
3. **Costo de envío automático**: se calcula y guarda **únicamente** al cambiar a estado `despachado`.
4. **Reporte mensual**: incluye solo pedidos del **mes y año actual**, agrupados por cliente, ordenados por total vendido descendente.
5. **Validación de estados**: solo se aceptan los estados definidos en la constante `ESTADOS_PEDIDO` (`['pendiente', 'despachado', 'entregado']`).

---

## Variables de entorno

| Variable | Descripción | Valor por defecto |
|---|---|---|
| `TRANSPORTADORA_API_KEY` | Clave Bearer para la API de tarifas | `TU_CREDENCIAL_AQUI` |

El resto de la configuración se gestiona directamente en los archivos de `config/`:

| Constante | Archivo | Descripción |
|---|---|---|
| `APP_NAME` | `config/app.php` | Nombre de la aplicación |
| `APP_VERSION` | `config/app.php` | Versión actual (`1.0.0`) |
| `BASE_URL` | `config/app.php` | URL base de la app |
| `TRANSPORTADORA_API_URL` | `config/app.php` | URL del endpoint de tarifas |
| `DB_HOST` | `config/database.php` | Host de la base de datos |
| `DB_PORT` | `config/database.php` | Puerto MySQL (por defecto `3306`) |
| `DB_NAME` | `config/database.php` | Nombre de la base de datos |
| `DB_USER` | `config/database.php` | Usuario MySQL |
| `DB_PASS` | `config/database.php` | Contraseña MySQL |

---

## Usuarios de prueba

Los usuarios de prueba incluidos en el script SQL tienen la contraseña **`password`** (hash bcrypt):

| Email | Contraseña | Rol |
|---|---|---|
| `admin@uva3.com` | `password` | admin |
| `operador@uva3.com` | `password` | operador |

> ⚠️ Para producción: reemplazar los hashes con `password_hash('contraseña_real', PASSWORD_BCRYPT)`.

---

## Versión

**v1.0.0** — UVA3 Logística · PHP 8.1+ · MySQL 8+
