<?php
require_once 'cors.php';

// Conexi贸n a BD
require_once "../datos_conexion.php";

$mysqli = new mysqli($host, $user, $pass, $database);
$mysqli->query("SET NAMES utf8");
$mysqli->set_charset('utf8');

if (!empty($input['debug']) && $input['debug'] === true) {
    $DEBUG_MODE = true;
}

// Extraer parámetros (permitir sobrescritura en modo debug)
$estudiante = isset($input['estudiante']) ? trim($input['estudiante']) : '';
$year = isset($input['year']) ? intval($input['year']) : 0;

// Validación básica (solo si no estamos en modo debug, o si los datos están presentes)
if (!$DEBUG_MODE) {
    if (empty($estudiante) || $year <= 0) {
        echo json_encode(['msg' => 'Faltan parámetros: estudiante y/o year.']);
        exit();
    }
    if ($year < 2000 || $year > 2030) {
        echo json_encode(['msg' => 'Año inválido.']);
        exit();
    }
    if (!preg_match('/^[a-zA-Z0-9]+$/', $estudiante)) {
        echo json_encode(['msg' => 'ID de estudiante inválido.']);
        exit();
    }
} else {
    // En modo debug, permitimos valores vacíos o inválidos, pero los mostramos tal cual
    // (útil para probar errores o diferentes entradas)
}

// Plantilla de la consulta
$sqlTemplate = "
    SELECT 
        convivencia.ind,
        estugrupos.estudiante AS estudiante,
        estugrupos.nombres AS nombres,
        CONCAT_WS('-', estugrupos.nivel, estugrupos.numero) AS grupo,
        sedes.sede AS sede,
        convivencia.asignatura,
        convivencia.fecha,
        IF(convivencia.hora <> '', convivencia.hora, SUBSTRING(convivencia.fechahora, 11, 8)) AS hora,
        IF(convivencia.tipoFalta = 'POSITIVO', 'OTRA OBSERVACION', convivencia.tipoFalta) AS tipoFalta,
        IF(convivencia.firma <> '', '1', '0') AS firmado,
        convivencia.firma  
    FROM convivencia
    INNER JOIN estugrupos ON convivencia.estudiante = estugrupos.estudiante
    INNER JOIN sedes ON estugrupos.asignacion = sedes.ind
    WHERE convivencia.estudiante = ?
      AND estugrupos.year = ?
      AND YEAR(convivencia.fechahora) = ?
    ORDER BY convivencia.fechahora DESC, sedes.sede
";

// Modo depuración: mostrar consulta con parámetros reemplazados
if ($DEBUG_MODE) {
    // Escapado básico para visualización (solo para desarrollo)
    $safeEstudiante = addslashes($estudiante); // suficiente para depuración, no para ejecución

    $debugSql = str_replace(
        ['?', '?', '?'],
        [
            "'{$safeEstudiante}'",
            (int)$year,
            (int)$year
        ],
        $sqlTemplate
    );

    echo json_encode([
        'debug' => true,
        'sql_interpolada' => $debugSql,
        'parametros_usados' => [
            'estudiante' => $estudiante,
            'year' => $year
        ],
        'nota' => 'La consulta NO fue ejecutada. Solo se muestra para depuración.'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

// ────────────────
// Ejecución normal (solo si NO está en modo debug)
// ────────────────

require_once "../datos_conexion.php";

$mysqli = new mysqli($host, $user, $pass, $database);

if ($mysqli->connect_error) {
    echo json_encode(['msg' => 'Error de conexión: ' . $mysqli->connect_error]);
    exit();
}

$mysqli->set_charset('utf8');

$stmt = $mysqli->prepare($sqlTemplate);
if (!$stmt) {
    echo json_encode(['msg' => 'Error al preparar la consulta: ' . $mysqli->error]);
    $mysqli->close();
    exit();
}

$stmt->bind_param('sss', $estudiante, $year, $year);

if (!$stmt->execute()) {
    echo json_encode(['msg' => 'Error al ejecutar la consulta: ' . $stmt->error]);
    $stmt->close();
    $mysqli->close();
    exit();
}

$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$mysqli->close();

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>