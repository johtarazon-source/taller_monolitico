<?php
/**
 * Tabla de reservas. Se usa en el listado y en los historiales.
 *
 * @var \App\Views\HtmlView $this
 * @var iterable<\App\Models\Reserva> $reservas
 * @var bool|null $conCliente
 * @var bool|null $conVehiculo
 * @var bool|null $conAcciones
 */

use App\Models\Reserva;
use App\Services\DisponibilidadService;
use App\Views\Formato;

$conCliente ??= true;
$conVehiculo ??= true;
$conAcciones ??= true;
$hoy = DisponibilidadService::hoy();
?>
<div class="tabla-contenedor">
    <table class="tabla">
        <thead>
            <tr>
                <th>#</th>
                <?php if ($conCliente): ?><th>Cliente</th><?php endif; ?>
                <?php if ($conVehiculo): ?><th>Vehículo</th><?php endif; ?>
                <th>Desde</th>
                <th>Hasta</th>
                <th>Días</th>
                <th>Estado</th>
                <?php if ($conAcciones): ?><th><span class="solo-lectores-pantalla">Acciones</span></th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservas as $reserva): ?>
                <?php $estadoVisible = Formato::estadoReserva($reserva); ?>
                <tr<?= $estadoVisible === 'vencida' ? ' class="tabla__fila--alerta"' : '' ?>>
                    <td data-titulo="#"><?= $this->escapar($reserva->id) ?></td>
                    <?php if ($conCliente): ?>
                        <td data-titulo="Cliente"><?= $this->escapar($reserva->cliente?->nombre) ?></td>
                    <?php endif; ?>
                    <?php if ($conVehiculo): ?>
                        <td data-titulo="Vehículo"><?= $this->escapar(Formato::vehiculo($reserva->vehiculo)) ?></td>
                    <?php endif; ?>
                    <td data-titulo="Desde"><?= $this->escapar(Formato::fecha($reserva->fecha_inicio)) ?></td>
                    <td data-titulo="Hasta"><?= $this->escapar(Formato::fecha($reserva->fecha_fin)) ?></td>
                    <td data-titulo="Días"><?= $this->escapar(Formato::dias($reserva->fecha_inicio, $reserva->fecha_fin)) ?></td>
                    <td data-titulo="Estado"><?= Formato::insignia($estadoVisible) ?></td>
                    <?php if ($conAcciones): ?>
                        <td class="tabla__acciones">
                            <?php if ($reserva->estado === Reserva::ACTIVA): ?>
                                <?php if ($reserva->fecha_inicio <= $hoy): ?>
                                    <a class="boton boton--chico boton--primario" href="<?= $this->escapar($this->url("/reservas/{$reserva->id}/devolucion")) ?>">Registrar devolución</a>
                                <?php endif; ?>
                                <a class="boton boton--chico boton--texto-peligro" href="<?= $this->escapar($this->url("/reservas/{$reserva->id}/cancelacion")) ?>">Cancelar</a>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
