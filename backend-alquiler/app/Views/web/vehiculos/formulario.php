<?php
/**
 * Alta y edicion de vehiculos.
 *
 * @var \App\Views\HtmlView $this
 * @var int|null $vehiculoId
 * @var array<string, mixed> $datosFormulario
 * @var array<string, string> $errores
 * @var string|null $estadoActual estado guardado (solo en edicion)
 */

$estaAlquilado = $estadoActual === 'alquilado';
$estadoSeleccionado = ($datosFormulario['estado'] ?? '') === 'mantenimiento' ? 'mantenimiento' : 'disponible';
$rutaFormulario = $vehiculoId === null ? '/vehiculos' : "/vehiculos/{$vehiculoId}";
?>
<section class="pagina pagina--angosta">
    <form class="formulario" method="post" action="<?= $this->escapar($this->url($rutaFormulario)) ?>">
        <?= $this->csrf() ?>
        <h1><?= $vehiculoId === null ? 'Nuevo vehículo' : 'Editar vehículo' ?></h1>

        <div class="rejilla-campos">
            <?= $this->parcial('_campo', ['nombreCampo' => 'marca', 'etiqueta' => 'Marca *', 'valor' => $datosFormulario['marca'] ?? '', 'errores' => $errores, 'atributos' => ['required' => true, 'maxlength' => 100]]) ?>
            <?= $this->parcial('_campo', ['nombreCampo' => 'modelo', 'etiqueta' => 'Modelo *', 'valor' => $datosFormulario['modelo'] ?? '', 'errores' => $errores, 'atributos' => ['required' => true, 'maxlength' => 100]]) ?>
            <?= $this->parcial('_campo', ['nombreCampo' => 'anio', 'etiqueta' => 'Año *', 'tipo' => 'number', 'valor' => $datosFormulario['anio'] ?? '', 'errores' => $errores, 'atributos' => ['required' => true, 'min' => 1901, 'max' => (int) date('Y') + 1]]) ?>
            <?= $this->parcial('_campo', ['nombreCampo' => 'categoria', 'etiqueta' => 'Categoría', 'valor' => $datosFormulario['categoria'] ?? '', 'errores' => $errores, 'atributos' => ['maxlength' => 50, 'placeholder' => 'Sedán, SUV, Camioneta...']]) ?>

            <div class="campo campo--ancho">
                <label class="campo__etiqueta" for="campo-estado">Estado</label>
                <?php if ($estaAlquilado): ?>
                    <p class="texto-suave">Alquilado. El estado se vuelve a calcular al registrar la devolución.</p>
                <?php else: ?>
                    <select class="campo__control" id="campo-estado" name="estado">
                        <option value="disponible"<?= $estadoSeleccionado === 'disponible' ? ' selected' : '' ?>>Disponible</option>
                        <option value="mantenimiento"<?= $estadoSeleccionado === 'mantenimiento' ? ' selected' : '' ?>>En mantenimiento</option>
                    </select>
                <?php endif; ?>
                <?php if (isset($errores['estado'])): ?>
                    <small class="mensaje-error"><?= $this->escapar($errores['estado']) ?></small>
                <?php endif; ?>
            </div>
        </div>

        <div class="formulario__pie">
            <a class="boton boton--secundario" href="<?= $this->escapar($this->url('/vehiculos')) ?>">Cancelar</a>
            <button type="submit" class="boton boton--primario"><?= $vehiculoId === null ? 'Registrar' : 'Guardar cambios' ?></button>
        </div>
    </form>
</section>
