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
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Licencia</th>
                        <th><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td data-titulo="Nombre"><strong><?= $this->e($cliente->nombre) ?></strong></td>
                            <td data-titulo="Teléfono"><?= $this->e($cliente->telefono ?: '-') ?></td>
                            <td data-titulo="Correo"><?= $this->e($cliente->correo ?: '-') ?></td>
                            <td data-titulo="Licencia">
                                <?php if ($cliente->numero_licencia): ?>
                                    <?= $this->e($cliente->numero_licencia) ?>
                                <?php else: ?>
                                    <span class="badge badge--cancelada">Sin licencia</span>
                                <?php endif; ?>
                            </td>
                            <td class="tabla__acciones">
                                <a class="btn btn--chico btn--secundario" href="<?= $this->e($this->url("/clientes/{$cliente->id}/editar")) ?>">Editar</a>
                                <a class="btn btn--chico btn--secundario" href="<?= $this->e($this->url("/clientes/{$cliente->id}/historial")) ?>">Historial</a>
                                <a class="btn btn--chico btn--texto-peligro" href="<?= $this->e($this->url("/clientes/{$cliente->id}/eliminar")) ?>">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
