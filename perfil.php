<?php
// Archivo: perfil.php
// Página de perfil de usuario con edición y visualización de envíos.

// Iniciar la sesión al principio
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión y la función de verificación (si existe)
include_once 'includes/conexion.php';

// Verificación de sesión: redirige si el usuario no ha iniciado sesión
if (!isset($_SESSION['id_usuarios'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id_usuarios'];
$message = "";

// Lógica para procesar la actualización del perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si se subió una foto de perfil
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/perfiles/';
        $file_name = uniqid('profile_') . '_' . basename($_FILES['foto_perfil']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $target_file)) {
            // Actualizar la ruta de la foto de perfil en la base de datos
            $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id_usuarios = ?");
            $stmt->bind_param("si", $target_file, $user_id);
            $stmt->execute();
            $stmt->close();
            $message = "Foto de perfil actualizada correctamente.";
        } else {
            $message = "Error al subir la foto de perfil.";
        }
    }

    // Si se subió una foto de portada
    if (isset($_FILES['foto_portada']) && $_FILES['foto_portada']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/portadas/';
        $file_name = uniqid('cover_') . '_' . basename($_FILES['foto_portada']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['foto_portada']['tmp_name'], $target_file)) {
            // Actualizar la ruta de la foto de portada en la base de datos
            $stmt = $conn->prepare("UPDATE usuarios SET foto_portada = ? WHERE id_usuarios = ?");
            $stmt->bind_param("si", $target_file, $user_id);
            $stmt->execute();
            $stmt->close();
            $message = "Foto de portada actualizada correctamente.";
        } else {
            $message = "Error al subir la foto de portada.";
        }
    }

    // Si se actualizó el correo o el teléfono
    if (isset($_POST['mail']) || isset($_POST['telefono'])) {
        $mail = $_POST['mail'] ?? null;
        $telefono = $_POST['telefono'] ?? null;

        $stmt = $conn->prepare("UPDATE usuarios SET mail = ?, telefono = ? WHERE id_usuarios = ?");
        $stmt->bind_param("ssi", $mail, $telefono, $user_id);
        $stmt->execute();
        $stmt->close();
        $message = "Datos de perfil actualizados correctamente.";
    }
}

// Obtener los datos completos del usuario
$stmt = $conn->prepare("SELECT username, perfil, mail, telefono, foto_perfil, foto_portada FROM usuarios WHERE id_usuarios = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

if (!$user_data) {
    header("Location: panel_control.php");
    exit();
}

$es_usuario = (strtolower($user_data['perfil']) === 'usuario');

// Obtener los envíos del usuario si el perfil es 'usuario'
$envios_result = null;
if ($es_usuario) {

    $sql_envios = "SELECT
                        e.id_envio,
                        e.estado,
                        e.fecha_envio,
                        p.destino
                    FROM envios e
                    JOIN pedidos p ON e.id_pedido = p.id_pedido
                    WHERE e.id_usuario = ?
                    ORDER BY e.fecha_envio DESC";

    $envios_result = $conn->prepare($sql_envios);
    $envios_result->bind_param("i", $user_id);
    $envios_result->execute();
    $envios = $envios_result->get_result();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos específicos para este perfil */
        .profile-container {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            background: #1a1a2e;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0, 240, 255, 0.2);
            padding: 0;
            overflow: hidden;
        }

        .profile-header {
            position: relative;
            text-align: center;
        }

        .cover-photo-container {
            position: relative;
            height: 250px;
            background-size: cover;
            background-position: center;
        }
        .cover-photo-container form {
            position: absolute;
            bottom: 10px;
            right: 10px;
        }
        .profile-photo-container {
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translate(-50%, 50%);
            width: 150px;
            height: 150px;
        }
        .profile-photo-container img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 5px solid #1a1a2e;
            object-fit: cover;
            box-shadow: 0 0 10px rgba(0, 240, 255, 0.4);
        }
        .profile-photo-container form {
            position: absolute;
            bottom: 0;
            right: 0;
        }

        .profile-details {
            padding-top: 90px;
            padding-bottom: 20px;
            text-align: center;
        }
        .profile-details h2 {
            margin: 0;
            color: #f0f0f5;
        }
        .profile-details p {
            margin: 5px 0 0;
            color: #a3a3a3;
            font-size: 1em;
        }
        
        .profile-content {
            padding: 20px 40px 40px;
        }
        .profile-content h3 {
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
            margin-top: 40px;
        }
        .profile-content form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .profile-content .edit-form {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        .profile-content .edit-form input {
            flex-grow: 1;
        }

        .button-file {
            background: #444;
            color: #fff;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        @media (max-width: 600px) {
            .profile-container {
                margin: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <div class="profile-header">
            <!-- Foto de portada -->
            <div class="cover-photo-container" style="background-image: url('<?php echo htmlspecialchars($user_data['foto_portada'] ?: 'https://placehold.co/800x250/1a1a2e/a3a3a3?text=Portada'); ?>');">
                <form action="perfil.php" method="POST" enctype="multipart/form-data">
                    <label for="foto_portada" class="button-file">Cambiar Portada</label>
                    <input type="file" name="foto_portada" id="foto_portada" onchange="this.form.submit();" style="display: none;">
                </form>
            </div>
            
            <!-- Foto de perfil -->
            <div class="profile-photo-container">
                <img src="<?php echo htmlspecialchars($user_data['foto_perfil'] ?: 'https://placehold.co/150x150/00f0ff/1a1a2e?text=Foto'); ?>" alt="Foto de Perfil">
                <form action="perfil.php" method="POST" enctype="multipart/form-data">
                    <label for="foto_perfil" class="button-file">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" name="foto_perfil" id="foto_perfil" onchange="this.form.submit();" style="display: none;">
                </form>
            </div>
        </div>

        <!-- Información del perfil -->
        <div class="profile-details">
            <h2><?php echo htmlspecialchars($user_data['username']); ?></h2>
            <p><strong>Perfil:</strong> <?php echo htmlspecialchars($user_data['perfil']); ?></p>
        </div>

        <div class="profile-content">
            <!-- Formulario de edición -->
            <h3>Datos de Contacto</h3>
            <form action="perfil.php" method="POST">
                <label for="mail">Correo Electrónico:</label>
                <div class="edit-form">
                    <input type="text" id="mail" name="mail" value="<?php echo htmlspecialchars($user_data['mail'] ?? ''); ?>">
                    <button type="submit">Guardar</button>
                </div>
                
                <label for="telefono">Teléfono:</label>
                <div class="edit-form">
                    <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($user_data['telefono'] ?? ''); ?>">
                    <button type="submit">Guardar</button>
                </div>
            </form>
        </div>

        <!-- Tabla de envíos, solo si el perfil es 'usuario' -->
        <?php if ($es_usuario && $envios->num_rows > 0): ?>
            <div class="profile-content">
                <h3>Mis Envíos</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID Envío</th>
                            <th>Destino</th>
                            <th>Estado</th>
                            <th>Fecha de Envío</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($envio = $envios->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($envio['id_envio']); ?></td>
                                <td><?php echo htmlspecialchars($envio['destino']); ?></td>
                                <td><?php echo htmlspecialchars($envio['estado']); ?></td>
                                <td><?php echo htmlspecialchars($envio['fecha_envio']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($es_usuario): ?>
            <div class="profile-content">
                <h3>Mis Envíos</h3>
                <p>No tienes envíos registrados.</p>
            </div>
        <?php endif; ?>

        <div class="nav-links">
            <a href="panel_control.php">Volver al Panel</a>
        </div>
    </div>
</body>
</html>
