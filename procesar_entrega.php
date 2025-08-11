<?php
// Archivo: procesar_entrega.php
// Script para procesar la acción de marcar un envío como entregado.
// Recibe el ID del envío a través de la URL.

// Incluimos la conexión a la base de datos.
// Se asume que este archivo ya establece la conexión y la guarda en la variable $conn.
include_once 'includes/conexion.php';

// Verifica que el usuario tenga sesión de administrador.
check_admin_access();

// Inicia una variable para el mensaje de respuesta
$mensaje = '';
$tipo_mensaje = '';

// Verifica si se ha recibido un ID válido en la URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $mensaje = "Error: ID de envío no válido.";
    $tipo_mensaje = 'error';
} else {
    $id_envio = $_GET['id'];
    
    try {
        // Prepara la consulta para actualizar el estado y la fecha de entrega
        // Usamos la variable $conn que se creó en conexion.php
        $stmt = $conn->prepare("UPDATE envios SET estado = 'Entregado', fecha_entrega = NOW() WHERE id_envio = ?");
        $stmt->bind_param("i", $id_envio);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $mensaje = "El envío ID: $id_envio ha sido marcado como entregado.";
                $tipo_mensaje = 'success';
            } else {
                $mensaje = "Error: No se encontró el envío con ID: $id_envio o ya estaba marcado como entregado.";
                $tipo_mensaje = 'error';
            }
        } else {
            throw new Exception("Error al actualizar el estado del envío: " . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $mensaje = "Error del sistema: " . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// Cerramos la conexión después de usarla
if (isset($conn)) {
    $conn->close();
}

// Redirige al usuario de vuelta a la página de administración
header("Location: ver_envios_admin.php?mensaje=" . urlencode($mensaje) . "&tipo=" . urlencode($tipo_mensaje));
exit();

?>
