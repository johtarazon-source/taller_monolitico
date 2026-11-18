<?php
/**
 * Historial de alquileres de un vehiculo o de un cliente.
 *
 * @var \App\Views\HtmlView $this
 * @var 'vehiculo'|'cliente' $tipoHistorial
 * @var \App\Models\Vehiculo|\App\Models\Cliente $propietarioHistorial
 * @var \Illuminate\Support\Collection<int, \App\Models\Reserva> $reservas
 */

use App\Views\Formato;

$esVehiculo = $tipoHistorial === 'vehiculo';
$nombreEntidad = $esVehiculo ? Formato::vehiculo($propietarioHistorial) : $propietarioHistorial->nombre;
$diasAlquilado = $reservas
    ->where('estado', '!=', 'cancelada')
    ->sum(fn ($reserva) => Formato::dias($reserva->fecha_inicio, $reserva->fecha_fin));
?>
<section class="pagina">
    <header class="pagina__cabecera">
        <div>
            <p><a href="<?= $this->escapar($this->url($esVehiculo ? '/vehiculos' : '/clientes')) ?>">Volver a <?= $esVehiculo ? 'Vehículos' : 'Clientes' ?></a></p>
            <h1>Historial: <?= $this->escapar($nombreEntidad) ?></h1>
            <p class="texto-suave">
                <?php if ($esVehiculo): ?>
                    <?= $this->escapar($propietarioHistorial->categoria ?: 'Sin categoría') ?> · <?= Formato::insignia($propietarioHistorial->estado) ?>
                <?php else: ?>
                    Licencia: <?= $this->escapar($propietarioHistorial->numero_licencia ?: 'sin registrar') ?>
                    <?php if ($propietarioHistorial->correo || $propietarioHistorial->telefono): ?>
                        · <?= $this->escapar($propietarioHistorial->correo ?: $propietarioHistorial->telefono) ?>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
        </div>
    </header>

    <div class="tarjetas-resumen">
        <?= $this->parcial('_tarjeta_dato', ['etiqueta' => 'Reservas', 'valor' => $reservas->count()]) ?>
        <?= $this->parcial('_tarjeta_dato', ['etiqueta' => 'Activas', 'valor' => $reservas->where('estado', 'activa')->count(), 'variante' => 'alquilado']) ?>
        <?= $this->parcial('_tarjeta_dato', ['etiqueta' => 'Completadas', 'valor' => $reservas->where('estado', 'completada')->count(), 'variante' => 'disponible']) ?>
        <?= $this->parcial('_tarjeta_dato', ['etiqueta' => 'Canceladas', 'valor' => $reservas->where('estado', 'cancelada')->count()]) ?>
        <?= $this->parcial('_tarjeta_dato', ['etiqueta' => 'Días de alquiler', 'valor' => $diasAlquilado]) ?>
    </div>

    <?php if ($reservas->isEmpty()): ?>
        <p class="estado-vacio">Todavía no hay alquileres registrados.</p>
    <?php else: ?>
        <?= $this->parcial('_tabla_reservas', [
            'reservas' => $reservas,
            'conCliente' => $esVehiculo,
            'conVehiculo' => !$esVehiculo,
            'conAcciones' => false,
        ]) ?>
    <?php endif; ?>
</section>
