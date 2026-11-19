<?php
$host     = 'localhost';
$dbname   = 'club_futbol';
$usuario  = 'root';
$password = '';

try {
    $conexion = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $usuario,
        $password
    );
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Configura el modo de error para lanzar excepciones
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Configura el modo de obtención de resultados por defecto como array asociativo
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('No se pudo conectar con la base de datos.');
}