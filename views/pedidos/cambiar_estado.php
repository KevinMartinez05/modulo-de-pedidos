<?php
// =============================================================
// views/pedidos/cambiar_estado.php — Formulario para cambiar estado
// $pedido viene del index.php
// =============================================================

$pageTitle  = 'Cambiar estado — Pedido #' . $pedido['id'];
$activePage = 'pedidos';

require_once __DIR__ . '/../layout.php';

// Estados disponibles según el estado actual
$transiciones = [
    'pendiente'  => ['despachado'],
    'despachado' => ['entregado'],
    'entregado'  => [],   // No se puede cambiar (salvo admin)
];
$posibles = $transiciones[$pedido['estado']] ?? [];
?>

<div class="card" style="max-width:560px">
  <div class="card-title">🔄 Cambiar estado del pedido #<?= $pedido['id'] ?></div>

  <!-- Info del pedido -->
  <div style="background:var(--surface2);border-radius:8px;padding:1rem;margin-bottom:1.5rem;font-size:.875rem">
    <div style="margin-bottom:.4rem">
      <strong>Cliente:</strong> <?= htmlspecialchars($pedido['cliente_nombre']) ?>
    </div>
    <div style="margin-bottom:.4rem">
      <strong>Descripción:</strong> <?= htmlspecialchars($pedido['descripcion']) ?>
    </div>
    <div style="margin-bottom:.4rem">
      <strong>Total:</strong> $<?= number_format($pedido['total'], 0, ',', '.') ?>
    </div>
    <div style="margin-bottom:.4rem">
      <strong>Destino:</strong> <?= htmlspecialchars($pedido['ciudad_destino'] ?? '—') ?>
    </div>
    <div>
      <strong>Estado actual:</strong>
      <span class="badge badge-<?= $pedido['estado'] ?>">
        <?= ucfirst($pedido['estado']) ?>
      </span>
    </div>
  </div>

  <?php if ($pedido['estado'] === 'entregado'): ?>
    <div class="alert alert-error">
      🔒 Este pedido ya fue entregado y no puede modificarse.
    </div>
    <a href="<?= BASE_URL ?>/" class="btn btn-ghost">← Volver</a>

  <?php elseif (empty($posibles)): ?>
    <div class="alert alert-info">No hay transiciones disponibles para este estado.</div>
    <a href="<?= BASE_URL ?>/" class="btn btn-ghost">← Volver</a>

  <?php else: ?>
    <form method="POST" action="<?= BASE_URL ?>/" id="form-cambiar-estado">
      <input type="hidden" name="action"    value="cambiar-estado" />
      <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>" />

      <div class="form-group">
        <label for="nuevo-estado" class="form-label">Nuevo estado *</label>
        <select id="nuevo-estado" name="estado" class="form-control" required>
          <option value="">— Selecciona —</option>
          <?php foreach ($posibles as $e): ?>
            <option value="<?= $e ?>"><?= ucfirst($e) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <?php if (in_array('despachado', $posibles)): ?>
        <div class="alert alert-info" style="font-size:.8rem">
          ℹ️ Al marcar como <strong>Despachado</strong>, el sistema calculará automáticamente
          el costo de envío consultando la API de la transportadora.
        </div>
      <?php endif; ?>

      <div style="display:flex;gap:.75rem;margin-top:1.5rem">
        <button type="submit" id="btn-confirmar-estado" class="btn btn-warning">
          ✅ Confirmar cambio
        </button>
        <a href="<?= BASE_URL ?>/" class="btn btn-ghost">Cancelar</a>
      </div>
    </form>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout_end.php'; ?>
