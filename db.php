<?php
/* Connect to an ODBC database using driver invocation */
$dsn = 'mysql:dbname=;host=';
$user = '';
$password = '';

try {
    $db = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>
