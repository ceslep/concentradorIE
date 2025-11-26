<?php
require_once 'cors.php';

// Conexi贸n a BD
require_once "../datos_conexion.php";

$mysqli = new mysqli($host, $user, $pass, $database);
$mysqli->query("SET NAMES utf8");
$mysqli->set_charset('utf8');


if ($mysqli->connect_error) {
    echo json_encode(["error" => "Error de conexión: " . $mysqli->connect_error]);
    exit();
}

// Leer año desde JSON o usar año actual si DEBUG_MODE está activo
$input = json_decode(file_get_contents("php://input"), true);
$year = $input['year'] ?? null;

if (DEBUG_MODE && !$year) {
    $year = (int)date("Y"); // Año actual para pruebas sin enviar datos
}

if (!$year || !is_numeric($year)) {
    echo json_encode(["error" => "El campo 'year' es obligatorio y debe ser un número."]);
    exit();
}

// Consulta SQL
$sql = "
    SELECT 
        s.sede AS sede,
        e.nivel AS nivel,
        e.numero AS numero,
        COUNT(e.estudiante) AS total_estudiantes
    FROM estugrupos e
    INNER JOIN sedes s ON e.asignacion = s.ind
    WHERE e.year = ? AND e.activo = 'S'
    GROUP BY s.sede, e.nivel, e.numero
    ORDER BY s.ind,s.sede, e.nivel, e.numero
";

// Depuración: mostrar consulta si DEBUG_MODE está activo
if (DEBUG_MODE) {
    // Reemplazar ? por valor real solo para mostrar (no para ejecutar)
    $debug_sql = str_replace("?", "'" . $mysqli->real_escape_string($year) . "'", $sql);
    error_log("Consulta SQL (DEBUG): " . $debug_sql);
    // Opcional: imprimir en salida (solo si no interfieres con JSON)
    // echo "/* DEBUG: " . $debug_sql . " */\n";
}

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Error en la preparación de la consulta: " . $mysqli->error]);
    exit();
}

$stmt->bind_param("i", $year);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

$stmt->close();
$mysqli->close();
?>