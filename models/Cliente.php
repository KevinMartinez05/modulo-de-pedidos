<?php
// =============================================================
// models/Cliente.php — Modelo de Cliente con PDO
// =============================================================

require_once __DIR__ . '/../config/database.php';

class Cliente {

    // Listar todos los clientes (para selects en formularios)
    public static function todos(): array {
        $stmt = db()->query("SELECT id, nombre, ciudad FROM clientes ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    // Obtener un cliente por ID
    public static function obtener(int $id): ?array {
        $stmt = db()->prepare("SELECT * FROM clientes WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
