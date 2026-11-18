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
        <h3><?= $this->escapar($cliente->nombre) ?></h3>
        <?php if (!$cliente->numero_licencia): ?>
            <span class="insignia insignia--cancelada">Sin licencia</span>
        <?php endif; ?>
    </header>
    <p class="texto-suave"><?= $this->escapar($cliente->telefono ?: 'Sin teléfono') ?> · <?= $this->escapar($cliente->correo ?: 'Sin correo') ?></p>
    <?php if ($cliente->numero_licencia): ?>
        <small class="texto-suave">Licencia: <?= $this->escapar($cliente->numero_licencia) ?></small>
    <?php endif; ?>
    <div class="tarjeta-cliente__acciones">
        <a class="boton boton--chico boton--secundario" href="<?= $this->escapar($this->url("/clientes/{$cliente->id}/editar")) ?>">Editar</a>
        <a class="boton boton--chico boton--secundario" href="<?= $this->escapar($this->url("/clientes/{$cliente->id}/historial")) ?>">Historial</a>
        <a class="boton boton--chico boton--texto-peligro" href="<?= $this->escapar($this->url("/clientes/{$cliente->id}/eliminar")) ?>">Eliminar</a>
    </div>
</article>
