<?php
// Archivo: envios.php
// Página para que el usuario gestione sus envíos.

include_once 'includes/conexion.php';
check_user_logged_in();

// Si el usuario es un administrador, lo redirigimos a la página de gestión de envíos.
// Esto asegura que cada perfil tenga su propia funcionalidad dedicada.
if ($_SESSION['perfil'] === 'administrador') {
    header("Location: gestion_envios.php");
    exit();
}

$message = "";
$user_id = $_SESSION['id_usuarios'];

// Lógica para registrar un nuevo envío
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destino = $_POST['destino'];
    $estado = 'Pendiente';

    // Inserta los datos del envío usando la conexión ya establecida
    $stmt = $conn->prepare("INSERT INTO envios (id_usuario, destino, estado) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $destino, $estado);

    if ($stmt->execute()) {
        $message = "Envío registrado correctamente.";
    } else {
        $message = "Error al registrar el envío: " . $conn->error;
    }
    $stmt->close();
}

// Obtener los envíos del usuario para mostrarlos
$envios_result = $conn->prepare("SELECT id_envio, destino, estado, fecha_envio FROM envios WHERE id_usuario = ? ORDER BY fecha_envio DESC");
$envios_result->bind_param("i", $user_id);
$envios_result->execute();
$envios = $envios_result->get_result();

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
        <h2>Gestionar Envíos</h2>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <h3>Registrar un Nuevo Envío</h3>
        <form action="envios.php" method="POST">
            <label for="destino">Destino del Envío:</label>
            <input type="text" id="destino" name="destino" required>
            
            <button type="submit">Registrar Envío</button>
        </form>

        <h3 style="margin-top: 40px;">Historial de Envíos</h3>
        <?php if ($envios->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Envío</th>
                        <th>Destino</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $envios->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id_envio']); ?></td>
                            <td><?php echo htmlspecialchars($row['destino']); ?></td>
                            <td><?php echo htmlspecialchars($row['estado']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_envio']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No has registrado ningún envío.</p>
        <?php endif; ?>

        <div class="nav-links">
            <a href="panel_control.php">Volver al Panel</a>
        </div>
    </div>
</body>
</html>
