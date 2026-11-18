<?php
/**
 * Nueva reserva en dos pasos (sin JavaScript):
 * 1. Formulario GET con las fechas.
 * 2. Con fechas validas, formulario POST con cliente y vehiculos disponibles.
 *
 * @var \App\Views\HtmlView $this
 * @var array<string, mixed> $datosFormulario
 * @var array<string, string> $errores
 * @var iterable<\App\Models\Cliente> $clientes
 * @var iterable<\App\Models\Vehiculo>|null $vehiculosDisponibles null si aun no se eligieron fechas
 */

use App\Services\DisponibilidadService;
use App\Views\Formato;

$fechaInicio = (string) ($datosFormulario['fecha_inicio'] ?? '');
$fechaFin = (string) ($datosFormulario['fecha_fin'] ?? '');
?>
<section class="pagina pagina--angosta">
    <header class="pagina__cabecera">
        <div>
            <h1>Nueva reserva</h1>
            <p class="texto-suave">Elija las fechas para ver los vehículos disponibles.</p>
        </div>
    </header>

    <form class="formulario" method="get" action="<?= $this->escapar($this->url('/reservas/nueva')) ?>">
        <?php if (!empty($datosFormulario['vehiculo_id'])): ?>
            <input type="hidden" name="vehiculo_id" value="<?= $this->escapar($datosFormulario['vehiculo_id']) ?>">
        <?php endif; ?>
        <h2>1. Periodo</h2>
        <div class="rejilla-campos">
            <?= $this->parcial('_campo', ['nombreCampo' => 'fecha_inicio', 'etiqueta' => 'Fecha de inicio *', 'tipo' => 'date', 'valor' => $fechaInicio, 'errores' => $errores, 'atributos' => ['required' => true, 'min' => DisponibilidadService::hoy()]]) ?>
            <?= $this->parcial('_campo', ['nombreCampo' => 'fecha_fin', 'etiqueta' => 'Fecha de fin *', 'tipo' => 'date', 'valor' => $fechaFin, 'errores' => $errores, 'atributos' => ['required' => true, 'min' => DisponibilidadService::hoy()]]) ?>
        </div>
        <div class="formulario__pie">
            <button type="submit" class="boton boton--secundario">Ver vehículos disponibles</button>
        </div>
    </form>

    <?php if ($vehiculosDisponibles !== null): ?>
        <form class="formulario" method="post" action="<?= $this->escapar($this->url('/reservas')) ?>">
            <?= $this->csrf() ?>
            <input type="hidden" name="fecha_inicio" value="<?= $this->escapar($fechaInicio) ?>">
            <input type="hidden" name="fecha_fin" value="<?= $this->escapar($fechaFin) ?>">

            <h2>2. Cliente y vehículo</h2>
            <p class="texto-suave">
                Del <?= $this->escapar(Formato::fecha($fechaInicio)) ?> al <?= $this->escapar(Formato::fecha($fechaFin)) ?>:
                <?= count($vehiculosDisponibles) ?> vehículo(s) disponible(s).
            </p>

            <?php if (isset($errores['general'])): ?>
                <p class="alerta alerta--error" role="alert"><?= $this->escapar($errores['general']) ?></p>
            <?php endif; ?>

            <div class="rejilla-campos">
                <div class="campo campo--ancho">
                    <label class="campo__etiqueta" for="campo-cliente_id">Cliente *</label>
                    <select class="campo__control" id="campo-cliente_id" name="cliente_id" required>
                        <option value="">Seleccione un cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <?php $sinLicencia = trim((string) $cliente->numero_licencia) === ''; ?>
                            <option value="<?= $this->escapar($cliente->id) ?>"
                                <?= $sinLicencia ? ' disabled' : '' ?>
                                <?= (string) $cliente->id === (string) ($datosFormulario['cliente_id'] ?? '') ? ' selected' : '' ?>>
                                <?= $this->escapar($cliente->nombre) ?><?= $sinLicencia ? ' (sin licencia)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errores['cliente_id'])): ?>
                        <small class="mensaje-error"><?= $this->escapar($errores['cliente_id']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="campo campo--ancho">
                    <label class="campo__etiqueta" for="campo-vehiculo_id">Vehículo disponible *</label>
                    <select class="campo__control" id="campo-vehiculo_id" name="vehiculo_id" required>
                        <option value=""><?= count($vehiculosDisponibles) === 0 ? 'No hay vehículos disponibles en esas fechas' : 'Seleccione un vehículo' ?></option>
                        <?php foreach ($vehiculosDisponibles as $vehiculo): ?>
                            <option value="<?= $this->escapar($vehiculo->id) ?>"
                                <?= (string) $vehiculo->id === (string) ($datosFormulario['vehiculo_id'] ?? '') ? ' selected' : '' ?>>
                                <?= $this->escapar(Formato::vehiculo($vehiculo)) ?> · <?= $this->escapar($vehiculo->categoria ?: 'Sin categoría') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errores['vehiculo_id'])): ?>
                        <small class="mensaje-error"><?= $this->escapar($errores['vehiculo_id']) ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <div class="formulario__pie">
                <a class="boton boton--secundario" href="<?= $this->escapar($this->url('/reservas')) ?>">Cancelar</a>
                <button type="submit" class="boton boton--primario">Reservar</button>
            </div>
        </form>
    <?php endif; ?>
</section>
