import { ApiClient, ApiError } from '../services/ApiClient.js';
import { SessionService } from '../services/SessionService.js';
import { mostrarMensaje, ocultarMensaje } from '../utils/mensajes.js';

/**
 * Controla el formulario de inicio de sesion y el boton de cerrar sesion del
 * contenedor principal.
 */
export class AuthController {
  constructor({ alAutenticarse, alCerrarSesion }) {
    this.alAutenticarse = alAutenticarse;
    this.alCerrarSesion = alCerrarSesion;

    this.formulario = document.getElementById('formulario-inicio-sesion');
    this.alerta = document.getElementById('inicio-sesion-alerta');
    this.botonCerrarSesion = document.getElementById('boton-cerrar-sesion');

    this.formulario.addEventListener('submit', (evento) => this.manejarInicioSesion(evento));
    this.botonCerrarSesion.addEventListener('click', () => this.manejarCierreSesion());
  }

  async manejarInicioSesion(evento) {
    evento.preventDefault();
    ocultarMensaje(this.alerta);

    const usuario = this.formulario.usuario.value.trim();
    // El nombre del campo y la clave JSON siguen siendo "password" (contrato de la API).
    const contrasena = this.formulario.password.value;

    if (!usuario || !contrasena) {
      mostrarMensaje(this.alerta, 'Usuario y contraseña son obligatorios.');
      return;
    }

    const botonEnviar = this.formulario.querySelector('button[type="submit"]');
    botonEnviar.disabled = true;

    try {
      const datosRespuesta = await ApiClient.post('/auth/login', { usuario, password: contrasena });
      SessionService.guardar(datosRespuesta.token, datosRespuesta.usuario);
      this.formulario.reset();
      this.alAutenticarse(datosRespuesta.usuario);
    } catch (error) {
      const mensaje = error instanceof ApiError ? error.message : 'No se pudo iniciar sesión.';
      mostrarMensaje(this.alerta, mensaje);
    } finally {
      botonEnviar.disabled = false;
    }
  }

  async manejarCierreSesion() {
    try {
      await ApiClient.post('/auth/logout', {});
    } catch {
      // Si el token ya expiro no hay nada que revertir en el servidor.
    } finally {
      SessionService.limpiar();
      this.alCerrarSesion();
    }
  }
}
