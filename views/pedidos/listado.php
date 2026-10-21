<?php
// =============================================================
// views/pedidos/listado.php — Lista de pedidos con filtro
// =============================================================

$pageTitle  = 'Pedidos';
$activePage = 'pedidos';

$estadoFiltro = $_GET['estado'] ?? '';
$pedidos      = Pedido::listar($estadoFiltro);

require_once __DIR__ . '/../layout.php';
?>

<!-- Filtro por estado -->
<div class="card">
  <form method="GET" action="<?= BASE_URL ?>/" class="filter-bar">
    <input type="hidden" name="page" value="pedidos" />

    <label for="estado-filtro" class="form-label" style="margin:0;white-space:nowrap">
      Filtrar por estado:
    </label>

    <select id="estado-filtro" name="estado" class="form-control" style="width:200px">
      <option value="">— Todos —</option>
      <option value="pendiente"  <?= $estadoFiltro === 'pendiente'  ? 'selected' : '' ?>>Pendiente</option>
      <option value="despachado" <?= $estadoFiltro === 'despachado' ? 'selected' : '' ?>>Despachado</option>
      <option value="entregado"  <?= $estadoFiltro === 'entregado'  ? 'selected' : '' ?>>Entregado</option>
    </select>

    <button type="submit" id="btn-filtrar" class="btn btn-primary">🔍 Filtrar</button>
    <a href="<?= BASE_URL ?>/" class="btn btn-ghost">✖ Limpiar</a>
    <a href="<?= BASE_URL ?>/?page=crear" class="btn btn-success" style="margin-left:auto">
      ➕ Nuevo pedido
    </a>
  </form>
</div>

<!-- Tabla de pedidos -->
<div class="card">
  <div class="card-title">
    🗂️ Pedidos
    <?php if ($estadoFiltro): ?>
      <span class="badge badge-<?= $estadoFiltro ?>" style="margin-left:.5rem">
        <?= ucfirst($estadoFiltro) ?>
      </span>
    <?php endif; ?>
    <small style="margin-left:auto;color:var(--text-muted);font-weight:400">
      <?= count($pedidos) ?> resultado(s)
    </small>
  </div>

  <?php if (empty($pedidos)): ?>
    <div class="alert alert-info">No hay pedidos<?= $estadoFiltro ? " con estado «{$estadoFiltro}»" : '' ?>.</div>
  <?php else: ?>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Cliente</th>
          <th>Descripción</th>
          <th>Total</th>
          <th>Costo envío</th>
          <th>Estado</th>
          <th>Destino</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pedidos as $p): ?>
        <tr>
          <td><strong>#<?= $p['id'] ?></strong></td>
          <td>
            <?= htmlspecialchars($p['cliente_nombre']) ?>
            <br><small style="color:var(--text-muted)"><?= htmlspecialchars($p['cliente_ciudad']) ?></small>
          </td>
          <td style="max-width:220px">
            <span title="<?= htmlspecialchars($p['descripcion']) ?>">
              <?= htmlspecialchars(mb_strimwidth($p['descripcion'], 0, 60, '…')) ?>
            </span>
          </td>
          <td><strong>$<?= number_format($p['total'], 0, ',', '.') ?></strong></td>
          <td>
            <?php if ($p['costo_envio'] !== null): ?>
              $<?= number_format($p['costo_envio'], 0, ',', '.') ?>
            <?php else: ?>
              <span style="color:var(--text-muted)">—</span>
            <?php endif; ?>
          </td>
          <td>
            <span class="badge badge-<?= $p['estado'] ?>">
              <?= ucfirst($p['estado']) ?>
            </span>
          </td>
          <td><?= htmlspecialchars($p['ciudad_destino'] ?? '—') ?></td>
          <td>
            <small><?= date('d/m/Y', strtotime($p['creado_en'])) ?></small>
          </td>
          <td>
            <?php if ($p['estado'] !== 'entregado'): ?>
              <a href="<?= BASE_URL ?>/?page=cambiar-estado&id=<?= $p['id'] ?>"
                 class="btn btn-warning btn-sm"
                 id="btn-estado-<?= $p['id'] ?>">
                🔄 Estado
              </a>
            <?php else: ?>
              <span style="color:var(--text-muted);font-size:.8rem">🔒 Entregado</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout_end.php'; ?>
