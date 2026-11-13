<?php
/**
 * Tarjeta con una cifra y su etiqueta.
 *
 * @var \App\Views\HtmlView $this
 * @var string $etiqueta
 * @var int|string $valor
 * @var string|null $variante disponible, alquilado o mantenimiento
 */
?>
<article class="<?= empty($variante) ? 'tarjeta-dato' : 'tarjeta-dato tarjeta-dato--' . $this->e($variante) ?>">
    <span class="tarjeta-dato__etiqueta"><?= $this->e($etiqueta) ?></span>
    <strong class="tarjeta-dato__valor"><?= $this->e($valor) ?></strong>
</article>
