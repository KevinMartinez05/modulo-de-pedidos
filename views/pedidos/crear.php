<?php
// =============================================================
// views/pedidos/crear.php — Formulario para crear un pedido
// =============================================================

$pageTitle  = 'Nuevo pedido';
$activePage = 'crear';

$clientes = Cliente::todos();

require_once __DIR__ . '/../layout.php';
?>

<div class="card" style="max-width:640px">
  <div class="card-title">➕ Crear nuevo pedido</div>

  <form method="POST" action="<?= BASE_URL ?>/" id="form-crear-pedido">
    <input type="hidden" name="action" value="crear" />

    <!-- Cliente -->
    <div class="form-group">
      <label for="cliente_id" class="form-label">Cliente *</label>
      <select id="cliente_id" name="cliente_id" class="form-control" required>
        <option value="">— Selecciona un cliente —</option>
        <?php foreach ($clientes as $c): ?>
          <option value="<?= $c['id'] ?>">
            <?= htmlspecialchars($c['nombre']) ?>
            (<?= htmlspecialchars($c['ciudad']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Descripción -->
    <div class="form-group">
      <label for="descripcion" class="form-label">Descripción del pedido *</label>
      <textarea id="descripcion" name="descripcion"
                class="form-control" rows="3"
                placeholder="Ej: Caja de 50 electrodomésticos variados..."
                required></textarea>
    </div>

    <!-- Total -->
    <div class="form-group">
      <label for="total" class="form-label">Total (COP) *</label>
      <input type="number" id="total" name="total"
             class="form-control"
             placeholder="Ej: 1500000"
             min="0" step="0.01" required />
    </div>

    <!-- Ciudad destino -->
    <div class="form-group">
      <label for="ciudad_destino" class="form-label">Ciudad de destino</label>
      <select id="ciudad_destino" name="ciudad_destino" class="form-control">
        <option value="">— Selecciona ciudad —</option>
        <?php
        $ciudades = ['Bogotá','Medellín','Cali','Barranquilla','Bucaramanga','Cartagena','Pereira','Manizales'];
        foreach ($ciudades as $ciudad):
        ?>
          <option value="<?= $ciudad ?>"><?= $ciudad ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="display:flex;gap:.75rem;margin-top:1.5rem">
      <button type="submit" id="btn-guardar-pedido" class="btn btn-primary">
        💾 Guardar pedido
      </button>
      <a href="<?= BASE_URL ?>/" class="btn btn-ghost">Cancelar</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../layout_end.php'; ?>
