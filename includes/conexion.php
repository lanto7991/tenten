<?php
// Archivo: includes/conexion.php
// Conexión a la base de datos "TenTen".

// Inicia la sesión solo si no hay una sesión activa.
// Esto previene el error "session_start(): Ignoring session_start() because a session is already active".
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Por favor, asegúrate de que el campo `pass_user` en la tabla `usuarios` sea de tipo VARCHAR(255)
// para poder almacenar contraseñas encriptadas de forma segura.
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "TenTen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

function check_admin_access() {
    if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'administrador') {
        header("Location: login.php");
        exit();
    }
}

function check_user_logged_in() {
    if (!isset($_SESSION['perfil'])) {
        header("Location: login.php");
        exit();
    }
}
?>