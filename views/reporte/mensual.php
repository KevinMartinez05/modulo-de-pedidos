<?php
// =============================================================
// views/reporte/mensual.php — Reporte total vendido por cliente
// =============================================================

$pageTitle  = 'Reporte mensual';
$activePage = 'reporte';

$reporte     = Pedido::reporteMensual();
$mesActual   = strftime('%B %Y') ?: date('F Y');
$totalGeneral= array_sum(array_column($reporte, 'total_vendido'));

require_once __DIR__ . '/../layout.php';
?>

<!-- Stats rápidas -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-value"><?= count($reporte) ?></div>
    <div class="stat-label">Clientes activos este mes</div>
  </div>
  <div class="stat-card">
    <div class="stat-value">$<?= number_format($totalGeneral / 1000000, 1) ?>M</div>
    <div class="stat-label">Total vendido en <?= date('F') ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= array_sum(array_column($reporte, 'cantidad_pedidos')) ?></div>
    <div class="stat-label">Pedidos en el mes</div>
  </div>
</div>

<!-- Tabla del reporte -->
<div class="card">
  <div class="card-title">
    📊 Total vendido por cliente —
    <span style="color:var(--accent)"><?= date('F Y') ?></span>
  </div>

  <?php if (empty($reporte)): ?>
    <div class="alert alert-info">No hay pedidos registrados en el mes actual.</div>
  <?php else: ?>

  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Cliente</th>
          <th>Email</th>
          <th>Pedidos</th>
          <th>Total vendido</th>
          <th>Participación</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reporte as $i => $r): ?>
        <?php $pct = $totalGeneral > 0 ? ($r['total_vendido'] / $totalGeneral * 100) : 0; ?>
        <tr>
          <td>
            <?php if ($i === 0): ?>
              🥇
            <?php elseif ($i === 1): ?>
              🥈
            <?php elseif ($i === 2): ?>
              🥉
            <?php else: ?>
              <?= $i + 1 ?>
            <?php endif; ?>
          </td>
          <td><strong><?= htmlspecialchars($r['cliente']) ?></strong></td>
          <td style="color:var(--text-muted)"><?= htmlspecialchars($r['email']) ?></td>
          <td><?= $r['cantidad_pedidos'] ?></td>
          <td>
            <strong style="color:var(--accent)">
              $<?= number_format($r['total_vendido'], 0, ',', '.') ?>
            </strong>
          </td>
          <td>
            <!-- Barra de progreso -->
            <div style="display:flex;align-items:center;gap:.5rem">
              <div style="flex:1;background:var(--surface2);border-radius:99px;height:8px;min-width:80px">
                <div style="background:var(--accent);height:8px;border-radius:99px;width:<?= round($pct) ?>%"></div>
              </div>
              <span style="font-size:.75rem;color:var(--text-muted);white-space:nowrap">
                <?= number_format($pct, 1) ?>%
              </span>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" style="font-weight:700;text-align:right">TOTAL</td>
          <td><strong><?= array_sum(array_column($reporte, 'cantidad_pedidos')) ?></strong></td>
          <td><strong style="color:var(--accent)">$<?= number_format($totalGeneral, 0, ',', '.') ?></strong></td>
          <td>100%</td>
        </tr>
      </tfoot>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout_end.php'; ?>
