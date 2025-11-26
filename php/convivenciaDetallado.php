<?php
require_once 'cors.php';

// Conexi贸n a BD
require_once "../datos_conexion.php";
$mysqli = new mysqli($host, $user, $pass, $database);
$mysqli->query("SET NAMES utf8");
$mysqli->set_charset('utf8');

$input = json_decode(file_get_contents('php://input'), true);

if (!empty($input['debug']) && $input['debug'] === true) {
    $DEBUG_MODE = true;
}

// Extraer parámetros
$ind = isset($input['ind']) ? trim($input['ind']) : null;
$estudiante = isset($input['estudiante']) ? trim($input['estudiante']) : null;
$year = isset($input['year']) ? intval($input['year']) : 0;

// Validación básica (solo en modo normal)
if (!$DEBUG_MODE) {
    if ($ind === null && $estudiante === null) {
        echo json_encode(['msg' => 'Debe proporcionar "ind" o "estudiante".']);
        exit();
    }
    if ($year <= 0 || $year < 2000 || $year > 2030) {
        echo json_encode(['msg' => 'Año inválido.']);
        exit();
    }
    // Validar formato simple de ID (ajusta según tu esquema)
    if ($ind !== null && !preg_match('/^[a-zA-Z0-9]+$/', $ind)) {
        echo json_encode(['msg' => 'ID de registro inválido.']);
        exit();
    }
    if ($estudiante !== null && !preg_match('/^[a-zA-Z0-9]+$/', $estudiante)) {
        echo json_encode(['msg' => 'ID de estudiante inválido.']);
        exit();
    }
}

// ───────────────────────────────
// Construcción de la consulta base
// ───────────────────────────────
$sqlBase = "
    SELECT 
        estugrupos.nombres,
        docentes.nombres AS cv_docente,
        convivencia.asignatura AS cv_asignatura,
        IF(convivencia.tipoFalta = 'POSITIVO', 'OTRAS OBSERVACIONES', convivencia.tipoFalta) AS cv_tipoFalta,
        REPLACE(convivencia.faltas, 'Array', '') AS cv_faltas,
        convivencia.descripcionSituacion AS cv_descripcionSituacion,
        convivencia.fecha AS cv_fecha,
        IF(convivencia.hora <> '', convivencia.hora, SUBSTRING(convivencia.fechahora, 11, 8)) AS cv_hora,
        convivencia.descargosEstudiante AS cv_descargosEstudiante,
        convivencia.positivos AS cv_positivos,
        convivencia.firma AS cv_firma,
        convivencia.firmaAcudiente,
        convivencia.fechahora AS cv_fechahora
    FROM convivencia
    INNER JOIN docentes ON convivencia.docente = docentes.identificacion 
    INNER JOIN estugrupos ON convivencia.estudiante = estugrupos.estudiante
    WHERE 
";

// Modo depuración: mostrar consulta interpolada
if ($DEBUG_MODE) {
    if ($ind !== null) {
        $where = "convivencia.ind = '" . addslashes($ind) . "'";
    } else {
        $where = "convivencia.estudiante = '" . addslashes($estudiante) . "'";
    }
    $where .= " AND YEAR(convivencia.fechahora) = " . (int)$year;
    $where .= " AND estugrupos.year = " . (int)$year;

    $debugSql = $sqlBase . $where . " ORDER BY convivencia.fechahora DESC";

    echo json_encode([
        'debug' => true,
        'sql_interpolada' => $debugSql,
        'parametros' => [
            'ind' => $ind,
            'estudiante' => $estudiante,
            'year' => $year
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

// ───────────────────────────────
// Ejecución normal (con consultas preparadas)
// ───────────────────────────────
require_once "../datos_conexion.php";

$mysqli = new mysqli($host, $user, $pass, $database);

if ($mysqli->connect_error) {
    echo json_encode(['msg' => 'Error de conexión: ' . $mysqli->connect_error]);
    exit();
}

$mysqli->set_charset('utf8');

// Preparar la consulta según el caso
if ($ind !== null) {
    $sql = $sqlBase . "
        convivencia.ind = ?
        AND YEAR(convivencia.fechahora) = ?
        AND estugrupos.year = ?
        ORDER BY convivencia.fechahora DESC
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sss', $ind, $year, $year);
} else {
    $sql = $sqlBase . "
        convivencia.estudiante = ?
        AND YEAR(convivencia.fechahora) = ?
        AND estugrupos.year = ?
        ORDER BY convivencia.fechahora DESC
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sss', $estudiante, $year, $year);
}

if (!$stmt) {
    echo json_encode(['msg' => 'Error al preparar la consulta: ' . $mysqli->error]);
    $mysqli->close();
    exit();
}

if (!$stmt->execute()) {
    echo json_encode(['msg' => 'Error al ejecutar: ' . $stmt->error]);
    $stmt->close();
    $mysqli->close();
    exit();
}

$result = $stmt->get_result();
$datos = $result->fetch_all(MYSQLI_ASSOC);

// Aplicar html_entity_decode a todos los valores
foreach ($datos as &$fila) {
    foreach ($fila as $clave => &$valor) {
        if (is_string($valor)) {
            $valor = html_entity_decode($valor, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
}

$stmt->close();
$mysqli->close();

echo json_encode($datos, JSON_UNESCAPED_UNICODE);
?>