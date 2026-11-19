<?php
require_once __DIR__ . '/includes/funciones.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
} elseif (($_SESSION['usuario_rol'] ?? '') === 'admin') {
    header('Location: admin_usuarios.php');
} else {
    header('Location: panel.php');
}
exit;