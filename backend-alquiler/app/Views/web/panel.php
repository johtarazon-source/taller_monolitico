<?php
/**
 * Panel principal: indicadores, reservas recientes y disponibilidad de la flota.
 *
 * @var \App\Views\HtmlView $this
 * @var array<string, mixed> $resumen
 */

use App\Views\Formato;

$v = $resumen['vehiculos'];
$vencidas = count($resumen['vencidas']);
$porcentaje = static fn (int $parte, int $total): int => $total > 0 ? (int) round($parte / $total * 100) : 0;
$porcentajeDisponible = $porcentaje($v['disponible'], $v['total']);
$deVehiculos = 'de ' . $v['total'] . ($v['total'] === 1 ? ' vehículo' : ' vehículos');

$tarjetas = [
    [
        'variante' => 'disponibles',
        'etiqueta' => 'Disponibles',
        'valor' => $v['disponible'],
        'detalle' => $deVehiculos,
        'porcentaje' => $porcentajeDisponible,
    ],
    [
        'variante' => 'rentados',
        'etiqueta' => 'Rentados',
        'valor' => $v['alquilado'],
        'detalle' => $deVehiculos,
        'porcentaje' => $porcentaje($v['alquilado'], $v['total']),
    ],
    [
        'variante' => 'clientes',
        'etiqueta' => 'Clientes',
        'valor' => $resumen['clientes'],
        'detalle' => $resumen['clientes_activos'] . ' con reserva activa',
        'porcentaje' => $porcentaje($resumen['clientes_activos'], $resumen['clientes']),
    ],
    [
        'variante' => 'reservas',
        'etiqueta' => 'Reservas',
        'valor' => $resumen['reservas_activas'],
        'detalle' => 'activas · ' . $resumen['reservas_total'] . ' en total',
        'porcentaje' => $porcentaje($resumen['reservas_activas'], $resumen['reservas_total']),
    ],
];
?>
<section class="pagina panel">
    <header class="panel__hero">
        <h1 class="panel__titulo">Panel de Gestor</h1>
        <p class="panel__subtitulo">Situación de la flota al <?= $this->e(Formato::fecha($resumen['fecha'])) ?></p>
        <div class="panel__acciones">
            <a class="btn btn--cta btn--grande" href="<?= $this->e($this->url('/reservas/nueva')) ?>">
                <span class="btn__icono" aria-hidden="true">
                    <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14" /></svg>
                </span>
                Nueva reserva
            </a>
            <?php if ($vencidas > 0): ?>
                <a class="chip chip--alerta" href="<?= $this->e($this->url('/reservas', ['estado' => 'activa'])) ?>">
                    <?= $this->parcial('_icono', ['nombre' => 'alerta']) ?>
                    <?= $this->e($vencidas) ?> <?= $vencidas === 1 ? 'devolución vencida' : 'devoluciones vencidas' ?>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <div class="panel__kpis">
        <?php foreach ($tarjetas as $tarjeta): ?>
            <?= $this->parcial('_kpi', $tarjeta) ?>
        <?php endforeach; ?>
    </div>

    <div class="panel__escenario">
        <?= $this->parcial('_mapa') ?>
        <img class="panel__auto" src="<?= $this->e($this->url('/img/hero-vehiculo.png', ['v' => @filemtime(__DIR__ . '/../../../public/img/hero-vehiculo.png')])) ?>" alt="" width="1320" height="733" decoding="async">
    </div>

    <section class="panel__recientes" aria-labelledby="titulo-recientes">
        <h2 id="titulo-recientes" class="panel__seccion">Reservas recientes</h2>
        <?php if (count($resumen['recientes']) === 0): ?>
            <?= $this->parcial('_vacio', [
                'icono' => 'M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM3 10h18M8 3v4M16 3v4M12 13v5M9.5 15.5h5',
                'titulo' => 'Aún no hay reservas.',
                'texto' => 'Crea tu primera reserva haciendo clic en "Nueva reserva".',
            ]) ?>
        <?php else: ?>
            <ul class="lista-reservas">
                <?php foreach ($resumen['recientes'] as $reserva): ?>
                    <?= $this->parcial('_fila_reserva', ['reserva' => $reserva]) ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="panel__flota" aria-labelledby="titulo-flota">
        <h2 id="titulo-flota" class="panel__seccion">Vehículos</h2>
        <?php if ($v['total'] === 0): ?>
            <?= $this->parcial('_vacio', [
                'icono' => 'M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11M3 11h18v5a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-1H7v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z',
                'titulo' => 'Aún no hay vehículos registrados.',
                'texto' => 'Agrega tu primer vehículo para comenzar a gestionar tu flota.',
            ]) ?>
        <?php else: ?>
            <?= $this->parcial('_donut', [
                'porcentaje' => $porcentajeDisponible,
                'disponibles' => $v['disponible'],
                'total' => $v['total'],
            ]) ?>
            <p class="panel__flota-resumen"><?= $this->e($v['disponible']) ?> de <?= $this->e($v['total']) ?> vehículos disponibles</p>
            <ul class="leyenda">
                <li><span class="leyenda__punto leyenda__punto--alquilado" aria-hidden="true"></span>Alquilados <strong><?= $this->e($v['alquilado']) ?></strong></li>
                <li><span class="leyenda__punto leyenda__punto--mantenimiento" aria-hidden="true"></span>En mantenimiento <strong><?= $this->e($v['mantenimiento']) ?></strong></li>
            </ul>
        <?php endif; ?>
    </section>

    <?php if ($resumen['marcas'] !== []): ?>
        <ul class="panel__marcas" aria-label="Marcas de la flota">
            <?php foreach ($resumen['marcas'] as $marca): ?>
                <li><?= $this->e($marca) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
