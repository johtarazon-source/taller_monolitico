import { SessionService } from './services/SessionService.js';
import { ApiClient } from './services/ApiClient.js';
import { AuthController } from './controllers/AuthController.js';

/**
 * Arranque de la aplicacion: alterna entre la pantalla de login y el shell
 * con la navegacion entre modulos (vehiculos, clientes, reservas, disponibilidad).
 */
class App {
  constructor() {
    this.vistaLogin = document.getElementById('vista-login');
    this.shell = document.getElementById('shell');
    this.usuarioActual = document.getElementById('usuario-actual');
    this.enlacesNav = document.querySelectorAll('.nav__enlace');
    this.vistasModulo = document.querySelectorAll('.vista-modulo');

    ApiClient.onNoAutorizado = () => this.mostrarLogin();

    this.authController = new AuthController({
      onAutenticado: (usuario) => this.mostrarShell(usuario),
      onCerrarSesion: () => this.mostrarLogin(),
    });

    this.enlacesNav.forEach((enlace) => {
      enlace.addEventListener('click', (evento) => {
        evento.preventDefault();
        this.navegarA(enlace.dataset.modulo);
      });
    });

    this.iniciar();
  }

  iniciar() {
    if (SessionService.haySesion()) {
      this.mostrarShell(SessionService.obtenerUsuario());
      return;
    }

    this.mostrarLogin();
  }

  mostrarLogin() {
    this.vistaLogin.hidden = false;
    this.shell.hidden = true;
  }

  mostrarShell(usuario) {
    this.usuarioActual.textContent = usuario;
    this.vistaLogin.hidden = true;
    this.shell.hidden = false;
    this.navegarA('vehiculos');
  }

  navegarA(modulo) {
    this.vistasModulo.forEach((vista) => {
      vista.hidden = vista.dataset.modulo !== modulo;
    });

    this.enlacesNav.forEach((enlace) => {
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
