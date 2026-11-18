<?php
/**
 * Pagina de confirmacion previa a una accion que cambia datos.
 *
 * @var \App\Views\HtmlView $this
 * @var string $titulo
 * @var string $mensaje
 * @var string $rutaAccion ruta del formulario POST
 * @var string $rutaVolver ruta para cancelar
 * @var string $textoBoton
 * @var bool $esPeligrosa
 * @var bool|null $opcionMantenimiento muestra la casilla "enviar a mantenimiento"
 */
?>
<section class="pagina pagina--angosta">
    <form class="formulario" method="post" action="<?= $this->escapar($this->url($rutaAccion)) ?>">
        <?= $this->csrf() ?>
        <h1>
            <?php if (!empty($esPeligrosa)): ?>
                <?= $this->parcial('_icono', ['nombreIcono' => 'alerta']) ?>
            <?php endif; ?>
            <?= $this->escapar($titulo) ?>
        </h1>
        <p><?= $this->escapar($mensaje) ?></p>

        <?php if (!empty($opcionMantenimiento)): ?>
            <label class="casilla">
                <input type="checkbox" name="mantenimiento" value="1">
                Enviar el vehículo a mantenimiento
            </label>
        <?php endif; ?>

        <div class="formulario__pie">
            <a class="boton boton--secundario" href="<?= $this->escapar($this->url($rutaVolver)) ?>">Volver</a>
            <button type="submit" class="boton <?= $esPeligrosa ? 'boton--peligro' : 'boton--primario' ?>"><?= $this->escapar($textoBoton) ?></button>
        </div>
    </form>
</section>
