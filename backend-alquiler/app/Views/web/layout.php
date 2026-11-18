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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?= $this->e($this->url('/css/styles.css', ['v' => @filemtime(__DIR__ . '/../../../public/css/styles.css')])) ?>">
</head>
<body class="<?= $usuario === null ? 'app app--login' : 'app' ?>">
    <a class="saltar" href="#contenido">Saltar al contenido</a>
<?php if ($usuario !== null): ?>
    <header class="app__cabecera">
        <div class="barra">
            <a class="barra__marca" href="<?= $this->e($this->url('/')) ?>" aria-label="CarRent, ir al panel">
                <?= $this->parcial('_logo') ?>
            </a>
            <nav class="barra__nav" aria-label="Secciones">
                <?php foreach ($secciones as $clave => [$ruta, $texto]): ?>
                    <?php $activa = $clave === $seccion; ?>
                    <a class="<?= $activa ? 'barra__enlace barra__enlace--activo' : 'barra__enlace' ?>"
                       href="<?= $this->e($this->url($ruta)) ?>"<?= $activa ? ' aria-current="page"' : '' ?>><?= $this->e($texto) ?></a>
                <?php endforeach; ?>
            </nav>
            <form method="post" action="<?= $this->e($this->url('/logout')) ?>">
                <?= $this->csrf() ?>
                <button type="submit" class="btn btn--texto btn--chico">Cerrar sesión</button>
            </form>
        </div>
    </header>
<?php endif; ?>

    <main class="app__contenido" id="contenido" tabindex="-1">
        <?php foreach ($mensajes as $flash): ?>
            <p class="alerta alerta--<?= $this->e($flash['tipo']) ?>" role="status">
                <?= $this->parcial('_icono', ['nombre' => $flash['tipo'] === 'exito' ? 'exito' : 'error']) ?>
                <span><?= $this->e($flash['mensaje']) ?></span>
            </p>
        <?php endforeach; ?>

        <?= $contenido ?>
    </main>

    <footer class="app__pie">CarRent · Gestor de Alquiler de Vehículos</footer>
</body>
</html>
