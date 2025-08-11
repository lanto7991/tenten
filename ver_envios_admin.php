<?php
// Archivo: ver_envios_admin.php
// Página de visualización y gestión de todos los envíos activos.
// Accesible solo por el administrador.

include_once 'includes/conexion.php';
// Verifica que el usuario haya iniciado sesión y tenga perfil de administrador
check_admin_access();

$message = "";
$error = "";

// Lógica para añadir, editar o eliminar un envío
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $id_envio = $_POST['id_envio'] ?? null;
    $id_usuario = $_POST['id_usuario'];
    $id_item = $_POST['id_item'];
    $cantidad = $_POST['cantidad'];
    $direccion_envio = $_POST['direccion_envio'];
    $nombre_destinatario = $_POST['nombre_destinatario'];
    $estado = $_POST['estado'];
    $asignado_a = $_POST['asignado_a'];

    // Si la acción es 'add', inserta un nuevo envío
    if ($action === 'add') {
        // Inicia una transacción para asegurar que ambos UPDATEs se ejecuten correctamente
        $conn->begin_transaction();

        try {
            // 1. Inserta el nuevo envío
            $stmt = $conn->prepare("INSERT INTO envios (id_usuario, id_item, cantidad, direccion_envio, nombre_destinatario, estado, asignado_a) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiissss", $id_usuario, $id_item, $cantidad, $direccion_envio, $nombre_destinatario, $estado, $asignado_a);
            $stmt->execute();
            $stmt->close();

            // 2. Descuenta el stock del item
            $stmt = $conn->prepare("UPDATE items SET stock = stock - ? WHERE id_item = ?");
            $stmt->bind_param("ii", $cantidad, $id_item);
            $stmt->execute();
            $stmt->close();

            // Si todo va bien, confirma los cambios
            $conn->commit();
            $message = "Envío añadido y stock descontado correctamente.";

        } catch (mysqli_sql_exception $exception) {
            // Si algo falla, deshace todos los cambios
            $conn->rollback();
            $error = "Error al añadir el envío y descontar stock: " . $exception->getMessage();
        }
    }
    
    // Si la acción es 'edit', actualiza un envío existente
    if ($action === 'edit' && $id_envio) {
        $conn->begin_transaction();

        try {
            // Obtener el estado y cantidad originales del envío antes de la actualización
            $stmt_old = $conn->prepare("SELECT estado, cantidad, id_item FROM envios WHERE id_envio = ?");
            $stmt_old->bind_param("i", $id_envio);
            $stmt_old->execute();
            $result_old = $stmt_old->get_result();
            $old_envio = $result_old->fetch_assoc();
            $stmt_old->close();

            $old_estado = $old_envio['estado'];
            $old_cantidad = $old_envio['cantidad'];
            $old_id_item = $old_envio['id_item'];

            // 1. Actualiza el envío
            $stmt_update = $conn->prepare("UPDATE envios SET id_usuario = ?, id_item = ?, cantidad = ?, direccion_envio = ?, nombre_destinatario = ?, estado = ?, asignado_a = ? WHERE id_envio = ?");
            $stmt_update->bind_param("iiissssi", $id_usuario, $id_item, $cantidad, $direccion_envio, $nombre_destinatario, $estado, $asignado_a, $id_envio);
            $stmt_update->execute();
            $stmt_update->close();

            // 2. Gestiona el stock si el estado cambia a 'Cancelado'
            if ($estado === 'Cancelado' && $old_estado !== 'Cancelado') {
                $stmt_stock_refund = $conn->prepare("UPDATE items SET stock = stock + ? WHERE id_item = ?");
                $stmt_stock_refund->bind_param("ii", $old_cantidad, $old_id_item);
                $stmt_stock_refund->execute();
                $stmt_stock_refund->close();
                $message = "Envío actualizado a Cancelado y stock devuelto.";
            } else {
                $message = "Envío editado correctamente.";
            }

            // Si todo va bien, confirma los cambios
            $conn->commit();

        } catch (mysqli_sql_exception $exception) {
            // Si algo falla, deshace todos los cambios
            $conn->rollback();
            $error = "Error al editar el envío: " . $exception->getMessage();
        }
    }
}

