<?php
$allowed_origins = [
    "http://l27.0.0.1:3000",
    "http://localhost:3000",
    "http://localhost:5173",
    "http://localhost:4173",
    "https://app.iedeoccidente.com"
];

// Verifica si la cabecera Origin existe y está permitida
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Credentials: true");
    header('Content-Type: application/json; charset=utf8');
}

// Manejo de preflight (OPTIONS) para CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>
