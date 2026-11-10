import { SessionService } from './services/SessionService.js';
import { startLoginController } from './controllers/LoginController.js';
import { AuthModel } from './models/AuthModel.js';

/**
 * Pantalla posterior al login. Los modulos de vehiculos, clientes,
 * reservas e historial se agregan en las fases siguientes.
 */
function renderAuthenticatedShell() {
  const header = document.getElementById('app-header');
  const main = document.getElementById('app-main');

  header.innerHTML = '<button id="logout-btn" type="button">Cerrar sesión</button>';
  main.innerHTML = '<p>Sesión iniciada correctamente.</p>';

  header.querySelector('#logout-btn').addEventListener('click', async () => {
    try {
      await AuthModel.logout();
    } finally {
      SessionService.clearToken();
      startApp();
    }
  });
}

function startApp() {
  const header = document.getElementById('app-header');
  const main = document.getElementById('app-main');
  header.innerHTML = '';

  if (SessionService.isAuthenticated()) {
    renderAuthenticatedShell();
    return;
  }

  startLoginController(main, renderAuthenticatedShell);
}

window.addEventListener('sesion-expirada', startApp);
document.addEventListener('DOMContentLoaded', startApp);
