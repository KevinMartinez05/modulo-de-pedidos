<?php
// =============================================================
// index.php — Router principal del módulo
// =============================================================

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Pedido.php';
require_once __DIR__ . '/models/Cliente.php';

// Ruta amigable: detectar si viene de /api/
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Redirigir peticiones a la API
if (str_contains($uri, '/api/pedidos')) {
    require_once __DIR__ . '/api/pedidos.php';
    exit;
}

// Obtener la página solicitada
$page = $_GET['page'] ?? 'pedidos';

// Mensajes flash simples (sin sesión)
$flashMsg  = '';
$flashType = 'info';

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/controllers/PedidoController.php';
    $ctrl   = new PedidoController();
    $result = $ctrl->handle($_POST);

    $flashMsg  = $result['msg'];
    $flashType = $result['type'];

    // Redirigir después del POST para evitar reenvío del formulario
    $redirect = $result['redirect'] ?? '';
    if ($redirect) {
        header("Location: {$redirect}");
        exit;
    }
}

// Enrutar a la vista correcta
switch ($page) {
    case 'crear':
        require_once __DIR__ . '/views/pedidos/crear.php';
        break;
    case 'cambiar-estado':
        $id     = (int) ($_GET['id'] ?? 0);
        $pedido = Pedido::obtener($id);
        if (!$pedido) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        require_once __DIR__ . '/views/pedidos/cambiar_estado.php';
        break;
    case 'reporte':
        require_once __DIR__ . '/views/reporte/mensual.php';
        break;
    default: // 'pedidos'
        require_once __DIR__ . '/views/pedidos/listado.php';
}
