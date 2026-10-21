<?php
// =============================================================
// controllers/PedidoController.php
// Maneja todas las acciones POST del módulo de pedidos
// =============================================================

require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../services/TransportadoraService.php';

class PedidoController {

    /**
     * Despacha la acción según el campo 'action' del POST.
     * Retorna array con: msg, type, redirect
     */
    public function handle(array $post): array {
        $action = $post['action'] ?? '';

        return match($action) {
            'crear'         => $this->crear($post),
            'cambiar-estado'=> $this->cambiarEstado($post),
            default         => ['msg' => 'Acción desconocida.', 'type' => 'error', 'redirect' => '']
        };
    }

    // ----------------------------------------------------------
    // Crear un pedido nuevo
    // ----------------------------------------------------------
    private function crear(array $post): array {
        // Validación básica
        $clienteId    = (int)   trim($post['cliente_id']    ?? '');
        $descripcion  = trim($post['descripcion'] ?? '');
        $total        = (float) str_replace(',', '.', $post['total'] ?? '0');
        $ciudadDestino= trim($post['ciudad_destino'] ?? '');

        if (!$clienteId || !$descripcion || $total <= 0) {
            return [
                'msg'      => 'Por favor completa todos los campos requeridos.',
                'type'     => 'error',
                'redirect' => ''
            ];
        }

        $id = Pedido::crear([
            'cliente_id'     => $clienteId,
            'descripcion'    => $descripcion,
            'total'          => $total,
            'ciudad_destino' => $ciudadDestino,
        ]);

        return [
            'msg'      => "✅ Pedido #{$id} creado correctamente.",
            'type'     => 'success',
            'redirect' => BASE_URL . '/'
        ];
    }

    // ----------------------------------------------------------
    // Cambiar el estado de un pedido
    // ----------------------------------------------------------
    private function cambiarEstado(array $post): array {
        $id          = (int) ($post['pedido_id'] ?? 0);
        $nuevoEstado = trim($post['estado'] ?? '');
        $esAdmin     = (bool) ($post['es_admin'] ?? false); // Simplificado

        if (!$id || !in_array($nuevoEstado, ESTADOS_PEDIDO)) {
            return ['msg' => 'Datos inválidos.', 'type' => 'error', 'redirect' => ''];
        }

        // Obtener el pedido actual
        $pedido = Pedido::obtener($id);
        if (!$pedido) {
            return ['msg' => 'Pedido no encontrado.', 'type' => 'error', 'redirect' => ''];
        }

        // REGLA: pedidos entregados no se pueden modificar (salvo admin)
        if ($pedido['estado'] === 'entregado' && !$esAdmin) {
            return [
                'msg'      => '🔒 Los pedidos entregados no se pueden modificar.',
                'type'     => 'error',
                'redirect' => ''
            ];
        }

        // Si se despacha → calcular costo de envío
        $costoEnvio = null;
        if ($nuevoEstado === 'despachado') {
            $servicio   = new TransportadoraService();
            $cotizacion = $servicio->cotizar(
                origen  : 'Bogotá',
                destino : $pedido['ciudad_destino'] ?? 'Bogotá',
                peso    : 5.0  // Peso por defecto en kg (se puede hacer configurable)
            );
            $costoEnvio = $cotizacion['costo'] ?? null;
        }

        $ok = Pedido::cambiarEstado($id, $nuevoEstado, $costoEnvio);

        if (!$ok) {
            return ['msg' => 'No se pudo actualizar el pedido.', 'type' => 'error', 'redirect' => ''];
        }

        $extra = ($costoEnvio !== null)
            ? " | Costo de envío: $" . number_format($costoEnvio, 2)
            : '';

        return [
            'msg'      => "✅ Pedido #{$id} actualizado a «{$nuevoEstado}»{$extra}.",
            'type'     => 'success',
            'redirect' => BASE_URL . '/'
        ];
    }
}
