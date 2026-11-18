<?php
/**
 * Tarjeta de vehiculo.
 *
 * @var \App\Views\HtmlView $this
 * @var \App\Models\Vehiculo $vehiculo
 * @var list<array{texto: string, ruta: string, parametros?: array<string, scalar>, claseCss: string}> $enlaces
 * @var string|null $nota texto adicional
 */

use App\Views\Formato;
?>
<article class="tarjeta-vehiculo">
    <header class="tarjeta-vehiculo__cabecera">
        <h3><?= $this->escapar("{$vehiculo->marca} {$vehiculo->modelo}") ?></h3>
        <?= Formato::insignia($vehiculo->estado) ?>
    </header>
    <p class="texto-suave"><?= $this->escapar($vehiculo->anio) ?> · <?= $this->escapar($vehiculo->categoria ?: 'Sin categoría') ?></p>
    <?php if (!empty($nota)): ?>
        <small class="texto-suave"><?= $this->escapar($nota) ?></small>
    <?php endif; ?>
    <div class="tarjeta-vehiculo__acciones">
        <?php foreach ($enlaces as $enlaceAccion): ?>
            <a class="boton boton--chico <?= $this->escapar($enlaceAccion['claseCss']) ?>"
               href="<?= $this->escapar($this->url($enlaceAccion['ruta'], $enlaceAccion['parametros'] ?? [])) ?>"><?= $this->escapar($enlaceAccion['texto']) ?></a>
        <?php endforeach; ?>
    </div>
</article>
