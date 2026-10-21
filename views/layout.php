<?php
// =============================================================
// views/layout.php — Plantilla base HTML
// Uso: incluir al inicio de cada vista con $pageTitle definido
// =============================================================

$pageTitle = $pageTitle ?? 'Módulo de Pedidos';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Módulo de gestión de pedidos para UVA3 Logística — control de estados, clientes y reportes." />
  <title><?= htmlspecialchars($pageTitle) ?> — UVA3 Logística</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css" />
</head>
<body>
<div class="wrapper">

  <!-- ── Sidebar ─────────────────────────────────────────── -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <span>📦</span> UVA3 Logística
    </div>

    <nav>
      <a href="<?= BASE_URL ?>/"
         class="nav-link <?= $activePage === 'pedidos' ? 'active' : '' ?>">
        <span class="nav-icon">🗂️</span> Pedidos
      </a>
      <a href="<?= BASE_URL ?>/?page=crear"
         class="nav-link <?= $activePage === 'crear' ? 'active' : '' ?>">
        <span class="nav-icon">➕</span> Nuevo pedido
      </a>
      <a href="<?= BASE_URL ?>/?page=reporte"
         class="nav-link <?= $activePage === 'reporte' ? 'active' : '' ?>">
        <span class="nav-icon">📊</span> Reporte mensual
      </a>
    </nav>

    <div style="margin-top:auto; font-size:.75rem; color:var(--text-muted); padding:.5rem">
      v1.0.0 &nbsp;·&nbsp; PHP + PDO
    </div>
  </aside>

  <!-- ── Contenido principal ──────────────────────────────── -->
  <main class="main">
    <div class="topbar">
      <h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
      <span class="badge-role">Operador</span>
    </div>

    <?php if (!empty($flashMsg)): ?>
      <div class="alert alert-<?= $flashType ?? 'info' ?>">
        <?= htmlspecialchars($flashMsg) ?>
      </div>
    <?php endif; ?>
