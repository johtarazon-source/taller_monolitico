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
$mensajesTemporales = Sesion::consumirMensajesTemporales();
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
    <meta name="color-scheme" content="dark">
    <meta name="theme-color" content="#0f1114">
    <title><?= $this->escapar(isset($titulo) ? "{$titulo} | Gestor de Alquiler" : 'Gestor de Alquiler de Vehículos') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?= $this->escapar($this->url('/css/styles.css', ['version' => @filemtime(__DIR__ . '/../../../public/css/styles.css')])) ?>">
</head>
<body class="<?= $usuario === null ? 'app aplicacion--inicio-sesion' : 'app' ?>">
    <a class="saltar" href="#contenido">Saltar al contenido</a>
<?php if ($usuario !== null): ?>
    <header class="aplicacion__cabecera">
        <div class="barra">
            <a class="barra__marca" href="<?= $this->escapar($this->url('/')) ?>" aria-label="CarRent, ir al panel">
                <?= $this->parcial('_logo') ?>
            </a>
            <nav class="barra__navegacion" aria-label="Secciones">
                <?php foreach ($secciones as $clave => [$ruta, $texto]): ?>
                    <?php $activa = $clave === $seccion; ?>
                    <a class="<?= $activa ? 'barra__enlace barra__enlace--activo' : 'barra__enlace' ?>"
                       href="<?= $this->escapar($this->url($ruta)) ?>"<?= $activa ? ' aria-current="page"' : '' ?>><?= $this->escapar($texto) ?></a>
                <?php endforeach; ?>
            </nav>
            <form method="post" action="<?= $this->escapar($this->url('/logout')) ?>">
                <?= $this->csrf() ?>
                <button type="submit" class="boton boton--secundario boton--chico">Cerrar sesión</button>
            </form>
        </div>
    </header>
<?php endif; ?>

    <main class="aplicacion__contenido" id="contenido" tabindex="-1">
        <?php foreach ($mensajesTemporales as $mensajeTemporal): ?>
            <p class="alerta alerta--<?= $this->escapar($mensajeTemporal['tipo']) ?>" role="status">
                <?= $this->parcial('_icono', ['nombreIcono' => $mensajeTemporal['tipo'] === 'exito' ? 'exito' : 'error']) ?>
                <span><?= $this->escapar($mensajeTemporal['mensaje']) ?></span>
            </p>
        <?php endforeach; ?>

        <?= $contenido ?>
    </main>
</body>
</html>
