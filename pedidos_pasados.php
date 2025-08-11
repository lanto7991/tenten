<?php
// Archivo: pedidos_pasados.php
// Página de visualización del historial de pedidos entregados y cancelados.
// Accesible solo por el administrador.

include_once 'includes/conexion.php';
// Verifica que el usuario haya iniciado sesión y tenga perfil de administrador.
// Asegúrate de que esta función existe y maneja la redirección si el usuario no es admin.
check_admin_access();

// Consulta para obtener todos los envíos que ya han sido entregados o cancelados.
// Se hace un JOIN con las tablas 'usuarios' e 'items' para obtener los nombres correspondientes.
$sql_envios = "SELECT
    e.id_envio,
    e.cantidad,
    e.direccion_envio,
    e.nombre_destinatario,
    e.estado,
    e.fecha_envio,
    e.asignado_a,
    u.username AS usuario_pedido,
    i.nombre_item
FROM envios e
JOIN usuarios u ON e.id_usuario = u.id_usuarios
JOIN items i ON e.id_item = i.id_item
WHERE e.estado IN ('Entregado', 'Cancelado')
ORDER BY e.fecha_envio DESC";

// Intenta ejecutar la consulta y maneja el error si falla
$envios_result = $conn->query($sql_envios);
if (!$envios_result) {
    die("Error en la consulta. Por favor, verifica que todas las columnas existan en la tabla 'envios'.<br>Error de la base de datos: " . $conn->error);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pedidos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Historial de Pedidos</h2>

        <?php if ($envios_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Item</th>
                        <th>Cantidad</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Asignado a</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $envios_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id_envio']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_item']); ?></td>
                            <td><?php echo htmlspecialchars($row['cantidad']); ?></td>
                            <td><?php echo htmlspecialchars($row['usuario_pedido']); ?></td>
                            <td><?php echo htmlspecialchars($row['estado']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_envio']); ?></td>
                            <td><?php echo htmlspecialchars($row['asignado_a'] ?: 'No asignado'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay pedidos en el historial.</p>
        <?php endif; ?>

        <div class="nav-links">
            <a href="panel_control.php">Volver al Panel</a>
        </div>
    </div>
</body>
</html>
