<?php
/**
 * @var \App\Views\HtmlView $this
 * @var string|null $mensajeError
 * @var string|null $nombreUsuario
 */
?>
<section class="inicio-sesion">
    <form class="inicio-sesion__tarjeta" method="post" action="<?= $this->escapar($this->url('/login')) ?>">
        <?= $this->csrf() ?>
        <?= $this->parcial('_logo') ?>
        <div>
            <h1 class="inicio-sesion__titulo">Iniciar sesión</h1>
            <p class="texto-suave">Ingrese para gestionar la flota, los clientes y las reservas.</p>
        </div>

        <div class="campo">
            <label class="campo__etiqueta" for="usuario">Usuario</label>
            <input class="campo__control" id="usuario" name="usuario" type="text" required autofocus
                   autocomplete="username" value="<?= $this->escapar($nombreUsuario ?? '') ?>">
        </div>

        <div class="campo">
            <label class="campo__etiqueta" for="contrasena">Contraseña</label>
            <input class="campo__control" id="contrasena" name="contrasena" type="password" required
                   autocomplete="current-password">
        </div>

        <?php if (!empty($mensajeError)): ?>
            <p class="mensaje-error" role="alert">
                <?= $this->parcial('_icono', ['nombreIcono' => 'alerta']) ?>
                <span><?= $this->escapar($mensajeError) ?></span>
            </p>
        <?php endif; ?>

        <button type="submit" class="boton boton--destacado boton--grande boton--bloque">Entrar</button>
    </form>
</section>
