<?php
/**
 * @var \App\Views\HtmlView $this
 * @var iterable<\App\Models\Reserva> $reservas
 * @var string $estado
 */

$opcionesEstado = ['activa' => 'Activas', 'completada' => 'Completadas', 'cancelada' => 'Canceladas', 'todas' => 'Todas'];
?>
<section class="pagina">
    <header class="pagina__cabecera">
        <div>
            <h1>Reservas</h1>
            <p class="texto-suave">Alquileres registrados, devoluciones y cancelaciones.</p>
        </div>
        <a class="boton boton--primario" href="<?= $this->escapar($this->url('/reservas/nueva')) ?>">Nueva reserva</a>
    </header>

    <form class="filtros" method="get" action="<?= $this->escapar($this->url('/reservas')) ?>">
        <div class="campo filtros__campo">
            <label class="campo__etiqueta" for="reservas-estado">Estado</label>
            <select class="campo__control" id="reservas-estado" name="estado">
                <?php foreach ($opcionesEstado as $valor => $texto): ?>
                    <option value="<?= $this->escapar($valor) ?>"<?= $valor === $estado ? ' selected' : '' ?>><?= $this->escapar($texto) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="boton boton--secundario">Filtrar</button>
    </form>

    <?php if (count($reservas) === 0): ?>
        <p class="estado-vacio">No hay reservas con ese estado.</p>
    <?php else: ?>
        <?= $this->parcial('_tabla_reservas', ['reservas' => $reservas]) ?>
    <?php endif; ?>
</section>
