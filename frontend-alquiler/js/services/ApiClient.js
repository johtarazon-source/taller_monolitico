import { URL_BASE_API } from '../config.js';
import { SessionService } from './SessionService.js';

/**
 * Error de la API con el mensaje y los datos de error que devuelve el backend.
 */
export class ApiError extends Error {
  constructor(mensaje, codigoEstado, errores = null) {
    super(mensaje);
    this.codigoEstado = codigoEstado;
    this.errores = errores;
  }
}

/**
 * Punto unico de acceso HTTP a la API. Agrega el token Bearer, interpreta el
 * formato estandar { ok, mensaje, data } y maneja la expiracion de sesion (401).
 */
export class ApiClient {
  static alNoAutorizarse = null;

  static async solicitar(ruta, { metodo = 'GET', cuerpo = null, parametrosConsulta = null } = {}) {
    const direccion = new URL(`${URL_BASE_API}${ruta}`);

    if (parametrosConsulta) {
      Object.entries(parametrosConsulta)
        .filter(([, valor]) => valor !== null && valor !== undefined && valor !== '')
        .forEach(([clave, valor]) => direccion.searchParams.set(clave, valor));
    }

    const cabeceras = { 'Content-Type': 'application/json' };
    const token = SessionService.obtenerToken();

    if (token) {
      cabeceras.Authorization = `Bearer ${token}`;
    }

    const respuesta = await fetch(direccion.toString(), {
      method: metodo,
      headers: cabeceras,
      body: cuerpo ? JSON.stringify(cuerpo) : null,
    });

    const cuerpoRespuesta = await respuesta.json().catch(() => null);

    if (respuesta.status === 401) {
      SessionService.limpiar();

      if (typeof ApiClient.alNoAutorizarse === 'function') {
        ApiClient.alNoAutorizarse();
      }
    }

    if (!respuesta.ok || !cuerpoRespuesta?.ok) {
      const mensaje = cuerpoRespuesta?.mensaje ?? 'Ocurrio un error al comunicarse con el servidor.';

      throw new ApiError(mensaje, respuesta.status, cuerpoRespuesta?.data ?? null);
    }

    return cuerpoRespuesta.data;
  }

  static get(ruta, parametrosConsulta) {
    return ApiClient.solicitar(ruta, { metodo: 'GET', parametrosConsulta });
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
