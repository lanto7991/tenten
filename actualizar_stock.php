<?php
// Archivo: actualizar_stock.php
// Página dedicada exclusivamente a la actualización del stock de items.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'includes/conexion.php';

// Verifica que el usuario sea administrador
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'administrador') {
    header("Location: panel_control.php");
    exit();
}

$message = "";

// Lógica para actualizar el stock de un item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_stock') {
    $id = $_POST['id_item'];
    $nuevo_stock = $_POST['stock'];

    // Validación de datos
    if (!is_numeric($id) || !is_numeric($nuevo_stock) || $nuevo_stock < 0) {
        $message = "Error: Los datos del formulario no son válidos.";
    } else {
        $stmt = $conn->prepare("UPDATE items SET stock = ? WHERE id_item = ?");
        $stmt->bind_param("ii", $nuevo_stock, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Stock del item actualizado correctamente.";
            header("Location: actualizar_stock.php"); // Redirección clave para evitar el problema de recarga
            exit();
        } else {
            $message = "Error al actualizar el stock: " . $conn->error;
        }
        $stmt->close();
    }
}

// Manejo de mensajes de sesión
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Obtener todos los items para mostrarlos
$items_result = $conn->query("SELECT id_item, nombre_item, precio, stock FROM items ORDER BY id_item DESC");
if (!$items_result) {
    die("Error en la consulta: " . $conn->error);
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Stock</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .out-of-stock {
            background-color: #f2f2f2;
            color: #888;
        }
        .out-of-stock a {
            color: #888;
        }
        .stock-form {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .stock-form input[type="number"] {
            width: 70px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Actualización de Stock</h2>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <h3>Items Existentes</h3>
        <?php if ($items_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Stock Actual</th>
                        <th>Modificar Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $items_result->fetch_assoc()): ?>
                        <tr class="<?php echo ($row['stock'] == 0) ? 'out-of-stock' : ''; ?>">
                            <td><?php echo htmlspecialchars($row['id_item']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_item']); ?></td>
                            <td>$<?php echo htmlspecialchars($row['precio']); ?></td>
                            <td><?php echo htmlspecialchars($row['stock']); ?></td>
                            <td>
                                <!-- Formulario para actualizar stock de cada item -->
                                <form action="actualizar_stock.php" method="POST" class="stock-form">
                                    <input type="hidden" name="action" value="update_stock">
                                    <input type="hidden" name="id_item" value="<?php echo htmlspecialchars($row['id_item']); ?>">
                                    <input type="number" name="stock" value="<?php echo htmlspecialchars($row['stock']); ?>" min="0" required>
                                    <button type="submit">Actualizar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay items cargados.</p>
        <?php endif; ?>

        <div class="nav-links">
            <a href="panel_control.php">Volver al Panel</a>
        </div>
    </div>
</body>
</html>
