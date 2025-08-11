<?php
// Archivo: editar_usuario.php
// Esta página permite al administrador editar los datos de un usuario.

include_once 'includes/conexion.php';
check_user_logged_in();

// Asegura que solo los administradores puedan acceder a esta página.
if ($_SESSION['perfil'] !== 'administrador') {
    header("Location: panel_control.php");
    exit();
}

$message = "";
$user_data = null;

// Lógica para procesar la edición del usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_usuario'];
    $username = $_POST['username'];
    $pass_user = $_POST['pass_user'];
    $perfil = $_POST['perfil'];

    // Encriptar la contraseña si se ha proporcionado una nueva.
    if (!empty($pass_user)) {
        $hashed_password = password_hash($pass_user, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET username = ?, pass_user = ?, perfil = ? WHERE id_usuarios = ?");
        $stmt->bind_param("sssi", $username, $hashed_password, $perfil, $id);
    } else {
        $stmt = $conn->prepare("UPDATE usuarios SET username = ?, perfil = ? WHERE id_usuarios = ?");
        $stmt = $conn->prepare("UPDATE usuarios SET username = ?, perfil = ? WHERE id_usuarios = ?");
        $stmt->bind_param("ssi", $username, $perfil, $id);
    }

    if ($stmt->execute()) {
        $message = "Usuario actualizado correctamente.";
    } else {
        $message = "Error al actualizar el usuario: " . $conn->error;
    }
    $stmt->close();

    // Después de actualizar, redirigir al panel de administración para ver la lista.
    header("Location: admin.php");
    exit();
}

// Obtener los datos del usuario para llenar el formulario
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT id_usuarios, username, perfil FROM usuarios WHERE id_usuarios = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
    } else {
        // Si no se encuentra el usuario, redirigir.
        header("Location: admin.php");
        exit();
    }
    $stmt->close();
} else {
    // Si no se proporciona un ID, redirigir.
    header("Location: admin.php");
    exit();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <!-- CDN de Tailwind CSS -->
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #1f2937;
        }
        .container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        h2 {
            font-size: 2.25rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
        }
        form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        form input, form select {
            width: 100%;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
        }
        .password-container {
            position: relative;
        }
        .password-container input {
            padding-right: 4rem; /* Espacio para el botón del ojo */
        }
        .password-container .toggle-password {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            color: #6b7280;
        }
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            text-align: center;
        }
        .success {
            color: #065f46;
        }
        button[type="submit"] {
            width: 100%;
            padding: 0.75rem 1rem;
            background-color: #1d4ed8;
            color: white;
            font-weight: 700;
            border-radius: 0.5rem;
            transition: background-color 0.2s;
        }
        button[type="submit"]:hover {
            background-color: #1e40af;
        }
        .nav-links {
            margin-top: 1rem;
            text-align: center;
        }
        .nav-links a {
            color: #4b5563;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Editar Usuario: <?php echo htmlspecialchars($user_data['username']); ?></h2>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form action="editar_usuario.php" method="POST">
            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($user_data['id_usuarios']); ?>">
            
            <label for="username">Nombre de Usuario:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
            
            <label for="pass_user">Contraseña (dejar en blanco para no cambiar):</label>
            <div class="password-container">
                <input type="password" id="pass_user" name="pass_user">
                <button type="button" id="togglePassword" class="toggle-password">
                    <!-- Icono de ojo cerrado -->
                    <svg id="eye-closed-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-2.02m4.612-4.613a10.003 10.003 0 014.321-.968c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.781 4.567M12 12a3 3 0 100-6 3 3 0 000 6z" />
                    </svg>
                    <!-- Icono de ojo abierto (inicialmente oculto) -->
                    <svg id="eye-open-icon" class="h-6 w-6 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            
            <label for="perfil">Perfil:</label>
            <select id="perfil" name="perfil">
                <option value="usuario" <?php echo ($user_data['perfil'] === 'usuario') ? 'selected' : ''; ?>>Usuario</option>
                <option value="administrador" <?php echo ($user_data['perfil'] === 'administrador') ? 'selected' : ''; ?>>Administrador</option>
            </select>
            
            <button type="submit">Guardar Cambios</button>
        </form>

        <div class="nav-links">
            <a href="admin.php">Volver a Gestión de Usuarios</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const togglePasswordBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('pass_user');
            const eyeOpenIcon = document.getElementById('eye-open-icon');
            const eyeClosedIcon = document.getElementById('eye-closed-icon');

            if (togglePasswordBtn && passwordInput) {
                togglePasswordBtn.addEventListener('click', () => {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    // Alterna los íconos de ojo
                    eyeOpenIcon.classList.toggle('hidden');
                    eyeClosedIcon.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>