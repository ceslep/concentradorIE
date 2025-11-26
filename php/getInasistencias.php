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

$mysqli->set_charset("utf8");

// Leer datos del cuerpo
$input = json_decode(file_get_contents("php://input"), true);

// Validar campos obligatorios
$required = ['Asignacion', 'nivel', 'numero', 'periodo'];
foreach ($required as $field) {
    if (!isset($input[$field])) {
        echo json_encode(["error" => "Falta el campo: $field"]);
        exit();
    }
}

$asignacion = (int)$input['Asignacion'];
$nivel = (int)$input['nivel'];
$numero = (int)$input['numero'];
$periodo = $input['periodo'];

// Año actual
$anio_actual = date("Y");

// Si es nivel < 6, obtenemos el docente asignado
$docente_cond = "";
if ($nivel < 6) {
    $stmt_doc = $mysqli->prepare("
        SELECT DISTINCT aa.docente
        FROM asignacion_asignaturas aa
        INNER JOIN docentes d ON aa.docente = d.identificacion
        WHERE aa.asignacion = ? AND aa.nivel = ? AND aa.numero = ? AND aa.year = ?
        LIMIT 1
    ");
    $stmt_doc->bind_param("iiii", $asignacion, $nivel, $numero, $anio_actual);
    $stmt_doc->execute();
    $result_doc = $stmt_doc->get_result();
    $doc_row = $result_doc->fetch_assoc();
    $stmt_doc->close();

    if (!$doc_row) {
        echo json_encode(["error" => "No se encontró docente asignado para este grupo"]);
        exit();
    }
    $docente_id = $doc_row['docente'];
    $docente_cond = "AND aa.docente = ?";
} else {
    $docente_id = null;
}

// Construir consulta principal
$sql = "
    SELECT 
        e.estudiante,
        e.nombres,
        aa.asignatura AS asignat,
        aa.materia AS asignatura,
        SUM(
            CASE 
                WHEN i.horas = 't' THEN 6 
                WHEN i.horas REGEXP '^[0-9]+$' THEN CAST(i.horas AS UNSIGNED)
                ELSE 0
            END
        ) AS cantidadInasistencias
    FROM estugrupos e
    LEFT JOIN inasistencia i 
        ON e.estudiante = i.estudiante 
        AND e.year = i.year
    LEFT JOIN asignacion_asignaturas aa 
        ON i.materia = aa.asignatura 
        AND aa.asignacion = ?
        AND aa.nivel = ?
        AND aa.numero = ?
        AND aa.year = ?
        " . ($nivel < 6 ? "AND aa.docente = ?" : "") . "
    WHERE 
        e.asignacion = ? 
        AND e.nivel = ? 
        AND e.numero = ? 
        AND e.year = ?
        AND (i.horas IS NULL OR i.horas != 'r')
";

$params = [$asignacion, $nivel, $numero, $anio_actual];
$types = "iiii";

if ($nivel < 6) {
    $params[] = $docente_id;
    $types .= "i";
}

// Agregar condiciones finales
$sql .= " AND (i.periodo = ? OR i.periodo IS NULL)";
$params[] = $periodo;
$types .= "s";

$sql .= " AND (i.year = ? OR i.year IS NULL)";
$params[] = $anio_actual;
$types .= "i";

$sql .= "
    GROUP BY e.estudiante, aa.materia
    ORDER BY e.nombres, aa.materia
";

// Depuración
if (DEBUG_MODE) {
    error_log("Consulta SQL: " . $sql);
    error_log("Parámetros: " . json_encode($params));
}

// Preparar y ejecutar
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Error en la consulta: " . $mysqli->error]);
    exit();
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$datos = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

$stmt->close();
$mysqli->close();
?>