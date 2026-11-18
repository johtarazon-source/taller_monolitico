<?php
/**
 * Alta y edicion de vehiculos.
 *
 * @var \App\Views\HtmlView $this
 * @var int|null $id
 * @var array<string, mixed> $datos
 * @var array<string, string> $errores
 * @var string|null $estadoActual estado guardado (solo en edicion)
 */

$alquilado = $estadoActual === 'alquilado';
$estado = ($datos['estado'] ?? '') === 'mantenimiento' ? 'mantenimiento' : 'disponible';
$accion = $id === null ? '/vehiculos' : "/vehiculos/{$id}";
?>
<section class="pagina pagina--angosta">
    <form class="formulario" method="post" action="<?= $this->e($this->url($accion)) ?>">
        <?= $this->csrf() ?>
        <h1><?= $id === null ? 'Nuevo vehículo' : 'Editar vehículo' ?></h1>

        <div class="rejilla-campos">
            <?= $this->parcial('_campo', ['nombre' => 'marca', 'etiqueta' => 'Marca *', 'valor' => $datos['marca'] ?? '', 'errores' => $errores, 'atributos' => ['required' => true, 'maxlength' => 100]]) ?>
            <?= $this->parcial('_campo', ['nombre' => 'modelo', 'etiqueta' => 'Modelo *', 'valor' => $datos['modelo'] ?? '', 'errores' => $errores, 'atributos' => ['required' => true, 'maxlength' => 100]]) ?>
            <?= $this->parcial('_campo', ['nombre' => 'anio', 'etiqueta' => 'Año *', 'tipo' => 'number', 'valor' => $datos['anio'] ?? '', 'errores' => $errores, 'atributos' => ['required' => true, 'min' => 1901, 'max' => (int) date('Y') + 1]]) ?>
            <?= $this->parcial('_campo', ['nombre' => 'categoria', 'etiqueta' => 'Categoría', 'valor' => $datos['categoria'] ?? '', 'errores' => $errores, 'atributos' => ['maxlength' => 50, 'placeholder' => 'Sedán, SUV, Camioneta...']]) ?>

            <div class="campo campo--ancho">
                <label class="campo__etiqueta" for="campo-estado">Estado</label>
                <?php if ($alquilado): ?>
                    <p class="texto-suave">Alquilado. El estado se vuelve a calcular al registrar la devolución.</p>
                <?php else: ?>
                    <select class="campo__control" id="campo-estado" name="estado">
                        <option value="disponible"<?= $estado === 'disponible' ? ' selected' : '' ?>>Disponible</option>
                        <option value="mantenimiento"<?= $estado === 'mantenimiento' ? ' selected' : '' ?>>En mantenimiento</option>
                    </select>
                <?php endif; ?>
                <?php if (isset($errores['estado'])): ?>
                    <small class="form-error"><?= $this->e($errores['estado']) ?></small>
                <?php endif; ?>
            </div>
        </div>

        <div class="formulario__pie">
            <a class="btn btn--secundario" href="<?= $this->e($this->url('/vehiculos')) ?>">Cancelar</a>
            <button type="submit" class="btn btn--primario"><?= $id === null ? 'Registrar' : 'Guardar cambios' ?></button>
        </div>
    </form>
</section>
