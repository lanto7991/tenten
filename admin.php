<?php
// Archivo: admin.php
// Esta página muestra la lista de usuarios y permite al administrador eliminarlos y agregarlos.

include_once 'includes/conexion.php';
check_user_logged_in();

// Asegura que solo los administradores puedan acceder a esta página.
if ($_SESSION['perfil'] !== 'administrador') {
    header("Location: panel_control.php");
    exit();
}

$message = "";
$current_user_id = $_SESSION['id_usuarios'];

// Lógica para eliminar usuarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id_usuario'])) {
    $id = $_POST['id_usuario'];
    
    // Evitar que el administrador se elimine a sí mismo
    if ($id == $current_user_id) {
        $message = "No puedes eliminar tu propia cuenta.";
    } else {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuarios = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Usuario eliminado correctamente.";
        } else {
            $message = "Error al eliminar el usuario: " . $conn->error;
        }
        $stmt->close();
    }
}

// Obtener todos los usuarios para la tabla
$usuarios_result = $conn->query("SELECT id_usuarios, username, perfil FROM usuarios ORDER BY id_usuarios DESC");
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Gestión de Usuarios</h2>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

       <div style = "text-align: center"> <h3 style="margin-top: 40px;">Usuarios Registrados</h3> </div>
        <?php if ($usuarios_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Perfil</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 10px;">
                            <a href="registro.php" class="button">Agregar Nuevo Usuario</a>|
                        </td>
                    </tr>
                    <?php while($row = $usuarios_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id_usuarios']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['perfil']); ?></td>
                            <td>
                                <a href="editar_usuario.php?id=<?php echo htmlspecialchars($row['id_usuarios']); ?>">
                                    <button type="submit" class="button-danger">Editar</button>
                                </a>
                                <form action="admin.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar a este usuario?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($row['id_usuarios']); ?>">
                                    <button type="submit" class="button-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay usuarios registrados.</p>
        <?php endif; ?>

        <div class="nav-links">
            <a href="panel_control.php">Volver al Panel</a>
        </div>
    </div>
</body>
</html>