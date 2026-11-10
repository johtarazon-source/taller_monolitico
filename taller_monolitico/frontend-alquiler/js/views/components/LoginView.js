export function renderLoginView(container) {
  container.innerHTML = `
    <section class="login-screen">
      <form id="login-form" class="login-card" novalidate>
        <h1>Iniciar sesión</h1>

        <label for="usuario">Usuario</label>
        <input id="usuario" name="usuario" type="text" required autocomplete="username" />

        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" required autocomplete="current-password" />

        <p id="login-error" class="form-error" role="alert" hidden></p>

        <button type="submit">Entrar</button>
      </form>
    </section>
  `;
}
