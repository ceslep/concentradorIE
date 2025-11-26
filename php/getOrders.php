<?php
require_once "cors.php";


require_once "../datos_conexion.php";

$mysqli = new mysqli($host, $user, $pass, $database);
$mysqli->query("SET NAMES utf8");
$mysqli->set_charset('utf8');

// === Leer entrada ===
$raw_input = file_get_contents("php://input");
$datos = json_decode($raw_input, false);

if (json_last_error() !== JSON_ERROR_NONE || !is_object($datos)) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit();
}

// === Validar parámetros ===
$year  = filter_var($datos->year ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 2000, 'max_range' => 2100]]);
$nivel = filter_var($datos->nivel ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]);

if ($year === false || $nivel === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros inválidos: year y nivel deben ser enteros válidos (year: 2000-2100, nivel: 1-12)']);
    exit();
}

// === Conexión ===
$mysqli = new mysqli($host, $user, $pass, $database);
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit();
}
$mysqli->set_charset('utf8mb4');

// === Consulta segura con prepared statement ===
$sql = "
    SELECT DISTINCT abreviatura
    FROM porcentajes_area_colegio
    WHERE year = ? AND nivel = ?
    ORDER BY orden
";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    error_log("Error preparando consulta: " . $mysqli->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error interno al preparar la consulta']);
    exit();
}

$stmt->bind_param('ii', $year, $nivel);
$stmt->execute();
$result = $stmt->get_result();

$abreviaturas = [];
while ($row = $result->fetch_assoc()) {
    $abreviaturas[] = $row['abreviatura'];
}

// === Respuesta final ===
echo json_encode($abreviaturas, JSON_UNESCAPED_UNICODE);

$stmt->close();
$mysqli->close();