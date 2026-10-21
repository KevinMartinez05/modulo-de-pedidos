<?php
// =============================================================
// models/Pedido.php — Modelo de Pedido con PDO
// =============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

class Pedido {

    // ----------------------------------------------------------
    // Listar pedidos (con filtro opcional por estado)
    // ----------------------------------------------------------
    public static function listar(string $estado = ''): array {
        $sql = "
            SELECT  p.id,
                    p.descripcion,
                    p.total,
                    p.estado,
                    p.costo_envio,
                    p.ciudad_destino,
                    p.creado_en,
                    p.actualizado_en,
                    c.nombre   AS cliente_nombre,
                    c.email    AS cliente_email,
                    c.ciudad   AS cliente_ciudad
            FROM    pedidos  p
            JOIN    clientes c ON c.id = p.cliente_id
        ";

        $params = [];

        if (!empty($estado) && in_array($estado, ESTADOS_PEDIDO)) {
            $sql   .= " WHERE p.estado = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " ORDER BY p.creado_en DESC";

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------
    // Obtener un pedido por ID
    // ----------------------------------------------------------
    public static function obtener(int $id): ?array {
        $stmt = db()->prepare("
            SELECT  p.*,
                    c.nombre AS cliente_nombre,
                    c.email  AS cliente_email,
                    c.ciudad AS cliente_ciudad
            FROM    pedidos  p
            JOIN    clientes c ON c.id = p.cliente_id
            WHERE   p.id = :id
            LIMIT   1
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ----------------------------------------------------------
    // Crear un pedido nuevo
    // ----------------------------------------------------------
    public static function crear(array $datos): int {
        $stmt = db()->prepare("
            INSERT INTO pedidos (cliente_id, descripcion, total, estado, ciudad_destino)
            VALUES (:cliente_id, :descripcion, :total, 'pendiente', :ciudad_destino)
        ");
        $stmt->execute([
            ':cliente_id'    => $datos['cliente_id'],
            ':descripcion'   => $datos['descripcion'],
            ':total'         => $datos['total'],
            ':ciudad_destino'=> $datos['ciudad_destino'] ?? null,
        ]);
        return (int) db()->lastInsertId();
    }

    // ----------------------------------------------------------
    // Cambiar el estado de un pedido
    // ----------------------------------------------------------
    public static function cambiarEstado(int $id, string $nuevoEstado, ?float $costoEnvio = null): bool {
        // Regla: los entregados no se modifican (salvo admin, que se valida en el controlador)
        $pedido = self::obtener($id);
        if (!$pedido) return false;

        $extra  = '';
        $params = [':id' => $id, ':estado' => $nuevoEstado];

        if ($nuevoEstado === 'despachado' && $costoEnvio !== null) {
            $extra  = ', costo_envio = :costo_envio';
            $params[':costo_envio'] = $costoEnvio;
        }

        $stmt = db()->prepare("
            UPDATE pedidos
            SET    estado = :estado $extra
            WHERE  id     = :id
        ");
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    // ----------------------------------------------------------
    // Reporte: total vendido por cliente en el mes actual
    // ----------------------------------------------------------
    public static function reporteMensual(): array {
        $stmt = db()->prepare("
            SELECT  c.nombre         AS cliente,
                    c.email          AS email,
                    SUM(p.total)     AS total_vendido,
                    COUNT(p.id)      AS cantidad_pedidos
            FROM    pedidos  p
            JOIN    clientes c ON c.id = p.cliente_id
            WHERE   YEAR(p.creado_en)  = YEAR(CURDATE())
              AND   MONTH(p.creado_en) = MONTH(CURDATE())
            GROUP BY p.cliente_id, c.nombre, c.email
            ORDER BY total_vendido DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
