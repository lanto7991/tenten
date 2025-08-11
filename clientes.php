<?php
// Archivo: clientes.php
// Muestra la lista de clientes de la base de datos.
include_once 'includes/conexion.php';
check_admin_access();

// Obtener todos los clientes de la tabla "clientes"
$sql_clientes = "SELECT id_cliente, nombre_cliente, apellido_cliente, dni_cliente, anio_cliente FROM clientes";
$result_clientes = $conn->query($sql_clientes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h2>Gestión de Clientes</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>DNI</th>
            <th>Año</th>
            <th>Acciones</th>
        </tr>
        <?php
        if ($result_clientes->num_rows > 0) {
            while($row = $result_clientes->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["id_cliente"] . "</td>";
                echo "<td>" . $row["nombre_cliente"] . "</td>";
                echo "<td>" . $row["apellido_cliente"] . "</td>";
                echo "<td>" . $row["dni_cliente"] . "</td>";
                echo "<td>" . $row["anio_cliente"] . "</td>";
                echo "<td>";
                echo "<a href='editar_cliente.php?id=" . $row["id_cliente"] . "'>Editar</a> | ";
                echo "<a href='eliminar_cliente.php?id=" . $row["id_cliente"] . "'>Eliminar</a>";
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No hay clientes registrados</td></tr>";
        }
        $conn->close();
        ?>
    </table>

    <br>
    <a href="admin.php">Volver al Panel de Administración</a>
</body>
</html>