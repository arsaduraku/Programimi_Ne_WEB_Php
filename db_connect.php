<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tour_guide_prishtina');
define('DB_USER', 'root');
define('DB_PASS', 'arsa2006');  

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        throw new Exception("Lidhja me databazën dështoi: " . $conn->connect_error);
    }

    $conn->set_charset('utf8');

} catch (Exception $e) {
    // Regjistro gabimin
    $logMsg = date('Y-m-d H:i:s') . " [DB_ERROR] " . $e->getMessage() . "\n";
    file_put_contents('errors.log', $logMsg, FILE_APPEND | LOCK_EX);

    die("<div style='font-family:Arial;color:red;padding:20px;'>
         X Gabim i brendshëm. Sigurohu që MySQL është aktiv në XAMPP.
         </div>");
}
?>