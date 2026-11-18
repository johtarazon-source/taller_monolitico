<?php
/**
 * Campo de formulario con etiqueta y mensaje de error.
 *
 * @var \App\Views\HtmlView $this
 * @var string $nombreCampo
 * @var string $etiqueta
 * @var string|null $tipo text, number, email, tel, date, search, password
 * @var mixed $valor
 * @var array<string, string> $errores
 * @var array<string, scalar|bool> $atributos atributos HTML extra (required, maxlength, min...)
 * @var string|null $ayuda texto de ayuda bajo el campo
 * @var bool|null $anchoCompleto ocupa las dos columnas de la rejilla
 */

$tipo ??= 'text';
$errores ??= [];
$atributos ??= [];
$mensajeError = $errores[$nombreCampo] ?? null;
$idCampo = 'campo-' . $nombreCampo;

$atributosHtmlExtra = '';
foreach ($atributos as $atributo => $valorAtributo) {
    if ($valorAtributo === true) {
        $atributosHtmlExtra .= ' ' . $this->escapar($atributo);
    } elseif ($valorAtributo !== false && $valorAtributo !== null) {
        $atributosHtmlExtra .= ' ' . $this->escapar($atributo) . '="' . $this->escapar($valorAtributo) . '"';
    }
}
?>
<div class="<?= empty($anchoCompleto) ? 'campo' : 'campo campo--ancho' ?>">
    <label class="campo__etiqueta" for="<?= $this->escapar($idCampo) ?>"><?= $this->escapar($etiqueta) ?></label>
    <input class="campo__control" id="<?= $this->escapar($idCampo) ?>" name="<?= $this->escapar($nombreCampo) ?>"
           type="<?= $this->escapar($tipo) ?>" value="<?= $this->escapar($valor ?? '') ?>"<?= $atributosHtmlExtra ?><?= $mensajeError ? ' aria-invalid="true"' : '' ?>>
    <?php if (!empty($ayuda)): ?>
        <small class="texto-suave"><?= $this->escapar($ayuda) ?></small>
    <?php endif; ?>
    <?php if ($mensajeError): ?>
        <small class="mensaje-error"><?= $this->parcial('_icono', ['nombreIcono' => 'alerta']) ?><span><?= $this->escapar($mensajeError) ?></span></small>
    <?php endif; ?>
</div>
