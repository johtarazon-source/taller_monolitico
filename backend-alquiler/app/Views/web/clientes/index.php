<?php
/**
 * @var \App\Views\HtmlView $this
 * @var iterable<\App\Models\Cliente> $clientes
 * @var string $buscar
 */
?>
<section class="pagina">
    <header class="pagina__cabecera">
        <div>
            <h1>Clientes</h1>
            <p class="texto-suave">Personas registradas para alquilar vehículos.</p>
        </div>
        <a class="btn btn--primario" href="<?= $this->e($this->url('/clientes/nuevo')) ?>">Nuevo cliente</a>
    </header>

    <form class="filtros" method="get" action="<?= $this->e($this->url('/clientes')) ?>" role="search">
        <div class="campo filtros__campo">
            <label class="campo__etiqueta" for="c-buscar">Buscar</label>
            <input class="campo__control" id="c-buscar" name="buscar" type="search"
                   placeholder="Nombre, correo, teléfono o licencia" value="<?= $this->e($buscar) ?>">
        </div>
        <button type="submit" class="btn btn--secundario">Buscar</button>
    </form>

    <?php if (count($clientes) === 0): ?>
        <p class="estado-vacio">No hay clientes que coincidan. Registre uno con "Nuevo cliente".</p>
    <?php else: ?>
        <div class="rejilla-tarjetas">
            <?php foreach ($clientes as $cliente): ?>
                <?= $this->parcial('_tarjeta_cliente', ['cliente' => $cliente]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
