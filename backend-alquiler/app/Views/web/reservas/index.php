<?php
/**
 * @var \App\Views\HtmlView $this
 * @var iterable<\App\Models\Reserva> $reservas
 * @var string $estado
 */

$opciones = ['activa' => 'Activas', 'completada' => 'Completadas', 'cancelada' => 'Canceladas', 'todas' => 'Todas'];
?>
<section class="pagina">
    <header class="pagina__cabecera">
        <div>
            <h1>Reservas</h1>
            <p class="texto-suave">Alquileres registrados, devoluciones y cancelaciones.</p>
        </div>
        <a class="btn btn--primario" href="<?= $this->e($this->url('/reservas/nueva')) ?>">Nueva reserva</a>
    </header>

    <form class="filtros" method="get" action="<?= $this->e($this->url('/reservas')) ?>">
        <div class="campo filtros__campo">
            <label class="campo__etiqueta" for="r-estado">Estado</label>
            <select class="campo__control" id="r-estado" name="estado">
                <?php foreach ($opciones as $valor => $texto): ?>
                    <option value="<?= $this->e($valor) ?>"<?= $valor === $estado ? ' selected' : '' ?>><?= $this->e($texto) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn--secundario">Filtrar</button>
    </form>

    <?php if (count($reservas) === 0): ?>
        <p class="estado-vacio">No hay reservas con ese estado.</p>
    <?php else: ?>
        <?= $this->parcial('_tabla_reservas', ['reservas' => $reservas]) ?>
    <?php endif; ?>
</section>
