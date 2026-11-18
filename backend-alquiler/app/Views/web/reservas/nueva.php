<?php
/**
 * Nueva reserva en dos pasos (sin JavaScript):
 * 1. Formulario GET con las fechas.
 * 2. Con fechas validas, formulario POST con cliente y vehiculos disponibles.
 *
 * @var \App\Views\HtmlView $this
 * @var array<string, mixed> $datos
 * @var array<string, string> $errores
 * @var iterable<\App\Models\Cliente> $clientes
 * @var iterable<\App\Models\Vehiculo>|null $disponibles null si aun no se eligieron fechas
 */

use App\Services\DisponibilidadService;
use App\Views\Formato;

$inicio = (string) ($datos['fecha_inicio'] ?? '');
$fin = (string) ($datos['fecha_fin'] ?? '');
?>
<section class="pagina pagina--angosta">
    <header class="pagina__cabecera">
        <div>
            <h1>Nueva reserva</h1>
            <p class="texto-suave">Elija las fechas para ver los vehículos disponibles.</p>
        </div>
    </header>

    <form class="formulario" method="get" action="<?= $this->e($this->url('/reservas/nueva')) ?>">
        <?php if (!empty($datos['vehiculo_id'])): ?>
            <input type="hidden" name="vehiculo_id" value="<?= $this->e($datos['vehiculo_id']) ?>">
        <?php endif; ?>
        <h2>1. Periodo</h2>
        <div class="rejilla-campos">
            <?= $this->parcial('_campo', ['nombre' => 'fecha_inicio', 'etiqueta' => 'Fecha de inicio *', 'tipo' => 'date', 'valor' => $inicio, 'errores' => $errores, 'atributos' => ['required' => true, 'min' => DisponibilidadService::hoy()]]) ?>
            <?= $this->parcial('_campo', ['nombre' => 'fecha_fin', 'etiqueta' => 'Fecha de fin *', 'tipo' => 'date', 'valor' => $fin, 'errores' => $errores, 'atributos' => ['required' => true, 'min' => DisponibilidadService::hoy()]]) ?>
        </div>
        <div class="formulario__pie">
            <button type="submit" class="btn btn--secundario">Ver vehículos disponibles</button>
        </div>
    </form>

    <?php if ($disponibles !== null): ?>
        <form class="formulario" method="post" action="<?= $this->e($this->url('/reservas')) ?>">
            <?= $this->csrf() ?>
            <input type="hidden" name="fecha_inicio" value="<?= $this->e($inicio) ?>">
            <input type="hidden" name="fecha_fin" value="<?= $this->e($fin) ?>">

            <h2>2. Cliente y vehículo</h2>
            <p class="texto-suave">
                Del <?= $this->e(Formato::fecha($inicio)) ?> al <?= $this->e(Formato::fecha($fin)) ?>:
                <?= count($disponibles) ?> vehículo(s) disponible(s).
            </p>

            <?php if (isset($errores['general'])): ?>
                <p class="alerta alerta--error" role="alert"><?= $this->e($errores['general']) ?></p>
            <?php endif; ?>

            <div class="rejilla-campos">
                <div class="campo campo--ancho">
                    <label class="campo__etiqueta" for="campo-cliente_id">Cliente *</label>
                    <select class="campo__control" id="campo-cliente_id" name="cliente_id" required>
                        <option value="">Seleccione un cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <?php $sinLicencia = trim((string) $cliente->numero_licencia) === ''; ?>
                            <option value="<?= $this->e($cliente->id) ?>"
                                <?= $sinLicencia ? ' disabled' : '' ?>
                                <?= (string) $cliente->id === (string) ($datos['cliente_id'] ?? '') ? ' selected' : '' ?>>
                                <?= $this->e($cliente->nombre) ?><?= $sinLicencia ? ' (sin licencia)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errores['cliente_id'])): ?>
                        <small class="form-error"><?= $this->e($errores['cliente_id']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="campo campo--ancho">
                    <label class="campo__etiqueta" for="campo-vehiculo_id">Vehículo disponible *</label>
                    <select class="campo__control" id="campo-vehiculo_id" name="vehiculo_id" required>
                        <option value=""><?= count($disponibles) === 0 ? 'No hay vehículos disponibles en esas fechas' : 'Seleccione un vehículo' ?></option>
                        <?php foreach ($disponibles as $vehiculo): ?>
                            <option value="<?= $this->e($vehiculo->id) ?>"
                                <?= (string) $vehiculo->id === (string) ($datos['vehiculo_id'] ?? '') ? ' selected' : '' ?>>
                                <?= $this->e(Formato::vehiculo($vehiculo)) ?> · <?= $this->e($vehiculo->categoria ?: 'Sin categoría') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errores['vehiculo_id'])): ?>
                        <small class="form-error"><?= $this->e($errores['vehiculo_id']) ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <div class="formulario__pie">
                <a class="btn btn--secundario" href="<?= $this->e($this->url('/reservas')) ?>">Cancelar</a>
                <button type="submit" class="btn btn--primario">Reservar</button>
            </div>
        </form>
    <?php endif; ?>
</section>
