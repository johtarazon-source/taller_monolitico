import { API_BASE_URL } from '../config.js';
import { SessionService } from './SessionService.js';

/**
 * Error de la API con el mensaje y los datos de error que devuelve el backend.
 */
export class ApiError extends Error {
  constructor(mensaje, status, errores = null) {
    super(mensaje);
    this.status = status;
    this.errores = errores;
  }
}

/**
 * Punto unico de acceso HTTP a la API. Agrega el token Bearer, interpreta el
 * formato estandar { ok, mensaje, data } y maneja la expiracion de sesion (401).
 */
export class ApiClient {
  static onNoAutorizado = null;

  static async solicitar(ruta, { metodo = 'GET', cuerpo = null, query = null } = {}) {
    const url = new URL(`${API_BASE_URL}${ruta}`);

    if (query) {
      Object.entries(query)
        .filter(([, valor]) => valor !== null && valor !== undefined && valor !== '')
        .forEach(([clave, valor]) => url.searchParams.set(clave, valor));
    }

    const cabeceras = { 'Content-Type': 'application/json' };
    const token = SessionService.obtenerToken();

    if (token) {
      cabeceras.Authorization = `Bearer ${token}`;
    }

    const respuesta = await fetch(url.toString(), {
      method: metodo,
      headers: cabeceras,
      body: cuerpo ? JSON.stringify(cuerpo) : null,
    });

    const payload = await respuesta.json().catch(() => null);

    if (respuesta.status === 401) {
      SessionService.limpiar();

      if (typeof ApiClient.onNoAutorizado === 'function') {
        ApiClient.onNoAutorizado();
      }
    }

    if (!respuesta.ok || !payload?.ok) {
      const mensaje = payload?.mensaje ?? 'Ocurrio un error al comunicarse con el servidor.';

      throw new ApiError(mensaje, respuesta.status, payload?.data ?? null);
    }

    return payload.data;
  }

  static get(ruta, query) {
    return ApiClient.solicitar(ruta, { metodo: 'GET', query });
  }

  static post(ruta, cuerpo) {
    return ApiClient.solicitar(ruta, { metodo: 'POST', cuerpo });
  }

  static put(ruta, cuerpo) {
    return ApiClient.solicitar(ruta, { metodo: 'PUT', cuerpo });
  }

  static delete(ruta) {
    return ApiClient.solicitar(ruta, { metodo: 'DELETE' });
  }
}
