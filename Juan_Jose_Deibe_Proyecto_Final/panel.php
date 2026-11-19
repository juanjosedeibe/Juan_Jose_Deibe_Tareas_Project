<?php
// Cabecera común. Antes de incluirla, definir $titulo (opcional).
require_once __DIR__ . '/funciones.php';
$titulo = $titulo ?? 'Club de Fútbol';
$logueado = isset($_SESSION['usuario_id']);
$esAdmin = ($_SESSION['usuario_rol'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escapar($titulo) ?> | Club de Fútbol</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<header class="barra">
    <a class="logo" href="index.php">Club de Fútbol</a>
    <nav>
        <?php if ($logueado): ?>
            <a href="panel.php">Mi panel</a>
            <?php if ($esAdmin): ?>
                <a href="admin_usuarios.php">Usuarios</a>
                <a href="crear_usuario.php">Nuevo usuario</a>
                <a href="usuarios_archivados.php">Archivados</a>
            <?php endif; ?>
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php">Iniciar sesión</a>
            <a href="registro.php">Registrarse</a>
        <?php endif; ?>
    </nav>
</header>
<main class="contenedor">