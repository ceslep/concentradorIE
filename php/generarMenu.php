<?php
require_once("cors.php");
require_once("Database.php");

// Establecer conexión con la base de datos
$db = Database::getInstance();
$mysqli = $db->getConnection();

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Leer y decodificar entrada JSON
$input = file_get_contents("php://input");
$datos = json_decode($input);

// Validar que los datos JSON sean válidos
if ($datos === null && $input !== '') {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

$docente = isset($datos->docente) ? $datos->docente : null;
$year = isset($datos->year) ? $datos->year : date('Y');

if (!$docente) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros: se requiere "docente"']);
    exit;
}

// 1. Obtener grados del docente
$sqlGrados = "SELECT DISTINCT nivel, numero, grados FROM asignacion_asignaturas 
              WHERE docente = ? AND year = ? 
              ORDER BY nivel, numero";

$stmtGrados = $mysqli->prepare($sqlGrados);
if (!$stmtGrados) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar consulta de grados']);
    exit;
}

$stmtGrados->bind_param('ss', $docente, $year);
$stmtGrados->execute();
$resultGrados = $stmtGrados->get_result();

$data = [];

// 2. Preparar consulta de asignaturas (una sola vez por eficiencia)
$sqlAsignaturas = "SELECT a.asignatura 
                   FROM asignacion_asignaturas a
                   INNER JOIN orden_asignaturas o ON o.asignatura = a.asignatura
                   WHERE a.docente = ? AND a.nivel = ? AND a.numero = ? AND a.year = ?
                   ORDER BY o.orden";

$stmtAsignaturas = $mysqli->prepare($sqlAsignaturas);
if (!$stmtAsignaturas) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar consulta de asignaturas']);
    exit;
}

while ($grado = $resultGrados->fetch_assoc()) {
    $nivel = $grado['nivel'];
    $numero = $grado['numero'];
    $gradoA = $grado['grados'];
    
    $stmtAsignaturas->bind_param('ssss', $docente, $nivel, $numero, $year);
    $stmtAsignaturas->execute();
    $resAsig = $stmtAsignaturas->get_result();
    
    $asignaturas = [];
    while ($asignatura = $resAsig->fetch_assoc()) {
        $asignaturas[] = $asignatura;
    }
    
    $data[] = [
        "grado" => $nivel . "-" . $numero,
        "nivel" => $nivel,
        "numero" => $numero,
        "gradoA" => $gradoA,
        "asignaturas" => $asignaturas
    ];
}

echo json_encode($data);

$stmtGrados->close();
$stmtAsignaturas->close();