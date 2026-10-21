<?php
// =============================================================
// config/app.php — Constantes globales de la aplicación
// =============================================================

define('APP_NAME',    'Módulo de Pedidos — UVA3 Logística');
define('APP_VERSION', '1.0.0');
define('BASE_URL',    'http://localhost/modulo-pedidos');

// Estados válidos de un pedido
define('ESTADOS_PEDIDO', ['pendiente', 'despachado', 'entregado']);

// API de la transportadora
define('TRANSPORTADORA_API_URL', 'https://tarifas.transportadora.test/v1/cotizar');
define('TRANSPORTADORA_API_KEY', getenv('TRANSPORTADORA_API_KEY') ?: 'TU_CREDENCIAL_AQUI');

// Zona horaria
date_default_timezone_set('America/Bogota');

// Mostrar errores solo en desarrollo (pon false en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);
