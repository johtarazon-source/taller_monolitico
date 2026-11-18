<?php
/**
 * Grafico de dona con el porcentaje de vehiculos disponibles.
 *
 * @var \App\Views\HtmlView $this
 * @var int $porcentaje 0-100
 * @var int $disponibles
 * @var int $total
 */

$radio = 52;
$circunferencia = 2 * M_PI * $radio;
$arco = round($circunferencia * max(0, min(100, $porcentaje)) / 100, 2);
?>
<div class="donut" role="img" aria-label="<?= $this->e("{$porcentaje}% de disponibilidad: {$disponibles} de {$total} vehículos disponibles") ?>">
    <svg class="donut__svg" viewBox="0 0 120 120" aria-hidden="true" focusable="false">
        <circle class="donut__pista" cx="60" cy="60" r="<?= $radio ?>" />
        <?php if ($arco > 0): ?>
            <circle class="donut__arco" cx="60" cy="60" r="<?= $radio ?>"
                    stroke-dasharray="<?= $this->e($arco) ?> <?= $this->e(round($circunferencia, 2)) ?>" />
        <?php endif; ?>
    </svg>
    <div class="donut__centro" aria-hidden="true">
        <strong class="donut__valor"><?= $this->e($porcentaje) ?>%</strong>
        <span class="donut__etiqueta">Disponibilidad</span>
    </div>
</div>
