<?php
require_once 'cors.php';

// Conexi贸n a BD
require_once "../datos_conexion.php";




// === Leer cuerpo JSON ===
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Cuerpo no es JSON v�lido']);
    exit;
}

$nivel = $input['nivel'] ?? null;
$numero = $input['numero'] ?? null;
$asignacion = $input['Asignacion'] ?? null; // Nota: frontend env�a "Asignacion" con may�scula
$year = $input['year'] ?? null;

// Validar par�metros
if ($nivel === null || $numero === null || $asignacion === null || $year === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan par�metros: nivel, numero, Asignacion, year']);
    exit;
}

if (!is_numeric($year)) {
    http_response_code(400);
    echo json_encode(['error' => 'El a�o debe ser num�rico']);
    exit;
}
$year = (int)$year;

// === Conexi�n con MySQLi ===
require_once "../datos_conexion.php"; // Debe definir: $host, $user, $pass, $db

$conexion = new mysqli($host, $user, $pass, $database);

if ($conexion->connect_error) {
    error_log("Error de conexi�n MySQLi: " . $conexion->connect_error);
    http_response_code(500);
    echo json_encode(['error' => 'Error al conectar con la base de datos']);
    exit;
}

$conexion->set_charset("utf8mb4");

// === 1. Consulta de �reas ===
$sqlAreas = "
    SELECT 
        pac.abreviatura AS asignatura,
        pac.abreviatura,
        aa.docente,
        pac.abreviatura AS materia,
        IF(pi.orden IS NULL, 200, pi.orden) AS ordenar
    FROM porcentajes_area_colegio pac
    LEFT JOIN asignacion_asignaturas aa 
        ON pac.asignatura = aa.asignatura 
        AND pac.year = aa.year
    LEFT JOIN parametros_informe pi 
        ON aa.asignatura = pi.codigo_materia
    INNER JOIN docentes d 
        ON aa.docente = d.identificacion
    WHERE 
        aa.nivel = ? 
        AND aa.numero = ? 
        AND aa.visible = 'S' 
        AND aa.year = ?
        AND pi.year = ?
    GROUP BY pac.area
    ORDER BY ordenar
";

$stmtAreas = $conexion->prepare($sqlAreas);
if (!$stmtAreas) {
    error_log("Error en prepare (�reas): " . $conexion->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar la consulta de �reas']);
    $conexion->close();
    exit;
}

$stmtAreas->bind_param("sssi", $nivel, $numero, $year, $year);

if (!$stmtAreas->execute()) {
    error_log("Error en execute (�reas): " . $stmtAreas->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error al ejecutar la consulta de �reas']);
    $stmtAreas->close();
    $conexion->close();
    exit;
}

$resultAreas = $stmtAreas->get_result();
//echo json_encode($resultAreas->fetch_all(MYSQLI_ASSOC));exit(0);
$areas = [];
while ($row = $resultAreas->fetch_assoc()) {
    $areas[] = $row;
}
$stmtAreas->close();

// === 2. Consulta de estudiantes ===
$sqlEstudiantes = "
    SELECT 
        estudiante,
        nombres,
        nivel,
        numero,
        asignacion,
        year,
        activo
    
    FROM estugrupos
    WHERE 
        year = ? 
        AND asignacion = ? 
        AND nivel = ? 
        AND numero = ?
        AND activo = 'S'
    ORDER BY nombres
";

$stmtEstudiantes = $conexion->prepare($sqlEstudiantes);
if (!$stmtEstudiantes) {
    error_log("Error en prepare (estudiantes): " . $conexion->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar la consulta de estudiantes']);
    $conexion->close();
    exit;
}

// Nota: `asignacion` en `estugrupos` es INT(1), as� que lo convertimos
$asignacionInt = (int)$asignacion;

$stmtEstudiantes->bind_param("iiii", $year, $asignacionInt, $nivel, $numero);

if (!$stmtEstudiantes->execute()) {
    error_log("Error en execute (estudiantes): " . $stmtEstudiantes->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error al ejecutar la consulta de estudiantes']);
    $stmtEstudiantes->close();
    $conexion->close();
    exit;
}

$resultEstudiantes = $stmtEstudiantes->get_result();

$estudiantes = [];
while ($row = $resultEstudiantes->fetch_assoc()) {
    $estudiantes[] = $row;
}
$stmtEstudiantes->close();

$conexion->close();

// === Respuesta final ===
echo json_encode([
    'areas' => $areas,
    'estudiantes' => $estudiantes
], JSON_UNESCAPED_UNICODE);
?>