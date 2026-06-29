<?php
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'atenea';
$dbPort = (int) (getenv('DB_PORT') ?: 3306);

$db = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

if (!$db) {
    die(
        sprintf(
            'Unable to connect to MySQL (%d): %s',
            mysqli_connect_errno(),
            mysqli_connect_error()
        )
    );
}

mysqli_set_charset($db, 'utf8mb4');
?>
