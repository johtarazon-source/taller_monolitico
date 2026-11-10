import { renderLoginView } from '../views/components/LoginView.js';
import { AuthModel } from '../models/AuthModel.js';
import { SessionService } from '../services/SessionService.js';

export function startLoginController(container, onSuccess) {
  renderLoginView(container);

  const form = container.querySelector('#login-form');
  const errorBox = container.querySelector('#login-error');

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    errorBox.hidden = true;

    const usuario = form.usuario.value.trim();
    const password = form.password.value;

    if (!usuario || !password) {
      errorBox.textContent = 'Usuario y contraseña son obligatorios.';
      errorBox.hidden = false;
      return;
    }

    try {
      const token = await AuthModel.login(usuario, password);
      SessionService.setToken(token);
      onSuccess();
    } catch (error) {
      errorBox.textContent = error.message;
      errorBox.hidden = false;
    }
  });
}
