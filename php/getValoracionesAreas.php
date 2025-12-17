<?php

require_once "cors.php";


require_once "../datos_conexion.php";

// Helper function to interpolate SQL queries for debugging
function interpolate_sql($sql, $params) {
    $indexed_params = $params;
    foreach ($indexed_params as &$param) {
        if (is_string($param)) {
            $param = "'" . $param . "'"; // Quote string parameters
        } elseif (is_null($param)) {
            $param = 'NULL';
        }
    }
    $sql = preg_replace_callback('/\?/', function($match) use (&$indexed_params) {
        return array_shift($indexed_params);
    }, $sql);
    return $sql;
}

$debug = false; // Set to true for debugging, false for production

require_once "Database.php";
$db = Database::getInstance();
$mysqli = $db->getConnection();


$datos = (object)json_decode(file_get_contents("php://input"));

// Input validation and sanitization
$nivel      = filter_var($datos->nivel ?? null, FILTER_VALIDATE_INT);
$numero     = filter_var($datos->numero ?? null, FILTER_VALIDATE_INT);
$periodo    = $datos->periodo ?? null;
$asignacion_suffix = ($datos->Asignacion ?? null) === "5" ? "_5" : ""; // Use a different variable name to avoid confusion
$year       = filter_var($datos->year ?? null, FILTER_VALIDATE_INT);

// Set default values for debugging if debug is true and parameters are null
if ($debug) {
    $nivel      = $nivel ?? 11;      // Example default
    $numero     = $numero ?? 2;      // Example default
    $periodo    = $periodo ?? 'CUATRO';    // Example default
    $asignacion_suffix = $asignacion_suffix ?? '';  // Example default (empty string for no suffix)
    $year       = $year ?? 2025;     // Example default
}

$debug_sqls = []; // Array to store SQL queries for debugging



$datos = (object)json_decode(file_get_contents("php://input"));
/*$sql=sprintf("Select notas.estudiante,nombres as Nombres,valoracion as Val,%s,%s,%s from notas",notas(),aspectos(),porcentajes());
    $sql.=" inner join estugrupos on notas.estudiante=estugrupos.estudiante";
    $sql.=" where docente='$datos->docente' and estugrupos.nivel='$datos->nivel'"; 
    $sql.=" and estugrupos.numero='$datos->numero' and asignatura='$datos->asignatura'";
    $sql.=" and periodo='$datos->periodo'";
    $sql.=" and notas.year='$datos->year'";
    $sql.=" order by nombres";*/
$sql = "";
$asignacion = $datos->Asignacion === "5" ? "_5" : "";
if ($datos->periodo == "CINCO") {
    $drop_sql = "DROP TABLE IF EXISTS areasCINCO";
    $mysqli->query($drop_sql);
    if ($debug) {
        $debug_sqls['drop_table_cinco'] = $drop_sql;
    }

    $create_sql = " CREATE TABLE areasCINCO\n";
    $create_sql .= " Select estugrupos.estudiante,estugrupos.nombres,porcentajes_area_colegio{$asignacion_suffix}.area,porcentajes_area_colegio{$asignacion_suffix}.abreviatura as asignat,(sum(valoracion)/4*porcentajes_area_colegio{$asignacion_suffix}.porcentaje/100) as valoracion  from estugrupos\n";
    $create_sql .= " inner join notas on estugrupos.estudiante=notas.estudiante and estugrupos.year=notas.year\n";
    $create_sql .= " inner join asignacion_asignaturas on notas.asignatura=asignacion_asignaturas.asignatura\n";
    $create_sql .= " inner join porcentajes_area_colegio{$asignacion_suffix} on asignacion_asignaturas.asignatura=porcentajes_area_colegio{$asignacion_suffix}.asignatura and asignacion_asignaturas.year=porcentajes_area_colegio{$asignacion_suffix}.year\n";
    $create_sql .= "  inner join docentes on asignacion_asignaturas.docente=docentes.identificacion";
    $create_sql .= " where 1=1\n";
    $params = [];
    $types = "";

    if ($year === (int)date('Y')) {
        $create_sql .= " and docentes.activo='S'";
    }
    $create_sql .= " and docentes.asignacion=?\n";
    $params[] = $datos->Asignacion;
    $types .= "s";

    $create_sql .= " and estugrupos.asignacion=?\n";
    $params[] = $datos->Asignacion;
    $types .= "s";

    $create_sql .= " and porcentajes_area_colegio{$asignacion_suffix}.nivel=?\n";
    $params[] = $nivel;
    $types .= "i";

    $create_sql .= " and asignacion_asignaturas.nivel=?\n";
    $params[] = $nivel;
    $types .= "i";

    $create_sql .= " and asignacion_asignaturas.numero=?\n";
    $params[] = $numero;
    $types .= "i";

    $create_sql .= " and estugrupos.nivel=?\n";
    $params[] = $nivel;
    $types .= "i";

    $create_sql .= " and estugrupos.numero=?\n";
    $params[] = $numero;
    $types .= "i";

    if ($year === (int)date('Y')) {
        $create_sql .= " and estugrupos.activo='S'\n";
    }
    $create_sql .= " and (notas.year=?)\n";
    $params[] = $year;
    $types .= "i";

    $create_sql .= " and porcentajes_area_colegio{$asignacion_suffix}.year=?\n";
    $params[] = $year;
    $types .= "i";

    $create_sql .= " and (estugrupos.year=?) \n";
    $params[] = $year;
    $types .= "i";

    $create_sql .= " and (asignacion_asignaturas.year=?)\n";
    $params[] = $year;
    $types .= "i";

    $create_sql .= " group by estugrupos.estudiante,porcentajes_area_colegio{$asignacion_suffix}.area,notas.asignatura\n";
    $create_sql .= " order by estugrupos.nombres,area";

    $stmt_create = $mysqli->prepare($create_sql);
    $stmt_create->bind_param($types, ...$params);
    if ($debug) {
        $debug_sqls['create_table_cinco_select'] = interpolate_sql($create_sql, $params);
    }
    $stmt_create->execute();
    $stmt_create->close();
    /* //echo json_encode(array("sql"=>$sql)); exit(0); 
    //  $mysqli->query($sql); */
} else {
    $drop_sql = "DROP TABLE IF EXISTS areasCINCO";
    $mysqli->query($drop_sql);
    if ($debug) {
        $debug_sqls['drop_table_other_period'] = $drop_sql;
    }

    $create_sql = " CREATE TABLE areasCINCO\n";
    $create_sql .= " Select estugrupos.estudiante,estugrupos.nombres,porcentajes_area_colegio{$asignacion_suffix}.area,porcentajes_area_colegio{$asignacion_suffix}.abreviatura as asignat,(avg(valoracion)*porcentajes_area_colegio{$asignacion_suffix}.porcentaje/100) as valoracion  from estugrupos\n";
    $create_sql .= " inner join notas on estugrupos.estudiante=notas.estudiante and estugrupos.year=notas.year\n";
    $create_sql .= " inner join asignacion_asignaturas on notas.asignatura=asignacion_asignaturas.asignatura\n";
    $create_sql .= " inner join porcentajes_area_colegio{$asignacion_suffix} on asignacion_asignaturas.asignatura=porcentajes_area_colegio{$asignacion_suffix}.asignatura and asignacion_asignaturas.year=porcentajes_area_colegio{$asignacion_suffix}.year\n";
    $create_sql .= "  inner join docentes on asignacion_asignaturas.docente=docentes.identificacion";
    $create_sql .= " where 1=1\n";
    $params = [];
    $types = "";

    $create_sql .= " and docentes.asignacion=?\n";
    $params[] = $datos->Asignacion;
    $types .= "s";

    if ($year === (int)date('Y')) {
        $create_sql .= " and docentes.activo='S'";
    }
    $create_sql .= " and estugrupos.asignacion=?\n";
    $params[] = $datos->Asignacion;
    $types .= "s";

    $create_sql .= " and porcentajes_area_colegio{$asignacion_suffix}.nivel=?\n";
    $params[] = $nivel;
    $types .= "i";

    $create_sql .= " and asignacion_asignaturas.nivel=?\n";
    $params[] = $nivel;
    $types .= "i";

    $create_sql .= " and asignacion_asignaturas.numero=?\n";
    $params[] = $numero;
    $types .= "i";

    $create_sql .= " and estugrupos.nivel=?\n";
    $params[] = $nivel;
    $types .= "i";

    $create_sql .= " and estugrupos.numero=?\n";
    $params[] = $numero;
    $types .= "i";

    if ($year === (int)date('Y')) {
        $create_sql .= " and estugrupos.activo='S'\n";
    }
    $create_sql .= " and (notas.year=?)\n";
    $params[] = $year;
    $types .= "i";

    $create_sql .= " and porcentajes_area_colegio{$asignacion_suffix}.year=?\n";
    $params[] = $year;
    $types .= "i";

    $create_sql .= " and (estugrupos.year=?) \n";
    $params[] = $year;
    $types .= "i";

    $create_sql .= " and (asignacion_asignaturas.year=?)\n";
    $params[] = $year;
    $types .= "i";

    $create_sql .= " group by estugrupos.estudiante,porcentajes_area_colegio{$asignacion_suffix}.area,notas.asignatura\n";
    $create_sql .= " order by estugrupos.nombres,area";

    $stmt_create = $mysqli->prepare($create_sql);
    $stmt_create->bind_param($types, ...$params);
    if ($debug) {
        $debug_sqls['create_table_other_period_select'] = interpolate_sql($create_sql, $params);
    }
    $stmt_create->execute();
    $stmt_create->close();

   /*   echo json_encode(array("sql" => $sql));
    exit(0); */

    //  echo json_encode(array("sql"=>$sql)); exit(0); 
}
if ($debug) {
    echo json_encode($debug_sqls);
} else {
    // The $mysqli->query($sql) that was here is now handled by $stmt_create->execute()
    // in the if/else blocks above.

    $final_select_sql = "select estudiante,nombres,area,asignat,sum(valoracion) as valoracion from areasCINCO";
    $final_select_sql .= " group by estudiante,asignat";

    if ($debug) {
        $debug_sqls['final_select'] = $final_select_sql;
    }

    $result = $mysqli->query($final_select_sql);
    $datos = $result->fetch_all(MYSQLI_ASSOC);

    function redondearValoracion($dato)
    {
        $dato['valoracion'] = round($dato['valoracion'], 3);
        return $dato;
    }

    $datosRedondeados = array_map('redondearValoracion', $datos);

    echo json_encode($datosRedondeados);
    $result->free();
}
// $mysqli->close();
