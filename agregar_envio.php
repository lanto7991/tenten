<?php
// Archivo: agregar_envio.php
// Esta página permite a los administradores agregar un nuevo envío a un usuario.

include_once 'includes/conexion.php';
check_user_logged_in();

// Asegura que solo los administradores puedan acceder a esta página.
if ($_SESSION['perfil'] !== 'administrador') {
    header("Location: panel_control.php");
    exit();
}

$message = "";

// Lógica para procesar la adición del envío
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_POST['id_usuario'];
    $destino = $_POST['destino'];
    $estado = $_POST['estado'];

    // Preparar la consulta para evitar inyección SQL
    $stmt = $conn->prepare("INSERT INTO envios (id_usuario, destino, estado) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id_usuario, $destino, $estado);
    
    if ($stmt->execute()) {
        $message = "Envío agregado correctamente.";
    } else {
        $message = "Error al agregar el envío: " . $conn->error;
    }
    $stmt->close();

    // Después de agregar el envío, redirigir al panel de gestión de envíos.
    header("Location: gestion_envios.php");
    exit();
}

// Obtener la lista de todos los usuarios para el formulario
$usuarios_result = $conn->query("SELECT id_usuarios, username FROM usuarios ORDER BY username ASC");
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Envío</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Agregar Nuevo Envío</h2>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form action="agregar_envio.php" method="POST">
            <label for="id_usuario">Seleccionar Usuario:</label>
            <select id="id_usuario" name="id_usuario" required>
                <?php while($row = $usuarios_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['id_usuarios']); ?>">
                        <?php echo htmlspecialchars($row['username']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <label for="destino">Destino:</label>
            <input type="text" id="destino" name="destino" required>
            
            <label for="estado">Estado:</label>
            <select id="estado" name="estado">
                <option value="Pendiente">Pendiente</option>
                <option value="Enviado">Enviado</option>
                <option value="Entregado">Entregado</option>
            </select>
            
            <button type="submit">Agregar Envío</button>
        </form>

        <div class="nav-links">
            <a href="gestion_envios.php">Volver a Gestión de Envíos</a>
        </div>
    </div>
</body>
</html>