<?php
// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar los encabezados de respuesta para JSON y CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir el archivo de conexión a la base de datos
require_once 'includes/conexion.php';

// Función para enviar una respuesta JSON
function sendResponse($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit();
}

// Verificar si se ha recibido la acción
if (!isset($_REQUEST['action'])) {
    sendResponse('error', 'Petición no válida.');
}

$action = $_REQUEST['action'];

try {
    // Lógica para manejar las diferentes acciones
    switch ($action) {
        case 'login':
            // Asegúrate de que los campos necesarios están presentes
            if (!isset($_POST['nombre_usuario']) || !isset($_POST['password'])) {
                sendResponse('error', 'Faltan datos para el login.');
            }

            $nombre_usuario = $_POST['nombre_usuario'];
            $password = $_POST['password'];

            // Se asume que la tabla de usuarios se llama 'usuarios'
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? AND password = ?");
            $stmt->bind_param("ss", $nombre_usuario, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                sendResponse('success', '¡Login exitoso!', ['user_id' => $user['id']]);
            } else {
                sendResponse('error', 'Usuario o contraseña incorrectos.');
            }
            $stmt->close();
            break;

        case 'get_pedidos':
            // Asegúrate de que el id del usuario está presente
            if (!isset($_GET['id_usuario'])) {
                sendResponse('error', 'Falta el ID del usuario.');
            }

            $id_usuario = $_GET['id_usuario'];

            // Se asume que la tabla de pedidos tiene una columna 'id_usuario'
            $stmt = $conn->prepare("SELECT * FROM pedidos WHERE id_usuario = ? AND estado = 'pendiente'");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $result = $stmt->get_result();

            $pedidos = [];
            while ($row = $result->fetch_assoc()) {
                $pedidos[] = $row;
            }
            sendResponse('success', 'Pedidos cargados correctamente.', $pedidos);
            $stmt->close();
            break;

        case 'marcar_entregado':
            // Asegúrate de que el id del pedido y el id del usuario están presentes
            if (!isset($_POST['id_pedido']) || !isset($_POST['id_usuario'])) {
                sendResponse('error', 'Faltan datos para marcar el pedido.');
            }

            $id_pedido = $_POST['id_pedido'];
            $id_usuario = $_POST['id_usuario'];
            
            // Verificación de seguridad: solo el usuario asignado puede marcar como entregado
            $stmt = $conn->prepare("UPDATE pedidos SET estado = 'entregado' WHERE id = ? AND id_usuario = ?");
            $stmt->bind_param("ii", $id_pedido, $id_usuario);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    sendResponse('success', 'Pedido marcado como entregado.');
                } else {
                    sendResponse('error', 'El pedido no se encontró o ya estaba entregado, o no tienes permiso para modificarlo.');
                }
            } else {
                sendResponse('error', 'Error al actualizar el pedido: ' . $stmt->error);
            }
            $stmt->close();
            break;

        default:
            sendResponse('error', 'Acción no reconocida.');
            break;
    }
} catch (Exception $e) {
    // Capturar y mostrar errores de la base de datos
    sendResponse('error', 'Error en el servidor: ' . $e->getMessage());
}

$conn->close();
?>
