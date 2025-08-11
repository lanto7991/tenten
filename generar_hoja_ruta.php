<?php
// Archivo: generar_hoja_ruta.php
// Script para generar un archivo CSV con los datos de los envíos.

// Para depuración, puedes descomentar las siguientes líneas
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once 'includes/conexion.php';
// Se asume que esta función valida si el usuario tiene permiso para generar el archivo
check_admin_access();

// Nombre del archivo de descarga
$filename = "hoja_de_ruta_" . date('Y-m-d') . ".csv";

// Encabezados HTTP para forzar la descarga de un archivo CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Abre el archivo de salida para escribir en él
$output = fopen('php://output', 'w');

// Escribe los encabezados de las columnas del CSV
fputcsv($output, ['ID_Envio', 'Destinatario', 'Direccion', 'Item', 'Estado', 'Asignado_a', 'Fecha_Ingreso']);

// Define la consulta SQL para obtener los datos de los envíos
$sql_envios = "SELECT 
    e.id_envio,
    e.nombre_destinatario,
    e.direccion_envio,
    i.nombre_item,
    e.estado,
    e.asignado_a,
    e.fecha_ingreso
FROM envios e
JOIN items i ON e.id_item = i.id_item
ORDER BY e.fecha_ingreso DESC";

// Ejecuta la consulta
$envios_result = $conn->query($sql_envios);

// Verifica si la consulta fue exitosa
if ($envios_result) {
    // Escribe cada fila de la base de datos en el archivo CSV
    while ($row = $envios_result->fetch_assoc()) {
        fputcsv($output, $row);
    }
} else {
    // Manejo de errores si la consulta falla
    // En un entorno de producción, es mejor registrar este error en un log
    fputcsv($output, ['Error: ' . $conn->error]);
}

// Cierra el archivo de salida
fclose($output);

// Cierra la conexión a la base de datos
$conn->close();

exit(); // Asegura que no se imprima nada más después del archivo
?>