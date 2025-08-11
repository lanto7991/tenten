<?php
// Archivo: gestion_envios.php
// Esta página permite a los administradores ver y gestionar todos los envíos del sistema.

include_once 'includes/conexion.php';
check_user_logged_in();

// Asegura que solo los administradores puedan acceder a esta página.
if ($_SESSION['perfil'] !== 'administrador') {
    header("Location: panel_control.php");
    exit();
}

$message = "";

// Lógica para eliminar un envío
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id_envio'])) {
    $id = $_POST['id_envio'];
    
    $stmt = $conn->prepare("DELETE FROM envios WHERE id_envio = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Envío eliminado correctamente.";
    } else {
        $message = "Error al eliminar el envío: " . $conn->error;
    }
    $stmt->close();
}

// Obtener todos los envíos y los nombres de usuario asociados
$envios_result = $conn->query("
    SELECT e.id_envio, e.destino, e.estado, e.fecha_envio, u.username 
    FROM envios e
    JOIN usuarios u ON e.id_usuario = u.id_usuarios
    ORDER BY e.fecha_envio DESC
");
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Envíos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Gestión de Envíos</h2>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <!-- El botón se ha movido aquí para asegurar su visibilidad -->
        <div style="margin-bottom: 20px;">
            <a href="agregar_envio.php" class="button">Agregar Nuevo Envío</a>
        </div>

        <h3 style="margin-top: 40px;">Todos los Envíos</h3>
        <?php if ($envios_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID de Envío</th>
                        <th>Usuario</th>
                        <th>Destino</th>
                        <th>Estado</th>
                        <th>Fecha de Envío</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $envios_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id_envio']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['destino']); ?></td>
                            <td><?php echo htmlspecialchars($row['estado']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_envio']); ?></td>
                            <td>
                                <form action="gestion_envios.php" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este envío?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_envio" value="<?php echo htmlspecialchars($row['id_envio']); ?>">
                                    <button type="submit" class="button-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay envíos registrados.</p>
        <?php endif; ?>

        <div class="nav-links">
            <a href="panel_control.php">Volver al Panel</a>
        </div>
    </div>
</body>
</html>