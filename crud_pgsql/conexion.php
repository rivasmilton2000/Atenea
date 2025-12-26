<?php
$host = "localhost";
$db   = "prueba_php";
$user = "postgres";
$pass = "1581"; // la de PostgreSQL

try {
    $conexion = new PDO(
        "pgsql:host=$host;dbname=$db",
        $user,
        $pass
    );
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}