// Lógica para eliminar un envío
if (isset($_GET['delete_id'])) {
    $conn->begin_transaction();
    $id_envio = $_GET['delete_id'];
    
    try {
        // Obtener la cantidad y el item del envío a eliminar para devolver el stock
        $stmt_old = $conn->prepare("SELECT cantidad, id_item FROM envios WHERE id_envio = ?");
        $stmt_old->bind_param("i", $id_envio);
        $stmt_old->execute();
        $result_old = $stmt_old->get_result();
        $old_envio = $result_old->fetch_assoc();
        $stmt_old->close();

        if ($old_envio) {
            $cantidad_a_devolver = $old_envio['cantidad'];
            $id_item_a_devolver = $old_envio['id_item'];

            // Devolver el stock
            $stmt_stock_refund = $conn->prepare("UPDATE items SET stock = stock + ? WHERE id_item = ?");
            $stmt_stock_refund->bind_param("ii", $cantidad_a_devolver, $id_item_a_devolver);
            $stmt_stock_refund->execute();
            $stmt_stock_refund->close();
        }

        // Eliminar el envío
        $stmt_delete = $conn->prepare("DELETE FROM envios WHERE id_envio = ?");
        $stmt_delete->bind_param("i", $id_envio);
        $stmt_delete->execute();
        $stmt_delete->close();
        
        $conn->commit();
        $message = "Envío eliminado y stock devuelto correctamente.";
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $error = "Error al eliminar el envío: " . $exception->getMessage();
    }
}

// Obtener todos los envíos con los datos del usuario y el item que NO han sido entregados
$sql_envios = "SELECT 
    e.id_envio,
    e.cantidad,
    e.direccion_envio,
    e.nombre_destinatario,
    e.estado,
    e.fecha_ingreso,
    e.asignado_a,
    u.id_usuarios,
    u.username AS usuario_pedido,
    i.id_item,
    i.nombre_item
FROM envios e
JOIN usuarios u ON e.id_usuario = u.id_usuarios
JOIN items i ON e.id_item = i.id_item
WHERE e.estado <> 'Entregado'
ORDER BY e.fecha_ingreso DESC";
$envios_result = $conn->query($sql_envios);

