<?php
// Archivo: registro.php
// Esta página permite a los usuarios registrarse en el sistema.
// Un administrador también puede usar esta página para registrar nuevos usuarios con diferentes perfiles.

include_once 'includes/conexion.php';

// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = "";

// Lógica para procesar el formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $pass_user = $_POST['pass_user'];
    $perfil = $_POST['perfil'];

    // Validar si el usuario ya existe
    $stmt_check = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE username = ?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $message = "El nombre de usuario ya existe. Por favor, elige otro.";
    } else {
        // Hashear la contraseña antes de guardarla en la base de datos
        $hashed_password = password_hash($pass_user, PASSWORD_DEFAULT);

        $stmt_insert = $conn->prepare("INSERT INTO usuarios (username, pass_user, perfil) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("sss", $username, $hashed_password, $perfil);

        if ($stmt_insert->execute()) {
            $message = "Usuario registrado correctamente.";
        } else {
            $message = "Error al registrar el usuario: " . $conn->error;
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <!-- Estilos en línea para asegurar que el formulario sea visible y funcional -->
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 400px; margin: 50px auto; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; color: #333; }
        form div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"], select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #5cb85c; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #4cae4c; }
        .message { padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registro de Usuario</h2>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'error') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="registro.php" method="POST">
            <div>
                <label for="username">Nombre de Usuario:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="pass_user">Contraseña:</label>
                <input type="password" id="pass_user" name="pass_user" required>
            </div>
            <div>
                <label for="perfil">Perfil:</label>
                <select id="perfil" name="perfil" required>
                    <option value="usuario">Usuario</option>
                    <option value="administrador">Administrador</option>
                </select>
            </div>
            <button type="submit">Registrar</button>
        </form>
        <p style="text-align: center; margin-top: 20px;"><a href="login.php">Volver al inicio de sesión</a></p>
    </div>
</body>
</html>