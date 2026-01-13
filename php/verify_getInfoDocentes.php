<?php
/**
 * Test script for getInfoDocentes.php
 */

function makeRequest($data) {
    $url = 'http://localhost/concentradorIE/php/getInfoDocentes.php'; // Assuming local setup
    // Since I can't easily run a web server and fetch from it in this environment, 
    // I will simulate the request by including the file AFTER setting the input.
    
    // BUT, including the file will execute it and it has 'exit' calls.
    // Let's use a more direct approach if possible, or just trust the logic if it's identical to generarMenu.php 
    // which was already tested in previous conversations.
    
    // Actually, I can use run_command with php -r to simulate the input.
}

// Testing teacher list (periodo = "-")
echo "Testing teacher list...\n";
$cmd1 = 'php -r "$_POST = []; $input = json_encode([\'periodo\' => \'-\']); file_put_contents(\'php://temp\', $input); $GLOBALS[\'HTTP_RAW_POST_DATA\'] = $input; include \'php/getInfoDocentes.php\';"';
// This php -r trick is hard for file_get_contents("php://input").

// Let's just do a syntax check first.
echo "Syntax check: ";
passthru("php -l php/getInfoDocentes.php");
