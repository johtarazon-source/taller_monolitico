<?php
/**
 * Tarjeta de indicador del panel: cifra grande, detalle y barra de proporcion.
 *
 * @var \App\Views\HtmlView $this
 * @var string $variante disponibles, rentados, clientes o reservas (define el color)
 * @var string $etiqueta
 * @var int|string $valor
 * @var string $detalle texto bajo la cifra (ej. "de 12 vehiculos")
 * @var int $porcentaje 0-100, largo de la barra
 */

$porcentaje = max(0, min(100, (int) $porcentaje));
?>
<article class="kpi kpi--<?= $this->e($variante) ?>">
    <span class="kpi__etiqueta"><?= $this->e($etiqueta) ?></span>
    <strong class="kpi__valor"><?= $this->e($valor) ?></strong>
    <span class="kpi__detalle"><?= $this->e($detalle) ?></span>
    <div class="kpi__medidor">
        <span class="kpi__barra" aria-hidden="true"><span class="kpi__relleno" style="--valor: <?= $porcentaje ?>"></span></span>
        <span class="kpi__porcentaje"><?= $porcentaje ?> %</span>
    </div>
</article>
