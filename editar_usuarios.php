<?php
// Archivo: admin.php
// Esta página permite al administrador gestionar los usuarios (CRUD).

include_once 'includes/conexion.php';
check_user_logged_in();

// Asegura que solo los administradores puedan acceder a esta página.
if ($_SESSION['perfil'] !== 'administrador') {
    header("Location: panel_control.php");
    exit();
}

$message = "";

// Lógica para añadir, editar o eliminar usuarios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id_usuario'])) {
        $id = $_POST['id_usuario'];
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuarios = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Usuario eliminado correctamente.";
        } else {
            $message = "Error al eliminar el usuario: " . $conn->error;
        }
        $stmt->close();
    } else {
        $id = isset($_POST['id_usuario']) ? $_POST['id_usuario'] : null;
        $username = $_POST['username'];
        $pass_user = $_POST['pass_user'];
        $perfil = $_POST['perfil'];

        // Encriptar la contraseña si se ha proporcionado una.
        $hashed_password = !empty($pass_user) ? password_hash($pass_user, PASSWORD_DEFAULT) : null;

        if ($id) { // Editar usuario
            if ($hashed_password) {
                $stmt = $conn->prepare("UPDATE usuarios SET username = ?, pass_user = ?, perfil = ? WHERE id_usuarios = ?");
                $stmt->bind_param("sssi", $username, $hashed_password, $perfil, $id);
            } else {
                $stmt = $conn->prepare("UPDATE usuarios SET username = ?, perfil = ? WHERE id_usuarios = ?");
                $stmt->bind_param("ssi", $username, $perfil, $id);
            }
            if ($stmt->execute()) {
                $message = "Usuario actualizado correctamente.";
            } else {
                $message = "Error al actualizar el usuario: " . $conn->error;
            }
            $stmt->close();
        } else { // Añadir nuevo usuario
            $stmt = $conn->prepare("INSERT INTO usuarios (username, pass_user, perfil) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $perfil);
            if ($stmt->execute()) {
                $message = "Usuario añadido correctamente.";
            } else {
                $message = "Error al añadir el usuario: " . $conn->error;
            }
            $stmt->close();
        }
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

        <h3>Añadir / Editar Usuario</h3>
        <form id="user-form" action="admin.php" method="POST">
            <input type="hidden" name="id_usuario" id="id_usuario">
            
            <label for="username">Nombre de Usuario:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="pass_user">Contraseña (dejar en blanco para no cambiar):</label>
            <input type="password" id="pass_user" name="pass_user">
            
            <label for="perfil">Perfil:</label>
            <select id="perfil" name="perfil">
                <option value="usuario">Usuario</option>
                <option value="administrador">Administrador</option>
            </select>
            
            <button type="submit" id="submit-button">Añadir Usuario</button>
        </form>

        <h3 style="margin-top: 40px;">Usuarios Registrados</h3>
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
                    <?php while($row = $usuarios_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id_usuarios']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['perfil']); ?></td>
                            <td>
                                <button onclick='loadUserForEdit(<?php echo json_encode($row); ?>)'>Editar</button>
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
    
    <script>
    function loadUserForEdit(user) {
        document.getElementById('id_usuario').value = user.id_usuarios;
        document.getElementById('username').value = user.username;
        document.getElementById('perfil').value = user.perfil;
        document.getElementById('submit-button').innerText = 'Guardar Cambios';
    }
    </script>
</body>
</html>
