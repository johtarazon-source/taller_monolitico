<?php
/**
 * Estado vacio de una tarjeta del panel: icono, titulo y texto de ayuda.
 *
 * @var \App\Views\HtmlView $this
 * @var string $icono trazo SVG (atributo "d" de un path, viewBox 24x24)
 * @var string $titulo
 * @var string $texto
 */
?>
<div class="vacio">
    <svg class="vacio__icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
        <path d="<?= $this->e($icono) ?>" />
    </svg>
    <strong class="vacio__titulo"><?= $this->e($titulo) ?></strong>
    <p class="vacio__texto"><?= $this->e($texto) ?></p>
</div>
