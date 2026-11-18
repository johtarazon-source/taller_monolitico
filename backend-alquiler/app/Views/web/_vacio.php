<?php
/**
 * Estado vacio de una tarjeta del panel: icono, titulo y texto de ayuda.
 *
 * @var \App\Views\HtmlView $this
 * @var string $trazoIcono trazo SVG (atributo "d" de un path, viewBox 24x24)
 * @var string $titulo
 * @var string $descripcion
 */
?>
<div class="vacio">
    <svg class="vacio__icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
        <path d="<?= $this->escapar($trazoIcono) ?>" />
    </svg>
    <strong class="vacio__titulo"><?= $this->escapar($titulo) ?></strong>
    <p class="vacio__texto"><?= $this->escapar($descripcion) ?></p>
</div>
