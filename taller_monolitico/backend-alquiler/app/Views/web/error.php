<?php
/**
 * @var \App\Views\HtmlView $this
 * @var int $status
 * @var string $mensaje
 */
?>
<section class="pagina">
    <header class="pagina__cabecera">
        <div>
            <h1>Error <?= $this->e($status) ?></h1>
            <p class="texto-suave"><?= $this->e($mensaje) ?></p>
        </div>
    </header>
    <p><a class="btn btn--secundario" href="<?= $this->e($this->url('/')) ?>">Ir al inicio</a></p>
</section>
