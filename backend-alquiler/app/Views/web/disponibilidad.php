<?php
/**
 * @var \App\Views\HtmlView $this
 * @var string $fechaInicio
 * @var string $fechaFin
 * @var array{fecha_inicio: string, fecha_fin: string, vehiculos: iterable<\App\Models\Vehiculo>}|null $resultadoConsulta
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

    <form class="filtros" method="get" action="<?= $this->escapar($this->url('/disponibilidad')) ?>">
        <div class="campo filtros__campo">
            <label class="campo__etiqueta" for="disponibilidad-fecha-inicio">Desde</label>
            <input class="campo__control" id="disponibilidad-fecha-inicio" name="fecha_inicio" type="date" required value="<?= $this->escapar($fechaInicio) ?>">
            <?php if (isset($errores['fecha_inicio'])): ?>
                <small class="mensaje-error"><?= $this->escapar($errores['fecha_inicio']) ?></small>
            <?php endif; ?>
        </div>
        <div class="campo filtros__campo">
            <label class="campo__etiqueta" for="disponibilidad-fecha-fin">Hasta</label>
            <input class="campo__control" id="disponibilidad-fecha-fin" name="fecha_fin" type="date" required value="<?= $this->escapar($fechaFin) ?>">
            <?php if (isset($errores['fecha_fin'])): ?>
                <small class="mensaje-error"><?= $this->escapar($errores['fecha_fin']) ?></small>
            <?php endif; ?>
        </div>
        <button type="submit" class="boton boton--primario">Consultar</button>
    </form>

    <?php if ($resultadoConsulta !== null): ?>
        <?php
        $desde = $resultadoConsulta['fecha_inicio'];
        $hasta = $resultadoConsulta['fecha_fin'];
        $textoRango = $desde === $hasta
            ? 'el ' . Formato::fecha($desde)
            : 'del ' . Formato::fecha($desde) . ' al ' . Formato::fecha($hasta);
        ?>
        <p class="texto-suave"><?= count($resultadoConsulta['vehiculos']) ?> vehículo(s) disponible(s) <?= $this->escapar($textoRango) ?>.</p>

        <?php if (count($resultadoConsulta['vehiculos']) === 0): ?>
            <p class="estado-vacio">No hay vehículos disponibles en ese periodo.</p>
        <?php else: ?>
            <div class="rejilla-tarjetas">
                <?php foreach ($resultadoConsulta['vehiculos'] as $vehiculo): ?>
                    <?= $this->parcial('_tarjeta_vehiculo', [
                        'vehiculo' => $vehiculo,
                        // El estado actual puede ser "alquilado" si se libera antes del periodo consultado.
                        'nota' => $vehiculo->estado === 'alquilado' ? 'Hoy está alquilado; se libera antes de esa fecha.' : null,
                        'enlaces' => [[
                            'texto' => 'Reservar',
                            'ruta' => '/reservas/nueva',
                            'parametros' => ['fecha_inicio' => $desde, 'fecha_fin' => $hasta, 'vehiculo_id' => $vehiculo->id],
                            'claseCss' => 'boton--primario',
                        ]],
                    ]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
