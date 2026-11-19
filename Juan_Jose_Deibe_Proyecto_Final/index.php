
<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

requerir_admin();

$roles_validos = ['usuario', 'admin'];

$errores  = []; // Array para almacenar mensajes de error
$nombre   = ''; // Mantener los valores ingresados en caso de error (nombre, apellido, dni, email, rol)
$apellido = ''; 
$dni      = '';
$email    = '';
$rol      = 'usuario';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_token_csrf($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Solicitud no válida.');
    }

    $nombre    = trim($_POST['nombre'] ?? '');
    $apellido  = trim($_POST['apellido'] ?? '');
    $dni       = trim($_POST['dni'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $rol       = trim($_POST['rol'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }
    if ($apellido === '') {
        $errores[] = 'El apellido es obligatorio.';
    }
    if (!preg_match('/^[0-9]{7,8}$/', $dni)) { // Validar que el DNI tenga 7 u 8 dígitos
        $errores[] = 'El DNI debe tener 7 u 8 números.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo válido.';
    }
    if (!in_array($rol, $roles_validos, true)) {
        $errores[] = 'El rol elegido no es válido.';
    }
    if (strlen($password) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    if ($password !== $confirmar) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    // Si no hay errores, intentar insertar el nuevo usuario en la base de datos
    if (empty($errores)) {
        try {
            $sql = 'INSERT INTO usuarios (nombre, apellido, dni, email, password_hash, rol)
                    VALUES (:nombre, :apellido, :dni, :email, :password_hash, :rol)';
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                'nombre'        => $nombre,
                'apellido'      => $apellido,
                'dni'           => $dni,
                'email'         => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'rol'           => $rol
            ]);
            $_SESSION['mensaje'] = 'Usuario registrado correctamente.';
            header('Location: admin_usuarios.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errores[] = 'El DNI o el correo electrónico ya están registrados.';
            } else {
                error_log($e->getMessage());
                $errores[] = 'Ocurrió un error al guardar los datos.';
            }
        }
    }
}

// Generar un token CSRF para proteger el formulario contra ataques de falsificación de solicitudes
$token = generar_token_csrf(); 

$titulo = 'Nuevo usuario';
require __DIR__ . '/includes/header.php';
?>

<section class="tarjeta">
    <h1>Registrar nuevo usuario</h1>

    <?php if (!empty($errores)): ?>
        <ul class="errores">
            <?php foreach ($errores as $error): ?>
                <li><?= escapar($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="crear_usuario.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= escapar($token) ?>">

        <div class="campo">
            <label for="nombre">Nombre *</label>
            <input type="text" id="nombre" name="nombre" maxlength="80" value="<?= escapar($nombre) ?>" required>
        </div>

        <div class="campo">
            <label for="apellido">Apellido *</label>
            <input type="text" id="apellido" name="apellido" maxlength="80" value="<?= escapar($apellido) ?>" required>
        </div>

        <div class="campo">
            <label for="dni">DNI *</label>
            <input type="text" id="dni" name="dni" inputmode="numeric" pattern="[0-9]{7,8}" maxlength="8" value="<?= escapar($dni) ?>" required>
        </div>

        <div class="campo">
            <label for="email">Correo electrónico *</label>
            <input type="email" id="email" name="email" maxlength="254" value="<?= escapar($email) ?>" required>
        </div>

        <div class="campo">
            <label for="rol">Rol *</label>
            <select id="rol" name="rol" required>
                <?php foreach ($roles_validos as $opcion): ?>
                    <option value="<?= escapar($opcion) ?>" <?= $rol === $opcion ? 'selected' : '' ?>><?= escapar($opcion) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="password">Contraseña * (mínimo 8 caracteres)</label>
            <input type="password" id="password" name="password" minlength="8" required>
        </div>

        <div class="campo">
            <label for="confirmar">Repetir contraseña *</label>
            <input type="password" id="confirmar" name="confirmar" minlength="8" required>
        </div>

        <div class="acciones">
            <button type="submit">Registrar usuario</button>
            <a href="admin_usuarios.php">Cancelar</a>
        </div>
    </form>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>