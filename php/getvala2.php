<?php
require_once "cors.php";


require_once "../datos_conexion.php";

// === Modo debug ===
$debug = false; // ← Cambia a false en producción

// Valores por defecto para debug
$debug_defaults = [
    'nivel' => 11,
    'numero' => 2,
    'periodo' => 'CINCO',
    'Asignacion' => '1',
    'year' => 2025
];

$debug_sqls = [];
$debug_results = null;

// === Conexión ===
$mysqli = new mysqli($host, $user, $pass, $database);
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit();
}
$mysqli->set_charset('utf8mb4');

// === Obtener y validar entrada ===
if ($debug) {
    // En modo debug, usar valores por defecto (ignorar el body)
    $datos = (object)$debug_defaults;
} else {
    $raw_input = file_get_contents("php://input");
    $decoded = json_decode($raw_input, false);
    if (json_last_error() !== JSON_ERROR_NONE || !is_object($decoded)) {
        http_response_code(400);
        echo json_encode(['error' => 'JSON inválido']);
        exit();
    }
    $datos = $decoded;
}

// === Validación estricta ===
$nivel = filter_var($datos->nivel ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]);
$numero = filter_var($datos->numero ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10]]);
$periodo = isset($datos->periodo) && in_array($datos->periodo, ['UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'DEF']) ? $datos->periodo : null;
$asignacion = isset($datos->Asignacion) && in_array($datos->Asignacion, ['1', '5']) ? $datos->Asignacion : null;
$year = filter_var($datos->year ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 2000, 'max_range' => 2100]]);

if ($nivel === false || $numero === false || $periodo === null || $asignacion === null || $year === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros inválidos']);
    exit();
}

// === Sufijo seguro ===
$table_suffix = ($asignacion === '5') ? '_5' : '';
$valid_suffixes = ['', '_5'];
if (!in_array($table_suffix, $valid_suffixes)) {
    http_response_code(500);
    echo json_encode(['error' => 'Sufijo de tabla no permitido']);
    exit();
}

// === Construir consulta ===
$is_cinco = ($periodo === 'CINCO');

$base_sql = "
    SELECT
        eg.estudiante,
        eg.nombres,
        pac.area,
        pac.abreviatura AS asignat,
        " . ($is_cinco ? "(SUM(n.valoracion) / 4 * pac.porcentaje / 100)" : "(AVG(n.valoracion) * pac.porcentaje / 100)") . " AS valoracion
    FROM estugrupos eg
    INNER JOIN notas n
        ON eg.estudiante = n.estudiante AND eg.year = n.year
    INNER JOIN asignacion_asignaturas aa
        ON n.asignatura = aa.asignatura AND n.year = aa.year
    INNER JOIN porcentajes_area_colegio{$table_suffix} pac
        ON aa.asignatura = pac.asignatura AND aa.year = pac.year
    INNER JOIN docentes d
        ON aa.docente = d.identificacion
    WHERE
        d.asignacion = ?
        AND eg.asignacion = ?
        AND pac.nivel = ?
        AND aa.nivel = ?
        AND aa.numero = ?
        AND eg.nivel = ?
        AND eg.numero = ?
        AND n.year = ?
        AND pac.year = ?
        AND eg.year = ?
        AND aa.year = ?
";

$current_year = (int)date('Y');
if ($year === $current_year) {
    $base_sql .= " AND d.activo = 'S' AND eg.activo = 'S'";
}

$base_sql .= " GROUP BY eg.estudiante, eg.nombres, pac.area, pac.abreviatura, pac.porcentaje ";

$params = [
    $asignacion,
    $asignacion,
    $nivel,
    $nivel,
    $numero,
    $nivel,
    $numero,
    $year,
    $year,
    $year,
    $year
];
$types = "ssiisiiiiii";

if ($debug) {
    $debug_sqls['main_query'] = interpolate_sql($base_sql, $params);
}

// === Consulta final con agrupación ===
$final_sql = "
    SELECT 
        estudiante,
        nombres,
        area,
        asignat,
        SUM(valoracion) AS valoracion
    FROM (
        {$base_sql}
    ) AS subquery
    GROUP BY estudiante, nombres, area, asignat
    ORDER BY nombres, area
";

$stmt = $mysqli->prepare($final_sql);
if (!$stmt) {
    error_log("Error preparando consulta: " . $mysqli->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar consulta']);
    exit();
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    error_log("Error ejecutando consulta: " . $mysqli->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error al ejecutar consulta']);
    exit();
}

$datos_raw = $result->fetch_all(MYSQLI_ASSOC);
$datos_redondeados = array_map(fn($row) => [
    'estudiante' => $row['estudiante'],
    'nombres' => $row['nombres'],
    'area' => $row['area'],
    'asignat' => $row['asignat'],
    'valoracion' => round((float)$row['valoracion'], 3)
], $datos_raw);

if ($debug) {
    $debug_results = $datos_redondeados;
    $output = [
        'debug' => true,
        'inputs' => [
            'nivel' => $nivel,
            'numero' => $numero,
            'periodo' => $periodo,
            'asignacion' => $asignacion,
            'year' => $year,
            'table_suffix' => $table_suffix
        ],
        'sql_queries' => $debug_sqls,
        'results_count' => count($debug_results),
        'results' => $debug_results
    ];
    echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode($datos_redondeados, JSON_UNESCAPED_UNICODE);
}

$stmt->close();
$mysqli->close();

// === Función de interpolación (solo para debug) ===
function interpolate_sql($sql, $params) {
    $indexed = array_values($params);
    foreach ($indexed as &$param) {
        if (is_string($param)) {
            $param = "'" . addslashes($param) . "'";
        } elseif (is_null($param)) {
            $param = 'NULL';
        } elseif (is_bool($param)) {
            $param = $param ? '1' : '0';
        }
    }
    return preg_replace_callback('/\?/', function() use (&$indexed) {
        return array_shift($indexed) ?? 'MISSING';
    }, $sql);
}