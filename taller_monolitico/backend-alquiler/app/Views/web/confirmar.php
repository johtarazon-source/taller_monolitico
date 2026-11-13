<?php
/**
 * Pagina de confirmacion previa a una accion que cambia datos.
 *
 * @var \App\Views\HtmlView $this
 * @var string $titulo
 * @var string $mensaje
 * @var string $accion ruta del formulario POST
 * @var string $volver ruta para cancelar
 * @var string $textoBoton
 * @var bool $peligro
 * @var bool|null $opcionMantenimiento muestra la casilla "enviar a mantenimiento"
 */
?>
<section class="pagina pagina--angosta">
    <form class="formulario" method="post" action="<?= $this->e($this->url($accion)) ?>">
        <?= $this->csrf() ?>
        <h1><?= $this->e($titulo) ?></h1>
        <p><?= $this->e($mensaje) ?></p>

        <?php if (!empty($opcionMantenimiento)): ?>
            <label class="casilla">
                <input type="checkbox" name="mantenimiento" value="1">
                Enviar el vehículo a mantenimiento
            </label>
        <?php endif; ?>

        <div class="formulario__pie">
            <a class="btn btn--secundario" href="<?= $this->e($this->url($volver)) ?>">Volver</a>
            <button type="submit" class="btn <?= $peligro ? 'btn--peligro' : 'btn--primario' ?>"><?= $this->e($textoBoton) ?></button>
        </div>
    </form>
</section>
