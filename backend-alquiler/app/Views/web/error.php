<?php
/**
 * @var \App\Views\HtmlView $this
 * @var int $codigoEstado
 * @var string $mensaje
 */
?>
<section class="pagina">
    <header class="pagina__cabecera">
        <div>
            <h1>Error <?= $this->escapar($codigoEstado) ?></h1>
            <p class="texto-suave"><?= $this->escapar($mensaje) ?></p>
        </div>
    </header>
    <p><a class="boton boton--secundario" href="<?= $this->escapar($this->url('/')) ?>">Ir al inicio</a></p>
</section>