// Obtener la lista de usuarios y items para el formulario de agregar/editar
$items_result = $conn->query("SELECT id_item, nombre_item, stock FROM items WHERE stock > 0 ORDER BY id_item ASC");
$usuarios_result = $conn->query("SELECT id_usuarios, username FROM usuarios");

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Envíos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Gestión de Envíos (Administrador)</h2>

        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Botón para generar hoja de ruta en Excel (CSV) -->
        <div style="text-align: center"> <a href="generar_hoja_ruta.php" class="button">Generar Hoja de Ruta</a> </div>

        <!-- Formulario para Añadir/Editar Envío -->
        <h3 style="margin-top: 40px;">Añadir / Editar Envío</h3>
        <form action="ver_envios_admin.php" method="POST">
            <input type="hidden" name="action" id="action-input" value="add">
            <input type="hidden" name="id_envio" id="id-envio-input">
            
            <label for="id_usuario">Usuario:</label>
            <select id="id_usuario" name="id_usuario" required>
                <option value="">-- Seleccione un usuario --</option>
                <?php $usuarios_result->data_seek(0); // Reinicia el puntero del resultado ?>
                <?php while($row = $usuarios_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['id_usuarios']); ?>"><?php echo htmlspecialchars($row['username']); ?></option>
                <?php endwhile; ?>
            </select>

            <label for="id_item">Item:</label>
            <select id="id_item" name="id_item" required>
                <option value="">-- Seleccione un item --</option>
                <?php $items_result->data_seek(0); // Reinicia el puntero del resultado ?>
                <?php while($row = $items_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['id_item']); ?>"><?php echo htmlspecialchars($row['id_item'] . ' - ' . $row['nombre_item'] . ' - ' . $row['stock'] . ' unidades'); ?></option>
                <?php endwhile; ?>
            </select>
            
            <label for="cantidad">Cantidad:</label>
            <input type="number" id="cantidad" name="cantidad" required>

            <label for="direccion_envio">Dirección:</label>
            <input type="text" id="direccion_envio" name="direccion_envio" required>
            
            <label for="nombre_destinatario">Destinatario:</label>
            <input type="text" id="nombre_destinatario" name="nombre_destinatario" required>
            
            <label for="estado">Estado:</label>
            <select id="estado" name="estado" required>
                <option value="Pendiente">Pendiente</option>
                <option value="En Proceso">En Proceso</option>
                <option value="Enviado">Enviado</option>
                <option value="Entregado">Entregado</option>
                <option value="Cancelado">Cancelado</option>
            </select>

            <label for="asignado_a">Asignado a:</label>
            <input type="text" id="asignado_a" name="asignado_a" placeholder="Nombre de la persona asignada">
            
            <button type="submit" id="submit-button">Añadir Envío</button>
        </form>
        
        <!-- Tabla de Envíos -->
        <h3 style="margin-top: 40px;">Pedidos Activos</h3>
        <?php if ($envios_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Item</th>
                        <th>Cantidad</th>
                        <th>Usuario</th>
                        <th>Asignado a</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $envios_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id_envio']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_item']); ?></td>
                            <td><?php echo htmlspecialchars($row['cantidad']); ?></td>
                            <td><a href="perfil.php?id=<?php echo htmlspecialchars($row['id_usuarios']); ?>"><?php echo htmlspecialchars($row['usuario_pedido']); ?></a></td>
                            <td><?php echo htmlspecialchars($row['asignado_a'] ?: 'No asignado'); ?></td>
                            <td><?php echo htmlspecialchars($row['estado']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_ingreso']); ?></td>
                            <td>
                                <a href="#" class="button-action" onclick="loadEnvioForEdit(<?php echo htmlspecialchars(json_encode($row)); ?>); return false;">Editar</a>
                                <a href="ver_envios_admin.php?delete_id=<?php echo htmlspecialchars($row['id_envio']); ?>" class="button-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar este envío? Esto devolverá el stock.');">Eliminar</a>
                                <a href="imprimir_etiqueta.php?id=<?php echo htmlspecialchars($row['id_envio']); ?>" target="_blank" class="button-action">Imprimir Etiqueta</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay envíos activos registrados.</p>
        <?php endif; ?>

        <div class="nav-links">
            <a href="panel_control.php">Volver al Panel</a>
        </div>
    </div>
    
    <script>
        function loadEnvioForEdit(envio) {
            document.getElementById('action-input').value = 'edit';
            document.getElementById('id-envio-input').value = envio.id_envio;
            document.getElementById('id_usuario').value = envio.id_usuarios;
            document.getElementById('id_item').value = envio.id_item;
            document.getElementById('cantidad').value = envio.cantidad;
            document.getElementById('direccion_envio').value = envio.direccion_envio;
            document.getElementById('nombre_destinatario').value = envio.nombre_destinatario;
            document.getElementById('estado').value = envio.estado;
            document.getElementById('asignado_a').value = envio.asignado_a;
            
            // Habilitar campos para edición
            document.getElementById('id_usuario').disabled = false;
            document.getElementById('id_item').disabled = false;
            document.getElementById('cantidad').disabled = false;
            document.getElementById('direccion_envio').disabled = false;
            document.getElementById('nombre_destinatario').disabled = false;

            document.getElementById('submit-button').innerText = 'Guardar Cambios';
        }
    </script>
</body>
</html>
