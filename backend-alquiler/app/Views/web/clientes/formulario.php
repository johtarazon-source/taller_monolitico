<?php
/**
 * Alta y edicion de clientes.
 *
 * @var \App\Views\HtmlView $this
 * @var int|null $clienteId
 * @var array<string, mixed> $datosFormulario
 * @var array<string, string> $errores
 */

$rutaFormulario = $clienteId === null ? '/clientes' : "/clientes/{$clienteId}";
?>
<section class="pagina pagina--angosta">
    <form class="formulario" method="post" action="<?= $this->escapar($this->url($rutaFormulario)) ?>">
        <?= $this->csrf() ?>
        <h1><?= $clienteId === null ? 'Nuevo cliente' : 'Editar cliente' ?></h1>

        <div class="rejilla-campos">
            <?= $this->parcial('_campo', ['nombreCampo' => 'nombre', 'etiqueta' => 'Nombre completo *', 'valor' => $datosFormulario['nombre'] ?? '', 'errores' => $errores, 'anchoCompleto' => true, 'atributos' => ['required' => true, 'maxlength' => 150]]) ?>
            <?= $this->parcial('_campo', ['nombreCampo' => 'telefono', 'etiqueta' => 'Teléfono', 'tipo' => 'tel', 'valor' => $datosFormulario['telefono'] ?? '', 'errores' => $errores, 'atributos' => ['maxlength' => 50]]) ?>
            <?= $this->parcial('_campo', ['nombreCampo' => 'correo', 'etiqueta' => 'Correo', 'tipo' => 'email', 'valor' => $datosFormulario['correo'] ?? '', 'errores' => $errores, 'atributos' => ['maxlength' => 100]]) ?>
            <?= $this->parcial('_campo', ['nombreCampo' => 'numero_licencia', 'etiqueta' => 'Número de licencia', 'valor' => $datosFormulario['numero_licencia'] ?? '', 'errores' => $errores, 'anchoCompleto' => true, 'ayuda' => 'Es obligatoria para poder hacer reservas.', 'atributos' => ['maxlength' => 50]]) ?>
        </div>

        <div class="formulario__pie">
            <a class="boton boton--secundario" href="<?= $this->escapar($this->url('/clientes')) ?>">Cancelar</a>
            <button type="submit" class="boton boton--primario"><?= $clienteId === null ? 'Registrar' : 'Guardar cambios' ?></button>
        </div>
    </form>
</section>
