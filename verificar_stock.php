<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$stmt = $pdo->query("
    SELECT f.id, f.nombre, ft.tamano, ft.stock, ft.id as tamano_id
    FROM fragancia_tamanos ft
    JOIN fragancias f ON f.id = ft.fragancia_id
    WHERE ft.stock <= 0 AND f.activo = 1
    ORDER BY f.nombre, ft.tamano
");

$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== TAMAÑOS SIN STOCK A ELIMINAR ===\n\n";

if (count($resultados) == 0) {
    echo "No hay tamaños sin stock.\n";
} else {
    $agrupados = [];
    foreach ($resultados as $row) {
        if (!isset($agrupados[$row['nombre']])) {
            $agrupados[$row['nombre']] = [];
        }
        $agrupados[$row['nombre']][] = $row;
    }

    foreach ($agrupados as $nombre => $tamanos) {
        echo "Fragancia: $nombre\n";
        foreach ($tamanos as $t) {
            echo "  - {$t['tamano']} (ID: {$t['tamano_id']}, Stock: {$t['stock']})\n";
        }
        echo "\n";
    }
}
?>
