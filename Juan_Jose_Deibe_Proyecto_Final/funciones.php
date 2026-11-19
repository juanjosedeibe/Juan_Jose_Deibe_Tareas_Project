<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

requerir_admin();

// El ID llega por GET al abrir el formulario y por POST al guardarlo
$entrada = ($_SERVER['REQUEST_METHOD'] === 'POST') ? INPUT_POST : INPUT_GET;
$id = filter_input($entrada, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id < 1) { // Si el ID no es un entero positivo válido, se considera inválido
    http_response_code(400);
    exit('Identificador inválido.');
}

// Solo se pueden editar usuarios activos
$stmt = $conexion->prepare('SELECT id, nombre, apellido, dni, email
                            FROM usuarios
                            WHERE id = :id AND archivado_en IS NULL
                            LIMIT 1');
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch();

// Si no se encuentra el usuario, se devuelve un error 404
if ($usuario === false) {
    http_response_code(404);
    exit('Usuario no encontrado.');
}

$errores  = [];
$nombre   = $usuario['nombre'];
$apellido = $usuario['apellido'];
$dni      = $usuario['dni'];
$email    = $usuario['email'];

// Procesar el formulario al enviarlo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_token_csrf($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Solicitud no válida.');
    }

    $nombre   = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dni      = trim($_POST['dni'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }
    if ($apellido === '') {
        $errores[] = 'El apellido es obligatorio.';
    }
    if (!preg_match('/^[0-9]{7,8}$/', $dni)) {
        $errores[] = 'El DNI debe tener 7 u 8 números.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo válido.';
    }

    if (empty($errores)) {
        try {
            // El rol no se modifica desde este formulario
            $sql = 'UPDATE usuarios
                    SET nombre = :nombre,
                        apellido = :apellido,
                        dni = :dni,
                        email = :email
                    WHERE id = :id
                    AND archivado_en IS NULL';
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                'nombre'   => $nombre,
                'apellido' => $apellido,
                'dni'      => $dni,
                'email'    => $email,
                'id'       => $id
            ]);
            $_SESSION['mensaje'] = 'Datos actualizados correctamente.';
            header('Location: admin_usuarios.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errores[] = 'El DNI o el correo electrónico ya pertenecen a otro usuario.';
            } else {
                error_log($e->getMessage());
                $errores[] = 'Ocurrió un error al guardar los datos.';
            }
        }
    }
}

// Generar un token CSRF para proteger el formulario contra ataques de falsificación de solicitudes
$token = generar_token_csrf();

$titulo = 'Editar usuario';
require __DIR__ . '/includes/header.php';
?>

<section class="tarjeta">
    <h1>Editar usuario</h1>

    <?php if (!empty($errores)): ?>
        <ul class="errores">
            <?php foreach ($errores as $error): ?>
                <li><?= escapar($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="editar_usuario.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= escapar($token) ?>">
        <input type="hidden" name="id" value="<?= (int) $id ?>">

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

        <div class="acciones">
            <button type="submit">Guardar cambios</button>
            <a href="admin_usuarios.php">Cancelar</a>
        </div>
    </form>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>