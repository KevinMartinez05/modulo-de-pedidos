<?php
// =============================================================
// api/pedidos.php — Endpoint: GET /api/pedidos?estado=...
// =============================================================

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Pedido.php';

// Solo aceptar método GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Método no permitido. Use GET.']);
    exit;
}

// Cabeceras CORS + JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$estado  = $_GET['estado'] ?? '';
$pedidos = Pedido::listar($estado);

// Formatear respuesta
$data = array_map(fn($p) => [
    'id'              => (int)    $p['id'],
    'cliente'         => $p['cliente_nombre'],
    'cliente_ciudad'  => $p['cliente_ciudad'],
    'descripcion'     => $p['descripcion'],
    'total'           => (float)  $p['total'],
    'costo_envio'     => $p['costo_envio'] !== null ? (float) $p['costo_envio'] : null,
    'estado'          => $p['estado'],
    'ciudad_destino'  => $p['ciudad_destino'],
    'creado_en'       => $p['creado_en'],
    'actualizado_en'  => $p['actualizado_en'],
], $pedidos);

echo json_encode([
    'success' => true,
    'filtro'  => $estado ?: null,
    'total'   => count($data),
    'data'    => $data,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
