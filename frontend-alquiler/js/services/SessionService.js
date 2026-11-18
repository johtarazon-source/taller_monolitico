const CLAVE_TOKEN = 'alquiler_token';
const CLAVE_USUARIO = 'alquiler_usuario';

/**
 * Guarda y recupera la sesion del usuario administrador en localStorage.
 */
export class SessionService {
  static guardar(token, usuario) {
    localStorage.setItem(CLAVE_TOKEN, token);
    localStorage.setItem(CLAVE_USUARIO, usuario);
  }

  static obtenerToken() {
    return localStorage.getItem(CLAVE_TOKEN);
  }

  static obtenerUsuario() {
    return localStorage.getItem(CLAVE_USUARIO);
  }

  static haySesion() {
    return Boolean(this.obtenerToken());
  }

  static limpiar() {
    localStorage.removeItem(CLAVE_TOKEN);
    localStorage.removeItem(CLAVE_USUARIO);
  }
}
