<?php
/**
 * Campo de formulario con etiqueta y mensaje de error.
 *
 * @var \App\Views\HtmlView $this
 * @var string $nombre
 * @var string $etiqueta
 * @var string|null $tipo text, number, email, tel, date, search, password
 * @var mixed $valor
 * @var array<string, string> $errores
 * @var array<string, scalar|bool> $atributos atributos HTML extra (required, maxlength, min...)
 * @var string|null $ayuda texto de ayuda bajo el campo
 * @var bool|null $ancho ocupa las dos columnas de la rejilla
 */

$tipo ??= 'text';
$errores ??= [];
$atributos ??= [];
$error = $errores[$nombre] ?? null;
$idCampo = 'campo-' . $nombre;

$extra = '';
foreach ($atributos as $atributo => $valorAtributo) {
    if ($valorAtributo === true) {
        $extra .= ' ' . $this->e($atributo);
    } elseif ($valorAtributo !== false && $valorAtributo !== null) {
        $extra .= ' ' . $this->e($atributo) . '="' . $this->e($valorAtributo) . '"';
    }
}
?>
<div class="<?= empty($ancho) ? 'campo' : 'campo campo--ancho' ?>">
    <label class="campo__etiqueta" for="<?= $this->e($idCampo) ?>"><?= $this->e($etiqueta) ?></label>
    <input class="campo__control" id="<?= $this->e($idCampo) ?>" name="<?= $this->e($nombre) ?>"
           type="<?= $this->e($tipo) ?>" value="<?= $this->e($valor ?? '') ?>"<?= $extra ?><?= $error ? ' aria-invalid="true"' : '' ?>>
    <?php if (!empty($ayuda)): ?>
        <small class="texto-suave"><?= $this->e($ayuda) ?></small>
    <?php endif; ?>
    <?php if ($error): ?>
        <small class="form-error"><?= $this->e($error) ?></small>
    <?php endif; ?>
</div>
