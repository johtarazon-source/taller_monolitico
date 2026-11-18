<?php
/**
 * Panel principal: reservas recientes, resumen de la flota y vehiculos.
 *
 * @var \App\Views\HtmlView $this
 * @var array<string, mixed> $resumen
 */

use App\Views\Formato;

$conteoVehiculos = $resumen['vehiculos'];
$cantidadVencidas = count($resumen['vencidas']);
$calcularPorcentaje = static fn (int $parte, int $total): int => $total > 0 ? (int) round($parte / $total * 100) : 0;
$porcentajeDisponible = $calcularPorcentaje($conteoVehiculos['disponible'], $conteoVehiculos['total']);
$detalleVehiculos = 'de ' . $conteoVehiculos['total'] . ($conteoVehiculos['total'] === 1 ? ' vehículo' : ' vehículos');

// Iconos de trazo (viewBox 24x24) usados en esta pagina.
$trazosIconos = [
    'vehiculo' => 'M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11M3 11h18v5a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-1H7v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z',
    'llave' => 'M13 15.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0zM21 2l-9.6 9.6M15.5 7.5l3 3L22 7l-3-3',
    'taller' => 'M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z',
    'panel' => 'M3 3h7v9H3zM14 3h7v5h-7zM14 12h7v9h-7zM3 16h7v5H3z',
    'flecha' => 'M7 7h10v10M7 17 17 7',
    'calendario' => 'M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM3 10h18M8 3v4M16 3v4M12 13v5M9.5 15.5h5',
];
$dibujarIcono = fn (string $trazo): string => '<svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="' . $this->escapar($trazo) . '" /></svg>';

