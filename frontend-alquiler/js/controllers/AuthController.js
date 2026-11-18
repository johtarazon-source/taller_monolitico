import { ApiClient, ApiError } from '../services/ApiClient.js';
import { SessionService } from '../services/SessionService.js';
import { mostrarMensaje, ocultarMensaje } from '../utils/mensajes.js';

/**
 * Controla el formulario de login y el boton de logout del shell principal.
 */
export class AuthController {
  constructor({ onAutenticado, onCerrarSesion }) {
    this.onAutenticado = onAutenticado;
    this.onCerrarSesion = onCerrarSesion;

    this.formulario = document.getElementById('form-login');
    this.alerta = document.getElementById('login-alerta');
    this.botonLogout = document.getElementById('btn-logout');

    this.formulario.addEventListener('submit', (evento) => this.manejarLogin(evento));
    this.botonLogout.addEventListener('click', () => this.manejarLogout());
  }

  async manejarLogin(evento) {
    evento.preventDefault();
    ocultarMensaje(this.alerta);

    const usuario = this.formulario.usuario.value.trim();
    const password = this.formulario.password.value;

    if (!usuario || !password) {
      mostrarMensaje(this.alerta, 'Usuario y contraseña son obligatorios.');
      return;
    }

    const boton = this.formulario.querySelector('button[type="submit"]');
    boton.disabled = true;

    try {
      const data = await ApiClient.post('/auth/login', { usuario, password });
      SessionService.guardar(data.token, data.usuario);
      this.formulario.reset();
      this.onAutenticado(data.usuario);
    } catch (error) {
      const mensaje = error instanceof ApiError ? error.message : 'No se pudo iniciar sesión.';
      mostrarMensaje(this.alerta, mensaje);
    } finally {
      boton.disabled = false;
    }
  }

  async manejarLogout() {
    try {
      await ApiClient.post('/auth/logout', {});
    } catch {
      // Si el token ya expiro no hay nada que revertir en el servidor.
    } finally {
      SessionService.limpiar();
      this.onCerrarSesion();
    }
  }
}
