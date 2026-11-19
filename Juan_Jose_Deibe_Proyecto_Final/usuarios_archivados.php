<?php
require_once __DIR__ . '/conexion.php'; //__DIR__ es una constante que devuelve la ruta del directorio actual del archivo
require_once __DIR__ . '/includes/funciones.php';

requerir_login();

// Se buscan los datos del usuario logueado (nunca se trae el hash)
$stmt = $conexion->prepare('SELECT nombre, apellido, dni, email, rol, creado_en,
                                   telefono, edad, posicion_favorita, competencias, mira_futbol,
                                   tipo_futbol, cantidad_jugadores, jugador_favorito, equipo_favorito
                            FROM usuarios
                            WHERE id = :id AND archivado_en IS NULL
                            LIMIT 1');
$stmt->execute(['id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

// Si la cuenta fue archivada mientras tenía la sesión abierta, se cierra
if ($usuario === false) {
    header('Location: cerrar_sesion.php');
    exit;
}

$titulo = 'Mi panel';
require __DIR__ . '/includes/header.php';
?>

<section class="tarjeta">
    <h1>Hola, <?= escapar($usuario['nombre']) ?></h1>

    <h2>Datos personales</h2>
    <table class="tabla-datos">
        <tr><th>Nombre</th><td><?= escapar($usuario['nombre']) ?></td></tr>
        <tr><th>Apellido</th><td><?= escapar($usuario['apellido']) ?></td></tr>
        <tr><th>DNI</th><td><?= escapar($usuario['dni']) ?></td></tr>
        <tr><th>Correo electrónico</th><td><?= escapar($usuario['email']) ?></td></tr>
        <tr><th>Teléfono</th><td><?= dato_o_guion($usuario['telefono']) ?></td></tr>
        <tr><th>Edad</th><td><?= dato_o_guion($usuario['edad']) ?></td></tr>
        <tr><th>Rol</th><td><?= escapar($usuario['rol']) ?></td></tr>
        <tr><th>Miembro desde</th><td><?= escapar($usuario['creado_en']) ?></td></tr>
    </table>

    <h2>Tus gustos de fútbol</h2>
    <table class="tabla-datos">
        <tr><th>Posición favorita</th><td><?= dato_o_guion($usuario['posicion_favorita']) ?></td></tr>
        <tr><th>Competencias que conocés</th><td><?= dato_o_guion(str_replace(',', ', ', (string) $usuario['competencias'])) ?></td></tr>
        <tr><th>¿Te gusta mirar fútbol?</th><td><?= dato_o_guion($usuario['mira_futbol']) ?></td></tr>
        <tr><th>Tipo de fútbol favorito</th><td><?= dato_o_guion($usuario['tipo_futbol']) ?></td></tr>
        <tr><th>Jugadores en cancha</th><td><?= dato_o_guion($usuario['cantidad_jugadores']) ?></td></tr>
        <tr><th>Jugador favorito</th><td><?= dato_o_guion($usuario['jugador_favorito']) ?></td></tr>
        <tr><th>Equipo favorito</th><td><?= dato_o_guion($usuario['equipo_favorito']) ?></td></tr>
    </table>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>