$tarjetasIndicadores = [
    [
        'variante' => 'disponibles',
        'etiqueta' => 'Disponibles',
        'valor' => $conteoVehiculos['disponible'],
        'detalle' => $detalleVehiculos,
        'porcentaje' => $porcentajeDisponible,
        'urlDestino' => $this->url('/vehiculos', ['estado' => 'disponible']),
        'textoAccesible' => 'Ver vehículos disponibles',
    ],
    [
        'variante' => 'rentados',
        'etiqueta' => 'Rentados',
        'valor' => $conteoVehiculos['alquilado'],
        'detalle' => $detalleVehiculos,
        'porcentaje' => $calcularPorcentaje($conteoVehiculos['alquilado'], $conteoVehiculos['total']),
        'urlDestino' => $this->url('/vehiculos', ['estado' => 'alquilado']),
        'textoAccesible' => 'Ver vehículos rentados',
    ],
    [
        'variante' => 'clientes',
        'etiqueta' => 'Clientes',
        'valor' => $resumen['clientes'],
        'detalle' => $resumen['clientes_activos'] . ' con reserva activa',
        'porcentaje' => $calcularPorcentaje($resumen['clientes_activos'], $resumen['clientes']),
        'urlDestino' => $this->url('/clientes'),
        'textoAccesible' => 'Ver clientes',
    ],
    [
        'variante' => 'reservas',
        'etiqueta' => 'Reservas',
        'valor' => $resumen['reservas_activas'],
        'detalle' => 'activas · ' . $resumen['reservas_total'] . ' en total',
        'porcentaje' => $calcularPorcentaje($resumen['reservas_activas'], $resumen['reservas_total']),
        'urlDestino' => $this->url('/reservas', ['estado' => 'activa']),
        'textoAccesible' => 'Ver reservas activas',
    ],
];
?>
<section class="pagina panel">
    <header class="panel__cabecera">
        <h1 class="panel__titulo">Panel de <strong>Gestor</strong></h1>
        <div class="panel__acciones">
            <a class="boton boton--destacado boton--grande" href="<?= $this->escapar($this->url('/reservas/nueva')) ?>">
                <span class="boton__icono" aria-hidden="true">
                    <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14" /></svg>
                </span>
                Nueva reserva
            </a>
            <?php if ($cantidadVencidas > 0): ?>
                <a class="aviso aviso--alerta" href="<?= $this->escapar($this->url('/reservas', ['estado' => 'activa'])) ?>">
                    <?= $this->parcial('_icono', ['nombreIcono' => 'alerta']) ?>
                    <?= $this->escapar($cantidadVencidas) ?> <?= $cantidadVencidas === 1 ? 'devolución vencida' : 'devoluciones vencidas' ?>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($resumen['marcas'] !== []): ?>
        <ul class="panel__marcas" aria-label="Marcas de la flota">
            <?php foreach ($resumen['marcas'] as $marca): ?>
                <li><?= $this->escapar($marca) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <section class="panel__recientes" aria-labelledby="titulo-recientes">
        <h2 id="titulo-recientes" class="panel__seccion">Reservas recientes</h2>
        <?php if (count($resumen['recientes']) === 0): ?>
            <?= $this->parcial('_vacio', [
                'trazoIcono' => $trazosIconos['calendario'],
                'titulo' => 'Aún no hay reservas.',
                'descripcion' => 'Crea tu primera reserva haciendo clic en "Nueva reserva".',
            ]) ?>
        <?php else: ?>
            <ul class="lista-reservas">
                <?php foreach ($resumen['recientes'] as $reserva): ?>
                    <?= $this->parcial('_fila_reserva', ['reserva' => $reserva]) ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="panel__resumen" aria-labelledby="titulo-resumen">
        <header class="panel__resumen-cabecera">
            <span class="icono-recuadro" aria-hidden="true"><?= $dibujarIcono($trazosIconos['panel']) ?></span>
            <h2 id="titulo-resumen">Resumen de la flota</h2>
            <a class="icono-recuadro" href="<?= $this->escapar($this->url('/vehiculos')) ?>" aria-label="Ver vehículos"><?= $dibujarIcono($trazosIconos['flecha']) ?></a>
        </header>

        <p class="panel__fecha">Situación de la flota al <span><?= $this->escapar(Formato::fecha($resumen['fecha'])) ?></span></p>

        <div class="panel__indicadores">
            <?php foreach ($tarjetasIndicadores as $tarjetaIndicador): ?>
                <?= $this->parcial('_indicador', $tarjetaIndicador) ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel__flota" aria-labelledby="titulo-flota">
        <header class="panel__flota-cabecera">
            <h2 id="titulo-flota">Vehículos</h2>
            <a class="boton boton--destacado boton--chico" href="<?= $this->escapar($this->url('/vehiculos')) ?>">Ver vehículos</a>
        </header>

        <div class="panel__escena">
            <img class="panel__auto" src="<?= $this->escapar($this->url('/img/hero-vehiculo.png', ['version' => @filemtime(__DIR__ . '/../../../public/img/hero-vehiculo.png')])) ?>" alt="" width="1320" height="733" decoding="async">
        </div>

        <?php if ($conteoVehiculos['total'] === 0): ?>
            <?= $this->parcial('_vacio', [
                'trazoIcono' => $trazosIconos['vehiculo'],
                'titulo' => 'Aún no hay vehículos registrados.',
                'descripcion' => 'Agrega tu primer vehículo para comenzar a gestionar tu flota.',
            ]) ?>
        <?php else: ?>
            <div class="panel__flota-datos">
                <?= $this->parcial('_dona', [
                    'porcentaje' => $porcentajeDisponible,
                    'disponibles' => $conteoVehiculos['disponible'],
                    'total' => $conteoVehiculos['total'],
                ]) ?>
                <div class="panel__flota-texto">
                    <span class="panel__flota-etiqueta">Disponibilidad</span>
                    <p class="panel__flota-resumen"><?= $this->escapar($conteoVehiculos['disponible']) ?> de <?= $this->escapar($conteoVehiculos['total']) ?> vehículos disponibles</p>
                </div>
            </div>

            <ul class="especificaciones" aria-label="Estado de la flota">
                <li>
                    <span class="icono-recuadro" aria-hidden="true"><?= $dibujarIcono($trazosIconos['vehiculo']) ?></span>
                    <strong><?= $this->escapar($conteoVehiculos['disponible']) ?></strong>
                    <span>Disponibles</span>
                </li>
                <li>
                    <span class="icono-recuadro" aria-hidden="true"><?= $dibujarIcono($trazosIconos['llave']) ?></span>
                    <strong><?= $this->escapar($conteoVehiculos['alquilado']) ?></strong>
                    <span>Alquilados</span>
                </li>
                <li>
                    <span class="icono-recuadro" aria-hidden="true"><?= $dibujarIcono($trazosIconos['taller']) ?></span>
                    <strong><?= $this->escapar($conteoVehiculos['mantenimiento']) ?></strong>
                    <span>En mantenimiento</span>
                </li>
            </ul>
        <?php endif; ?>
    </section>
</section>
