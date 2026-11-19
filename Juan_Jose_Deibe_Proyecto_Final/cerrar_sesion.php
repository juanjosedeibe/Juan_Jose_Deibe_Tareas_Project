<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

// Requerir que el usuario esté autenticado como administrador
requerir_admin();

// Solo se acepta POST (nunca archivar con un link GET)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

// Validar el token CSRF para proteger contra ataques de falsificación de solicitudes
if (!validar_token_csrf($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Solicitud no válida.');
}

// Obtener y validar el identificador del usuario a archivar
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('Identificador inválido.');
}

// Un administrador no puede archivar su propia cuenta
if ($id === (int) $_SESSION['usuario_id']) {
    $_SESSION['mensaje'] = 'No podés archivar tu propia cuenta.';
    header('Location: admin_usuarios.php');
    exit;
}

// Archivar el usuario estableciendo la fecha y hora actual en la columna archivado_en
$stmt = $conexion->prepare('UPDATE usuarios
                            SET archivado_en = CURRENT_TIMESTAMP
                            WHERE id = :id
                            AND archivado_en IS NULL');
$stmt->execute(['id' => $id]);

if ($stmt->rowCount() === 1) {
    $_SESSION['mensaje'] = 'Usuario archivado correctamente.';
} else {
    $_SESSION['mensaje'] = 'No se encontró un usuario activo con ese identificador.';
}

header('Location: admin_usuarios.php');
exit;