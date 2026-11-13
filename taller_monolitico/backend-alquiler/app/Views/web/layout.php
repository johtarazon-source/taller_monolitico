<?php
/**
 * Layout comun de todas las paginas.
 *
 * @var \App\Views\HtmlView $this
 * @var string $contenido HTML ya renderizado de la pagina
 * @var string|null $titulo
 * @var string|null $seccion
 */

use App\Support\Sesion;

$usuario = Sesion::usuario();
$mensajes = Sesion::consumirFlash();
$seccion ??= '';
$secciones = [
    'panel' => ['/', 'Panel'],
    'vehiculos' => ['/vehiculos', 'Vehículos'],
    'clientes' => ['/clientes', 'Clientes'],
    'reservas' => ['/reservas', 'Reservas'],
    'disponibilidad' => ['/disponibilidad', 'Disponibilidad'],
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e(isset($titulo) ? "{$titulo} | Gestor de Alquiler" : 'Gestor de Alquiler de Vehículos') ?></title>
    <link rel="stylesheet" href="<?= $this->e($this->url('/css/styles.css')) ?>">
</head>
<body class="<?= $usuario === null ? 'app app--login' : 'app' ?>">
<?php if ($usuario !== null): ?>
    <header class="app__cabecera">
        <div class="barra">
            <a class="barra__marca" href="<?= $this->e($this->url('/')) ?>">Gestor de Alquiler</a>
            <nav class="barra__nav" aria-label="Secciones">
                <?php foreach ($secciones as $clave => [$ruta, $texto]): ?>
                    <?php $activa = $clave === $seccion; ?>
                    <a class="<?= $activa ? 'barra__enlace barra__enlace--activo' : 'barra__enlace' ?>"
                       href="<?= $this->e($this->url($ruta)) ?>"<?= $activa ? ' aria-current="page"' : '' ?>><?= $this->e($texto) ?></a>
                <?php endforeach; ?>
            </nav>
            <form method="post" action="<?= $this->e($this->url('/logout')) ?>">
                <?= $this->csrf() ?>
                <button type="submit" class="btn btn--secundario btn--chico">Cerrar sesión</button>
            </form>
        </div>
    </header>
<?php endif; ?>

    <main class="app__contenido">
        <?php foreach ($mensajes as $flash): ?>
            <p class="alerta alerta--<?= $this->e($flash['tipo']) ?>" role="status"><?= $this->e($flash['mensaje']) ?></p>
        <?php endforeach; ?>

        <?= $contenido ?>
    </main>

    <footer class="app__pie">Gestor de Alquiler de Vehículos · Taller monolítico</footer>
</body>
</html>
