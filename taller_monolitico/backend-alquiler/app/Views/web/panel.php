<?php
/**
 * @var \App\Views\HtmlView $this
 * @var array<string, mixed> $resumen
 */

use App\Views\Formato;

$v = $resumen['vehiculos'];
$bloques = [
    ['Devoluciones vencidas', $resumen['vencidas'], 'fecha_fin', 'No hay devoluciones vencidas.', true],
    ['Devoluciones próximas (7 días)', $resumen['proximas_devoluciones'], 'fecha_fin', 'No hay devoluciones esta semana.', false],
    ['Salidas próximas (7 días)', $resumen['proximas_salidas'], 'fecha_inicio', 'No hay reservas que inicien esta semana.', false],
];
?>
<section class="pagina">
    <header class="pagina__cabecera">
        <div>
            <h1>Panel general</h1>
            <p class="texto-suave">Situación de la flota al <?= $this->e(Formato::fecha($resumen['fecha'])) ?></p>
        </div>
        <a class="btn btn--primario" href="<?= $this->e($this->url('/reservas/nueva')) ?>">Nueva reserva</a>
    </header>

    <div class="tarjetas-resumen">
        <?= $this->parcial('_tarjeta_dato', ['etiqueta' => 'Vehículos', 'valor' => $v['total']]) ?>
        <?= $this->parcial('_tarjeta_dato', ['etiqueta' => 'Disponibles', 'valor' => $v['disponible'], 'variante' => 'disponible']) ?>
        <?= $this->parcial('_tarjeta_dato', ['etiqueta' => 'Alquilados', 'valor' => $v['alquilado'], 'variante' => 'alquilado']) ?>
        <?= $this->parcial('_tarjeta_dato', ['etiqueta' => 'En mantenimiento', 'valor' => $v['mantenimiento'], 'variante' => 'mantenimiento']) ?>
        <?= $this->parcial('_tarjeta_dato', ['etiqueta' => 'Clientes', 'valor' => $resumen['clientes']]) ?>
        <?= $this->parcial('_tarjeta_dato', ['etiqueta' => 'Reservas activas', 'valor' => $resumen['reservas_activas']]) ?>
    </div>

    <div class="reporte-columnas">
        <?php foreach ($bloques as [$titulo, $reservas, $campoFecha, $vacio, $esAlerta]): ?>
            <section class="<?= $esAlerta && count($reservas) > 0 ? 'bloque bloque--alerta' : 'bloque' ?>">
                <h2><?= $this->e($titulo) ?></h2>
                <?php if (count($reservas) === 0): ?>
                    <p class="texto-suave"><?= $this->e($vacio) ?></p>
                <?php else: ?>
                    <ul class="lista-simple">
                        <?php foreach ($reservas as $reserva): ?>
                            <li class="lista-simple__item">
                                <strong><?= $this->e(Formato::vehiculo($reserva->vehiculo)) ?></strong>
                                <span class="lista-simple__detalle"><?= $this->e($reserva->cliente?->nombre) ?> · <?= $this->e(Formato::fecha($reserva->{$campoFecha})) ?></span>
                                <span class="lista-simple__estado"><?= Formato::badge(Formato::estadoReserva($reserva)) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>
</section>
