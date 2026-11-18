<?php
/**
 * Tarjeta de indicador del panel: cifra grande, detalle y barra de proporcion.
 * Es un enlace a la pagina del tema que resume.
 *
 * @var \App\Views\HtmlView $this
 * @var string $variante disponibles, rentados, clientes o reservas (define el color)
 * @var string $etiqueta
 * @var int|string $valor
 * @var string $detalle texto bajo la cifra (ej. "de 12 vehiculos")
 * @var int $porcentaje 0-100, largo de la barra
 * @var string $urlDestino URL de destino
 * @var string $textoAccesible texto para lectores de pantalla (ej. "Ver vehiculos disponibles")
 */

$porcentaje = max(0, min(100, (int) $porcentaje));
?>
<a class="indicador indicador--<?= $this->escapar($variante) ?>" href="<?= $this->escapar($urlDestino) ?>">
    <span class="indicador__etiqueta"><?= $this->escapar($etiqueta) ?></span>
    <strong class="indicador__valor"><?= $this->escapar($valor) ?></strong>
    <span class="indicador__detalle"><?= $this->escapar($detalle) ?></span>
    <div class="indicador__medidor">
        <span class="indicador__barra" aria-hidden="true"><span class="indicador__relleno" style="--porcentaje-relleno: <?= $porcentaje ?>"></span></span>
        <span class="indicador__porcentaje"><?= $porcentaje ?> %</span>
    </div>
    <span class="solo-lectores-pantalla"><?= $this->escapar($textoAccesible) ?></span>
</a>
