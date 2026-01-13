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

// Validar entrada JSON
if ($datos === null && $input !== '') {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

// Parámetros por defecto
$periodoReq = isset($datos->periodo) ? $datos->periodo : "-";
$x = isset($datos->x) ? $datos->x : null;

// 1. Obtener lista de docentes
$sqlDocentes = "SELECT identificacion, nombres, sedes.sede, asignacion 
                FROM docentes
                INNER JOIN sedes ON docentes.asignacion = sedes.ind
                WHERE activo = 'S' AND nombres NOT LIKE '%COORDI%'
                ORDER BY asignacion, nombres";

$resDocentes = $mysqli->query($sqlDocentes);

if (!$resDocentes) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener docentes']);
    exit;
}

// Si el periodo es "-", devolvemos solo la lista de docentes
if ($periodoReq === "-") {
    $data = $resDocentes->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);
    $resDocentes->free();
    exit;
}

// 2. Determinar el periodo si es automático (aunque se pase explícitamente, validamos lógica previa)
$periodo = $periodoReq;
if ($periodo === "-") { // Doble chequeo por seguridad según lógica original
    $sqlPeriodo = "SELECT nombre FROM periodos WHERE CURDATE() BETWEEN fechainicial AND fechafinal";
    $resP = $mysqli->query($sqlPeriodo);
    if ($resP && $rowP = $resP->fetch_assoc()) {
        $periodo = $rowP['nombre'];
    }
}

// 3. Preparar funciones para columnas (notas y aspectos)
function getColumnas($prefix, $count) {
    $cols = [];
    for ($i = 1; $i <= $count; $i++) {
        $cols[] = $prefix . $i;
    }
    return implode(',', $cols);
}

$colsNotas = getColumnas("nota", 12);
$colsAspectos = getColumnas("aspecto", 12);

// 4. Preparar consulta de notas (una sola vez por eficiencia)
$sqlNotas = "SELECT docente, asignatura, CONCAT_WS('-', nivel, numero) AS grado, nivel, numero, $colsNotas, $colsAspectos 
             FROM notas
             LEFT JOIN estugrupos ON notas.estudiante = estugrupos.estudiante
             WHERE docente = ? 
             AND estugrupos.year = YEAR(CURDATE())
             AND (notas.year = YEAR(CURDATE()) OR notas.year IS NULL) 
             AND (notas.periodo = ? OR notas.periodo IS NULL)";

$stmtNotas = $mysqli->prepare($sqlNotas);
if (!$stmtNotas) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar consulta de notas']);
    exit;
}

$info = [];
while ($docente = $resDocentes->fetch_assoc()) {
    $idDocente = $docente['identificacion'];
    $nombreDocente = $docente['nombres'];
    $sede = $docente['sede'];
    $asignacion = (int)$docente['asignacion'];
    
    $datosNotas = [];
    if ($x !== 'x') {
        $stmtNotas->bind_param('ss', $idDocente, $periodo);
        $stmtNotas->execute();
        $resNotas = $stmtNotas->get_result();
        $datosNotas = $resNotas->fetch_all(MYSQLI_ASSOC);
    }
    
    $info[] = [
        'docente' => $idDocente,
        'nombres' => $nombreDocente,
        'cantidad' => count($datosNotas),
        'sede' => $sede,
        'asignacion' => $asignacion,
        'periodo' => $periodo,
        'datos' => $datosNotas
    ];
}

echo json_encode($info);

$resDocentes->free();
$stmtNotas->close();
