<?php
/**
 * @var \App\Views\HtmlView $this
 * @var string $inicio
 * @var string $fin
 * @var array{fecha_inicio: string, fecha_fin: string, vehiculos: iterable<\App\Models\Vehiculo>}|null $resultado
 * @var array<string, string> $errores
 */

use App\Views\Formato;
?>
<section class="pagina">
    <header class="pagina__cabecera">
        <div>
            <h1>Disponibilidad</h1>
            <p class="texto-suave">Vehículos que se pueden reservar en un periodo.</p>
        </div>
    </header>

    <form class="filtros" method="get" action="<?= $this->e($this->url('/disponibilidad')) ?>">
        <div class="campo filtros__campo">
            <label class="campo__etiqueta" for="d-inicio">Desde</label>
            <input class="campo__control" id="d-inicio" name="fecha_inicio" type="date" required value="<?= $this->e($inicio) ?>">
            <?php if (isset($errores['fecha_inicio'])): ?>
                <small class="form-error"><?= $this->e($errores['fecha_inicio']) ?></small>
            <?php endif; ?>
        </div>
        <div class="campo filtros__campo">
            <label class="campo__etiqueta" for="d-fin">Hasta</label>
            <input class="campo__control" id="d-fin" name="fecha_fin" type="date" required value="<?= $this->e($fin) ?>">
            <?php if (isset($errores['fecha_fin'])): ?>
                <small class="form-error"><?= $this->e($errores['fecha_fin']) ?></small>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn--primario">Consultar</button>
    </form>

    <?php if ($resultado !== null): ?>
        <?php
        $desde = $resultado['fecha_inicio'];
        $hasta = $resultado['fecha_fin'];
        $rango = $desde === $hasta
            ? 'el ' . Formato::fecha($desde)
            : 'del ' . Formato::fecha($desde) . ' al ' . Formato::fecha($hasta);
        ?>
        <p class="texto-suave"><?= count($resultado['vehiculos']) ?> vehículo(s) disponible(s) <?= $this->e($rango) ?>.</p>

        <?php if (count($resultado['vehiculos']) === 0): ?>
            <p class="estado-vacio">No hay vehículos disponibles en ese periodo.</p>
        <?php else: ?>
            <div class="rejilla-tarjetas">
                <?php foreach ($resultado['vehiculos'] as $vehiculo): ?>
                    <?= $this->parcial('_tarjeta_vehiculo', [
                        'vehiculo' => $vehiculo,
                        // El estado actual puede ser "alquilado" si se libera antes del periodo consultado.
                        'nota' => $vehiculo->estado === 'alquilado' ? 'Hoy está alquilado; se libera antes de esa fecha.' : null,
                        'enlaces' => [[
                            'texto' => 'Reservar',
                            'ruta' => '/reservas/nueva',
                            'query' => ['fecha_inicio' => $desde, 'fecha_fin' => $hasta, 'vehiculo_id' => $vehiculo->id],
                            'clase' => 'btn--primario',
                        ]],
                    ]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
