<?php
/**
 * @var \App\Views\HtmlView $this
 * @var iterable<\App\Models\Vehiculo> $vehiculos
 * @var array<string, mixed> $filtros
 */

$opcionesEstado = ['' => 'Todos', 'disponible' => 'Disponible', 'alquilado' => 'Alquilado', 'mantenimiento' => 'Mantenimiento'];
$estadoFiltro = (string) ($filtros['estado'] ?? '');
?>
<section class="pagina">
    <header class="pagina__cabecera">
        <div>
            <h1>Vehículos</h1>
            <p class="texto-suave">Flota registrada para alquiler.</p>
        </div>
        <a class="boton boton--primario" href="<?= $this->escapar($this->url('/vehiculos/nuevo')) ?>">Nuevo vehículo</a>
    </header>

    <form class="filtros" method="get" action="<?= $this->escapar($this->url('/vehiculos')) ?>" role="search">
        <div class="campo filtros__campo">
            <label class="campo__etiqueta" for="vehiculos-buscar">Buscar</label>
            <input class="campo__control" id="vehiculos-buscar" name="buscar" type="search"
                   placeholder="Marca, modelo o categoría" value="<?= $this->escapar($filtros['buscar'] ?? '') ?>">
        </div>
        <div class="campo filtros__campo">
            <label class="campo__etiqueta" for="vehiculos-estado">Estado</label>
            <select class="campo__control" id="vehiculos-estado" name="estado">
                <?php foreach ($opcionesEstado as $valor => $texto): ?>
                    <option value="<?= $this->escapar($valor) ?>"<?= $valor === $estadoFiltro ? ' selected' : '' ?>><?= $this->escapar($texto) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="boton boton--secundario">Filtrar</button>
    </form>

    <?php if (count($vehiculos) === 0): ?>
        <p class="estado-vacio">No hay vehículos que coincidan. Registre uno con "Nuevo vehículo".</p>
    <?php else: ?>
        <div class="rejilla-tarjetas">
            <?php foreach ($vehiculos as $vehiculo): ?>
                <?= $this->parcial('_tarjeta_vehiculo', [
                    'vehiculo' => $vehiculo,
                    'enlaces' => [
                        ['texto' => 'Editar', 'ruta' => "/vehiculos/{$vehiculo->id}/editar", 'claseCss' => 'boton--secundario'],
                        ['texto' => 'Historial', 'ruta' => "/vehiculos/{$vehiculo->id}/historial", 'claseCss' => 'boton--secundario'],
                        ['texto' => 'Eliminar', 'ruta' => "/vehiculos/{$vehiculo->id}/eliminar", 'claseCss' => 'boton--texto-peligro'],
                    ],
                ]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
