<?php
/**
 * @var \App\Views\HtmlView $this
 * @var string|null $error
 * @var string|null $usuario
 */
?>
<section class="login">
    <form class="login__tarjeta" method="post" action="<?= $this->e($this->url('/login')) ?>">
        <?= $this->csrf() ?>
        <h1>Iniciar sesión</h1>

        <div class="campo">
            <label class="campo__etiqueta" for="usuario">Usuario</label>
            <input class="campo__control" id="usuario" name="usuario" type="text" required autofocus
                   autocomplete="username" value="<?= $this->e($usuario ?? '') ?>">
        </div>

        <div class="campo">
            <label class="campo__etiqueta" for="password">Contraseña</label>
            <input class="campo__control" id="password" name="password" type="password" required
                   autocomplete="current-password">
        </div>

        <?php if (!empty($error)): ?>
            <p class="form-error" role="alert"><?= $this->e($error) ?></p>
        <?php endif; ?>

        <button type="submit" class="btn btn--primario btn--bloque">Entrar</button>
    </form>
</section>
