<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

requerir_admin();

// Mensaje de otras páginas (editar, archivar, crear...), se muestra una sola vez
$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);

$buscar = trim($_GET['buscar'] ?? ''); // Obtener el término de búsqueda desde la URL, si existe

$sql = 'SELECT id, nombre, apellido, dni, email, rol, creado_en, archivado_en
        FROM usuarios
        WHERE archivado_en IS NULL';
$parametros = [];

if ($buscar !== '') { // Si hay un término de búsqueda, agregar condiciones a la consulta
    $sql .= ' AND (nombre LIKE :nombre OR apellido LIKE :apellido OR dni LIKE :dni OR email LIKE :email)';
    $termino = '%' . $buscar . '%';
    $parametros = [
        'nombre'   => $termino,
        'apellido' => $termino,
        'dni'      => $termino,
        'email'    => $termino
    ];
}

// Ordenar los resultados por apellido y nombre
$sql .= ' ORDER BY apellido, nombre'; 

$stmt = $conexion->prepare($sql);
$stmt->execute($parametros);
$usuarios = $stmt->fetchAll();

$token = generar_token_csrf();

$titulo = 'Administrar usuarios';
require __DIR__ . '/includes/header.php';
?>

<section class="tarjeta tarjeta-ancha">
    <h1>Usuarios activos</h1>

    <?php if ($mensaje !== ''): ?>
        <p class="mensaje-ok"><?= escapar($mensaje) ?></p>
    <?php endif; ?>

    <form method="GET" action="admin_usuarios.php" class="buscador">
        <label for="buscar">Buscar usuario</label>
        <input type="search" id="buscar" name="buscar" maxlength="100" placeholder="Nombre, apellido, DNI o correo" value="<?= escapar($buscar) ?>">
        <button type="submit">Buscar</button>
        <?php if ($buscar !== ''): ?>
            <a href="admin_usuarios.php">Limpiar</a>
        <?php endif; ?>
    </form>

    <p><a href="crear_usuario.php">+ Registrar nuevo usuario</a></p>

    <?php if (empty($usuarios)): ?>
        <p>No se encontraron usuarios.</p>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla-listado">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>DNI</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= escapar((string) $usuario['id']) ?></td>
                            <td><?= escapar($usuario['nombre']) ?></td>
                            <td><?= escapar($usuario['apellido']) ?></td>
                            <td><?= escapar($usuario['dni']) ?></td>
                            <td><?= escapar($usuario['email']) ?></td>
                            <td><?= escapar($usuario['rol']) ?></td>
                            <td>Activo</td>
                            <td class="acciones-fila">
                                <a href="editar_usuario.php?id=<?= (int) $usuario['id'] ?>">Editar</a>

                                <?php if ((int) $usuario['id'] !== (int) $_SESSION['usuario_id']): ?>
                                    <form method="POST" action="archivar_usuario.php" onsubmit="return confirm('¿Archivar a este usuario?');">
                                        <input type="hidden" name="csrf_token" value="<?= escapar($token) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                                        <button type="submit">Archivar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>