<?php
// Funciones compartidas por todo el proyecto
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Escapa texto antes de mostrarlo en HTML (evita XSS)
function escapar(string $valor): string {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

// Genera (o reutiliza) el token CSRF (un token único que se utiliza para saber si el usuario es el que pide la solicitud) de la sesión
function generar_token_csrf(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Compara el token recibido por POST con el de la sesión
function validar_token_csrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Páginas privadas: pide sesión iniciada
function requerir_login(): void {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Páginas administrativas: pide sesión y rol admin
function requerir_admin(): void {
    if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Acceso denegado.');
    }
}

// Muestra un dato escapado, o un guion si está vacío (para datos opcionales)
function dato_o_guion($valor): string {
    if ($valor === null || $valor === '') {
        return '—';
    }
    return escapar((string) $valor);
}