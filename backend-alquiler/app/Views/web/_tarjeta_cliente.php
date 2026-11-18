<?php
/**
 * Tarjeta de cliente.
 *
 * @var \App\Views\HtmlView $this
 * @var \App\Models\Cliente $cliente
 */
?>
<article class="tarjeta-cliente">
    <header class="tarjeta-cliente__cabecera">
        <h3><?= $this->e($cliente->nombre) ?></h3>
        <?php if (!$cliente->numero_licencia): ?>
            <span class="badge badge--cancelada">Sin licencia</span>
        <?php endif; ?>
    </header>
    <p class="texto-suave"><?= $this->e($cliente->telefono ?: 'Sin teléfono') ?> · <?= $this->e($cliente->correo ?: 'Sin correo') ?></p>
    <?php if ($cliente->numero_licencia): ?>
        <small class="texto-suave">Licencia: <?= $this->e($cliente->numero_licencia) ?></small>
    <?php endif; ?>
    <div class="tarjeta-cliente__acciones">
        <a class="btn btn--chico btn--secundario" href="<?= $this->e($this->url("/clientes/{$cliente->id}/editar")) ?>">Editar</a>
        <a class="btn btn--chico btn--secundario" href="<?= $this->e($this->url("/clientes/{$cliente->id}/historial")) ?>">Historial</a>
        <a class="btn btn--chico btn--texto-peligro" href="<?= $this->e($this->url("/clientes/{$cliente->id}/eliminar")) ?>">Eliminar</a>
    </div>
</article>
