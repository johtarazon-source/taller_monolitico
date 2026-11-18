<?php
/**
 * Tarjeta de vehiculo.
 *
 * @var \App\Views\HtmlView $this
 * @var \App\Models\Vehiculo $vehiculo
 * @var list<array{texto: string, ruta: string, query?: array<string, scalar>, clase: string}> $enlaces
 * @var string|null $nota texto adicional
 */

use App\Views\Formato;
?>
<article class="tarjeta-vehiculo">
    <header class="tarjeta-vehiculo__cabecera">
        <h3><?= $this->e("{$vehiculo->marca} {$vehiculo->modelo}") ?></h3>
        <?= Formato::badge($vehiculo->estado) ?>
    </header>
    <p class="texto-suave"><?= $this->e($vehiculo->anio) ?> · <?= $this->e($vehiculo->categoria ?: 'Sin categoría') ?></p>
    <?php if (!empty($nota)): ?>
        <small class="texto-suave"><?= $this->e($nota) ?></small>
    <?php endif; ?>
    <div class="tarjeta-vehiculo__acciones">
        <?php foreach ($enlaces as $enlace): ?>
            <a class="btn btn--chico <?= $this->e($enlace['clase']) ?>"
               href="<?= $this->e($this->url($enlace['ruta'], $enlace['query'] ?? [])) ?>"><?= $this->e($enlace['texto']) ?></a>
        <?php endforeach; ?>
    </div>
</article>
