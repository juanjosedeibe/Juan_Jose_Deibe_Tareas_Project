<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

// Si ya hay sesión iniciada, ir directo al panel que corresponde
if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . (($_SESSION['usuario_rol'] ?? '') === 'admin' ? 'admin_usuarios.php' : 'panel.php'));
    exit;
}

$errores = [];
$email   = '';


// Procesar el formulario al enviarlo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errores[] = 'Completá el correo y la contraseña.';
    } else {
        $sql = 'SELECT id, nombre, email, password_hash, rol
                FROM usuarios
                WHERE email = :email
                AND archivado_en IS NULL
                LIMIT 1';
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario !== false && password_verify($password, $usuario['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id']  = $usuario['id'];
            $_SESSION['usuario_rol'] = $usuario['rol'];

            if ($usuario['rol'] === 'admin') {
                header('Location: admin_usuarios.php');
            } else {
                header('Location: panel.php');
            }
            exit;
        }

        // No se revela cuál de los dos datos es incorrecto
        $errores[] = 'Correo o contraseña incorrectos.';
    }
}

$titulo = 'Iniciar sesión';
require __DIR__ . '/includes/header.php';
?>

<section class="tarjeta">
    <h1>Iniciar sesión</h1>

    <?php if (!empty($errores)): ?>
        <ul class="errores">
            <?php foreach ($errores as $error): ?>
                <li><?= escapar($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" maxlength="254" value="<?= escapar($email) ?>" required>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn-login">Ingresar</button>
    </form>

    <p>¿No tenés cuenta? <a href="registro.php">Registrate</a></p>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>