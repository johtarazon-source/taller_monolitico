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
<div class="dona" role="img" aria-label="<?= $this->escapar("{$porcentaje}% de disponibilidad: {$disponibles} de {$total} vehículos disponibles") ?>">
    <svg class="dona__svg" viewBox="0 0 120 120" aria-hidden="true" focusable="false">
        <circle class="dona__pista" cx="60" cy="60" r="<?= $radio ?>" />
        <?php if ($arco > 0): ?>
            <circle class="dona__arco" cx="60" cy="60" r="<?= $radio ?>"
                    stroke-dasharray="<?= $this->escapar($arco) ?> <?= $this->escapar(round($circunferencia, 2)) ?>" />
        <?php endif; ?>
    </svg>
    <div class="dona__centro" aria-hidden="true">
        <strong class="dona__valor"><?= $this->escapar($porcentaje) ?>%</strong>
    </div>
</div>
