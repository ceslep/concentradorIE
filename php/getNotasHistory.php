<?php
// Define debug mode
const DEBUG_MODE = false; // Set to true to enable debug output

require_once "cors.php";

    require_once("../datos_conexion.php");

    // Establish database connection
    $mysqli = new mysqli($host, $user, $pass, $database);

    // Check for connection errors
    if ($mysqli->connect_error) {
        echo json_encode(["error" => "Error de conexión a la base de datos: " . $mysqli->connect_error]);
        exit();
    }

    $mysqli->set_charset('utf8');

    $input_data = json_decode(file_get_contents('php://input'));

    if ($input_data === null) {
        echo json_encode(["error" => "Invalid JSON payload or empty request body."]);
        exit();
    }

    // Prepare the SQL statement using placeholders
    $sql = "SELECT * FROM notas2
            WHERE estudiante = ?
            AND periodo = ?
            AND asignatura = ?
            AND year = ?";

    $stmt = $mysqli->prepare($sql);

    // Debug option: print SQL query if DEBUG_MODE is true
    if (DEBUG_MODE) {
        // Ensure Content-Type is application/json for debug output
        header("Content-Type: application/json");

        // Get bound parameters (this is a simplified approach, real mysqli_stmt_get_bindings is complex)
        // For demonstration, we'll use the input_data directly.
        $bound_params = [$input_data->estudiante, $input_data->periodo, $input_data->asignatura,$input_data->year];

        $debug_sql = $sql;
        $param_index = 0;
        while (($pos = strpos($debug_sql, '?')) !== false && $param_index < count($bound_params)) {
            $param = $bound_params[$param_index];
            $param_value = is_string($param) ? "'" . $mysqli->real_escape_string($param) . "'" : $param;
            $debug_sql = substr_replace($debug_sql, $param_value, $pos, 1);
            $param_index++;
        }

        echo json_encode(['sql' => $debug_sql]);
        $mysqli->close();
        exit();
    }

    // Check if the statement preparation was successful
    if ($stmt === false) {
        echo json_encode(["error" => "Error al preparar la consulta: " . $mysqli->error]);
        $mysqli->close();
        exit();
    }

    // Bind parameters
    $stmt->bind_param("ssss", $input_data->estudiante, $input_data->periodo, $input_data->asignatura,$input_data->year);

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Fetch all results as an associative array
    $datos = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($datos);

    // Close statement and connection
    $stmt->close();
    $mysqli->close();
?>
