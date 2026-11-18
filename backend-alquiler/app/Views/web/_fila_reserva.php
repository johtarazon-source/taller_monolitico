<?php
/**
 * Tarjeta de una reserva reciente: vehiculo, cliente, fechas y estado.
 * Enlaza al historial del vehiculo (si el vehiculo ya no existe, no hay enlace).
 *
 * @var \App\Views\HtmlView $this
 * @var \App\Models\Reserva $reserva
 */

use App\Views\Formato;

$vehiculo = $reserva->vehiculo;
$cantidadDias = Formato::dias($reserva->fecha_inicio, $reserva->fecha_fin);
$elementoHtml = $vehiculo !== null ? 'a' : 'div';
$atributoHref = $vehiculo !== null ? ' href="' . $this->escapar($this->url("/vehiculos/{$vehiculo->id}/historial")) . '"' : '';
?>
<li class="fila-reserva">
    <<?= $elementoHtml ?> class="fila-reserva__contenido"<?= $atributoHref ?>>
        <?php if ($vehiculo !== null): ?>
            <span class="fila-reserva__enlace" aria-hidden="true">
                <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10M7 17 17 7" /></svg>
            </span>
        <?php endif; ?>
        <div class="fila-reserva__datos">
            <strong class="fila-reserva__titulo"><?= $this->escapar($vehiculo ? "{$vehiculo->marca} {$vehiculo->modelo}" : 'Vehículo eliminado') ?></strong>
            <span class="fila-reserva__detalle"><?= $this->escapar($reserva->cliente?->nombre ?? 'Cliente eliminado') ?></span>
            <span class="fila-reserva__detalle">
                <?= $this->escapar(Formato::fechaCorta($reserva->fecha_inicio)) ?> → <?= $this->escapar(Formato::fechaCorta($reserva->fecha_fin)) ?>
                · <?= $this->escapar($cantidadDias) ?> <?= $cantidadDias === 1 ? 'día' : 'días' ?>
            </span>
            <span class="fila-reserva__estado"><?= Formato::insignia(Formato::estadoReserva($reserva)) ?></span>
        </div>
    </<?= $elementoHtml ?>>
</li>
