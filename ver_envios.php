<?php
// Archivo: ver_envios.php
// Página para que un usuario vea sus propios envíos.

include_once 'includes/conexion.php';
// Se verifica que el usuario esté logueado.
check_user_logged_in();

// Obtiene el ID del usuario de la sesión
$id_usuario = $_SESSION['id_usuarios'];

// Lógica para obtener los envíos del usuario actual
$sql_envios = "SELECT 
    e.id_envio,
    e.direccion_envio,
    e.nombre_destinatario,
    e.estado,
    e.fecha_ingreso,
    e.asignado_a,
    i.nombre_item
FROM envios e
JOIN items i ON e.id_item = i.id_item
WHERE e.id_usuario = ?
ORDER BY e.fecha_ingreso DESC";

$stmt = $conn->prepare($sql_envios);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$envios_result = $stmt->get_result();

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Envíos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Mis Envíos</h2>

        <?php if ($envios_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Item</th>
                        <th>Dirección</th>
                        <th>Destinatario</th>
                        <th>Asignado a</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $envios_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id_envio']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_item']); ?></td>
                            <td><?php echo htmlspecialchars($row['direccion_envio']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_destinatario']); ?></td>
                            <td><?php echo htmlspecialchars($row['asignado_a'] ?: 'No asignado'); ?></td>
                            <td><?php echo htmlspecialchars($row['estado']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_ingreso']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tienes envíos registrados.</p>
        <?php endif; ?>

        <div class="nav-links">
            <a href="panel_control.php">Volver al Panel</a>
        </div>
    </div>
</body>
</html>
