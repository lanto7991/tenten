<?php
// Archivo: imprimir_etiqueta.php
// Página para generar una etiqueta de envío para imprimir.

include_once 'includes/conexion.php';
check_admin_access();

if (!isset($_GET['id'])) {
    // Si no se proporciona un ID, redirige al panel de envíos.
    header("Location: ver_envios_admin.php");
    exit();
}

$id_envio = $_GET['id'];

// Obtener los datos del envío, el usuario y el item
$sql_envio = "SELECT 
    e.id_envio,
    e.direccion_envio,
    e.nombre_destinatario,
    e.estado,
    e.fecha_ingreso,
    e.asignado_a,
    i.nombre_item,
    i.descripcion,
    u.id_usuarios,
    u.username,
    u.telefono
FROM envios e
JOIN items i ON e.id_item = i.id_item
JOIN usuarios u ON e.id_usuario = u.id_usuarios
WHERE e.id_envio = ?";

$stmt = $conn->prepare($sql_envio);
$stmt->bind_param("i", $id_envio);
$stmt->execute();
$envio = $stmt->get_result()->fetch_assoc();

$stmt->close();
$conn->close();

if (!$envio) {
    echo "Envío no encontrado.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta de Envío #<?php echo htmlspecialchars($envio['id_envio']); ?></title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 20px; background: #fff; color: #000; }
        .etiqueta {
            width: 80mm; /* Tamaño de etiqueta estándar */
            height: 100mm;
            border: 2px solid #000;
            padding: 10px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 10px; }
        .logo { max-width: 50px; max-height: 50px; }
        .header h1 { margin: 0; font-size: 14px; }
        .section { margin-bottom: 10px; }
        .section h2 { font-size: 12px; margin: 0; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .section p { margin: 5px 0; font-size: 11px; }
        .strong { font-weight: bold; }
        @media print {
            body { background: none; }
            .etiqueta { border: none; }
        }
    </style>
</head>
<body>
    <div class="etiqueta">
        <div class="header">
            <!-- Logo de la empresa - Reemplaza con la ruta de tu logo -->
            <img src="img/tenten_logo.png" alt="Logo de la Empresa" class="logo">
            <h1>Etiqueta de Envío</h1>
        </div>
        
        <div class="section">
            <h2>Destinatario</h2>
            <p><span class="strong">Nombre:</span> <?php echo htmlspecialchars($envio['nombre_destinatario']); ?></p>
            <p><span class="strong">Dirección:</span> <?php echo htmlspecialchars($envio['direccion_envio']); ?></p>
        </div>
        
        <div class="section">
            <h2>Descripción del Envío</h2>
            <p><span class="strong">Producto:</span> <?php echo htmlspecialchars($envio['nombre_item']); ?></p>
            <p><span class="strong">Descripción:</span> <?php echo htmlspecialchars($envio['descripcion'] ?: 'No disponible'); ?></p>
        </div>
    </div>

    <!-- Script para abrir la ventana de impresión automáticamente -->
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
