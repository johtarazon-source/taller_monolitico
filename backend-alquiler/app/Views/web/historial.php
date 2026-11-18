<?php
/**
 * Historial de alquileres de un vehiculo o de un cliente.
 *
 * @var \App\Views\HtmlView $this
 * @var 'vehiculo'|'cliente' $tipo
 * @var \App\Models\Vehiculo|\App\Models\Cliente $entidad
 * @var \Illuminate\Support\Collection<int, \App\Models\Reserva> $reservas
 */

use App\Views\Formato;

$esVehiculo = $tipo === 'vehiculo';
$nombre = $esVehiculo ? Formato::vehiculo($entidad) : $entidad->nombre;
$diasAlquilado = $reservas
    ->where('estado', '!=', 'cancelada')
    ->sum(fn ($r) => Formato::dias($r->fecha_inicio, $r->fecha_fin));
?>
<section class="pagina">
    <header class="pagina__cabecera">
        <div>
            <p><a href="<?= $this->e($this->url($esVehiculo ? '/vehiculos' : '/clientes')) ?>">Volver a <?= $esVehiculo ? 'Vehículos' : 'Clientes' ?></a></p>
            <h1>Historial: <?= $this->e($nombre) ?></h1>
            <p class="texto-suave">
                <?php if ($esVehiculo): ?>
                    <?= $this->e($entidad->categoria ?: 'Sin categoría') ?> · <?= Formato::badge($entidad->estado) ?>
                <?php else: ?>
                    Licencia: <?= $this->e($entidad->numero_licencia ?: 'sin registrar') ?>
                    <?php if ($entidad->correo || $entidad->telefono): ?>
                        · <?= $this->e($entidad->correo ?: $entidad->telefono) ?>
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
