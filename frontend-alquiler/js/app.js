import { SessionService } from './services/SessionService.js';
import { ApiClient } from './services/ApiClient.js';
import { AuthController } from './controllers/AuthController.js';

/**
 * Arranque de la aplicacion: alterna entre la pantalla de inicio de sesion y el
 * contenedor principal con la navegacion entre modulos (vehiculos, clientes,
 * reservas, disponibilidad).
 */
class App {
  constructor() {
    this.vistaInicioSesion = document.getElementById('vista-inicio-sesion');
    this.contenedorPrincipal = document.getElementById('contenedor-principal');
    this.usuarioActual = document.getElementById('usuario-actual');
    this.enlacesNavegacion = document.querySelectorAll('.navegacion__enlace');
    this.vistasModulo = document.querySelectorAll('.vista-modulo');

    ApiClient.alNoAutorizarse = () => this.mostrarInicioSesion();

    this.authController = new AuthController({
      alAutenticarse: (usuario) => this.mostrarAplicacion(usuario),
      alCerrarSesion: () => this.mostrarInicioSesion(),
    });

    this.enlacesNavegacion.forEach((enlace) => {
      enlace.addEventListener('click', (evento) => {
        evento.preventDefault();
        this.navegarA(enlace.dataset.modulo);
      });
    });

    this.iniciar();
  }

  iniciar() {
    if (SessionService.haySesion()) {
      this.mostrarAplicacion(SessionService.obtenerUsuario());
      return;
    }

    this.mostrarInicioSesion();
  }

  mostrarInicioSesion() {
    this.vistaInicioSesion.hidden = false;
    this.contenedorPrincipal.hidden = true;
  }

  mostrarAplicacion(usuario) {
    this.usuarioActual.textContent = usuario;
    this.vistaInicioSesion.hidden = true;
    this.contenedorPrincipal.hidden = false;
    this.navegarA('vehiculos');
  }

  navegarA(modulo) {
    this.vistasModulo.forEach((vista) => {
      vista.hidden = vista.dataset.modulo !== modulo;
    });

    this.enlacesNavegacion.forEach((enlace) => {
      const esActivo = enlace.dataset.modulo === modulo;
      enlace.classList.toggle('activo', esActivo);

      if (esActivo) {
        enlace.setAttribute('aria-current', 'page');
      } else {
        enlace.removeAttribute('aria-current');
      }
    });

    document.getElementById('contenido-principal')?.focus();
  }
}

document.addEventListener('DOMContentLoaded', () => new App());
