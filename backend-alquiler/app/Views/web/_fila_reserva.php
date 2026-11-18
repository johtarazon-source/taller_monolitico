<?php
/**
 * Fila tipo pastilla de una reserva reciente (miniatura, resumen y estado).
 *
 * @var \App\Views\HtmlView $this
 * @var \App\Models\Reserva $reserva
 */

use App\Views\Formato;

$vehiculo = $reserva->vehiculo;
$dias = Formato::dias($reserva->fecha_inicio, $reserva->fecha_fin);
?>
<li class="fila-reserva">
    <img class="fila-reserva__foto" src="<?= $this->e($this->url('/img/hero-vehiculo.png', ['v' => @filemtime(__DIR__ . '/../../../public/img/hero-vehiculo.png')])) ?>" alt="" width="72" height="48" loading="lazy" decoding="async">
    <div class="fila-reserva__datos">
        <strong class="fila-reserva__titulo"><?= $this->e($vehiculo ? "{$vehiculo->marca} {$vehiculo->modelo}" : 'Vehículo eliminado') ?></strong>
        <span class="fila-reserva__detalle">
            <?= $this->e($reserva->cliente?->nombre ?? 'Cliente eliminado') ?>
            · <?= $this->e(Formato::fechaCorta($reserva->fecha_inicio)) ?> → <?= $this->e(Formato::fechaCorta($reserva->fecha_fin)) ?>
            · <?= $this->e($dias) ?> <?= $dias === 1 ? 'día' : 'días' ?>
        </span>
    </div>
    <?= Formato::badge(Formato::estadoReserva($reserva)) ?>
</li>
