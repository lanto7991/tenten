<?php
// Archivo: mover_pedidos_historicos.php
// Script para mover automáticamente los pedidos del día a la tabla de historial.

// Incluir el archivo de conexión a la base de datos
require_once 'includes/conexion.php';

// Iniciar una transacción para asegurar que todas las operaciones se completen
// con éxito o ninguna se aplique
$conn->begin_transaction();

try {
    // 1. Mover los pedidos de la tabla 'pedidos' a la tabla 'pedidos_pasados'
    // Se insertan todos los pedidos que no estén en estado 'Pendiente'
    $sql_insert = "
    INSERT INTO pedidos_pasados (
        id_pedido_original,
        nombre_destinatario,
        direccion_envio,
        id_item,
        cantidad,
        estado,
        asignado_a,
        fecha_ingreso,
        fecha_cierre
    )
    SELECT
        id,
        nombre_destinatario,
        direccion_envio,
        id_item,
        cantidad,
        estado,
        asignado_a,
        fecha_ingreso,
        NOW()
    FROM pedidos
    WHERE estado != 'Pendiente'";

    if (!$conn->query($sql_insert)) {
        throw new Exception("Error al insertar en pedidos_pasados: " . $conn->error);
    }

    // 2. Eliminar los pedidos que se acaban de mover de la tabla 'pedidos'
    $sql_delete = "DELETE FROM pedidos WHERE estado != 'Pendiente'";

    if (!$conn->query($sql_delete)) {
        throw new Exception("Error al eliminar de pedidos: " . $conn->error);
    }

    // 3. Confirmar la transacción si todo fue exitoso
    $conn->commit();
    echo "Proceso de mover pedidos al historial completado con éxito.";

} catch (Exception $e) {
    // Si algo falla, revertir todas las operaciones de la transacción
    $conn->rollback();
    echo "Error en el proceso: " . $e->getMessage();
}

$conn->close();
?>
