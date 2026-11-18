<?php
/**
 * Icono SVG inline reutilizable (sin dependencias externas).
 *
 * @var \App\Views\HtmlView $this
 * @var string $nombreIcono exito, error, info, alerta
 */

$trazos = [
    'exito' => '<path d="M20 6 9 17l-5-5"/>',
    'error' => '<path d="M18 6 6 18"/><path d="M6 6l12 12"/>',
    'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
    'alerta' => '<path d="M12 9v4"/><path d="M12 17h.01"/><path d="m10.29 3.86-8.18 14.18A1 1 0 0 0 3 19.5h18a1 1 0 0 0 .89-1.46L13.71 3.86a1 1 0 0 0-1.42 0z"/>',
];

$trazo = $trazos[$nombreIcono] ?? $trazos['info'];
?>
<svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><?= $trazo ?></svg>
