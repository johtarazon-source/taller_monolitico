<?php
/**
 * Alta y edicion de clientes.
 *
 * @var \App\Views\HtmlView $this
 * @var int|null $id
 * @var array<string, mixed> $datos
 * @var array<string, string> $errores
 */

$accion = $id === null ? '/clientes' : "/clientes/{$id}";
?>
<section class="pagina pagina--angosta">
    <form class="formulario" method="post" action="<?= $this->e($this->url($accion)) ?>">
        <?= $this->csrf() ?>
        <h1><?= $id === null ? 'Nuevo cliente' : 'Editar cliente' ?></h1>

        <div class="rejilla-campos">
            <?= $this->parcial('_campo', ['nombre' => 'nombre', 'etiqueta' => 'Nombre completo *', 'valor' => $datos['nombre'] ?? '', 'errores' => $errores, 'ancho' => true, 'atributos' => ['required' => true, 'maxlength' => 150]]) ?>
            <?= $this->parcial('_campo', ['nombre' => 'telefono', 'etiqueta' => 'Teléfono', 'tipo' => 'tel', 'valor' => $datos['telefono'] ?? '', 'errores' => $errores, 'atributos' => ['maxlength' => 50]]) ?>
            <?= $this->parcial('_campo', ['nombre' => 'correo', 'etiqueta' => 'Correo', 'tipo' => 'email', 'valor' => $datos['correo'] ?? '', 'errores' => $errores, 'atributos' => ['maxlength' => 100]]) ?>
            <?= $this->parcial('_campo', ['nombre' => 'numero_licencia', 'etiqueta' => 'Número de licencia', 'valor' => $datos['numero_licencia'] ?? '', 'errores' => $errores, 'ancho' => true, 'ayuda' => 'Es obligatoria para poder hacer reservas.', 'atributos' => ['maxlength' => 50]]) ?>
        </div>

        <div class="formulario__pie">
            <a class="btn btn--secundario" href="<?= $this->e($this->url('/clientes')) ?>">Cancelar</a>
            <button type="submit" class="btn btn--primario"><?= $id === null ? 'Registrar' : 'Guardar cambios' ?></button>
        </div>
    </form>
</section>
