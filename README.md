# Módulo de Pedidos — UVA3 Logística

Módulo de gestión de pedidos construido con **PHP puro + PDO + MySQL**, desarrollado como reto práctico de la Parte 5.

---

## 🚀 Instalación y ejecución

### Requisitos
- XAMPP (PHP ≥ 8.0, MySQL 5.7+)
- Navegador moderno

### Pasos

1. **Clonar el repositorio** en la carpeta `htdocs` de XAMPP:
   ```bash
   cd C:/xampp/htdocs
   git clone <URL_DEL_REPO> modulo-pedidos
   ```

2. **Importar la base de datos**:
   - Abre **phpMyAdmin** → Import → selecciona `database/schema.sql`
   - O ejecuta por consola:
     ```bash
     mysql -u root -p < database/schema.sql
     ```

3. **Configurar la conexión** en `config/database.php`:
   ```php
   define('DB_USER', 'root');
   define('DB_PASS', '');        // tu contraseña de MySQL
   ```

4. **Abrir en el navegador**:
   ```
   http://localhost/modulo-pedidos/
   ```

---

## 📋 Credenciales de prueba

| Rol | Email | Contraseña |
|-----|-------|------------|
| Admin | admin@uva3.com | password |
| Operador | operador@uva3.com | password |

---

## 🗂️ Estructura del proyecto

```
modulo-pedidos/
├── config/
│   ├── app.php          # Constantes globales
│   └── database.php     # Conexión PDO
├── models/
│   ├── Pedido.php       # Modelo de pedidos
│   └── Cliente.php      # Modelo de clientes
├── views/
│   ├── layout.php       # Plantilla base HTML
│   ├── pedidos/         # Vistas del módulo
│   └── reporte/         # Vista del reporte mensual
├── controllers/
│   └── PedidoController.php
├── api/
│   └── pedidos.php      # Endpoint GET /api/pedidos
├── services/
│   └── TransportadoraService.php   # Integración API tarifas
├── database/
│   └── schema.sql       # Migraciones y datos de prueba
├── public/
│   └── css/style.css    # Estilos
├── index.php            # Router principal
└── README.md
```

---

## 📡 API

### GET /api/pedidos
Devuelve los pedidos en formato JSON.

**Parámetros opcionales:**
| Parámetro | Valores | Descripción |
|-----------|---------|-------------|
| `estado` | `pendiente`, `despachado`, `entregado` | Filtra por estado |

**Ejemplo:**
```
GET http://localhost/modulo-pedidos/api/pedidos?estado=pendiente
```

---

## 📝 Registro de uso de IA

| # | Prompt enviado | Resultado obtenido | Correcciones aplicadas |
|---|----------------|-------------------|------------------------|
| 1 | Crear estructura completa de proyecto PHP con PDO para módulo de pedidos logísticos con tablas clientes/pedidos | Generó schema.sql, config, modelos y estructura de carpetas | Se ajustaron los datos de prueba para que algunos pedidos fueran del mes anterior |
| 2 | *(se completa a medida que avanza el proyecto)* | | |

---

*Proyecto desarrollado como parte del reto práctico — Parte 5. Ver informe final para detalles.*
