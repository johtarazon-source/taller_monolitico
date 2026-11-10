import { API_BASE_URL } from '../config.js';
import { SessionService } from './SessionService.js';

/**
 * Unico punto de fetch de la aplicacion: agrega el token, parsea el
 * formato estandar { ok, mensaje, data } y limpia la sesion ante un 401.
 */
async function request(path, { method = 'GET', body = null, auth = true } = {}) {
  const headers = { 'Content-Type': 'application/json' };
  const token = SessionService.getToken();

  if (auth && token) {
    headers.Authorization = `Bearer ${token}`;
  }

  const response = await fetch(`${API_BASE_URL}${path}`, {
    method,
    headers,
    body: body ? JSON.stringify(body) : null,
  });

  const payload = await response.json().catch(() => ({
    ok: false,
    mensaje: 'Respuesta inválida del servidor.',
    data: null,
  }));

  if (response.status === 401) {
    SessionService.clearToken();
    window.dispatchEvent(new CustomEvent('sesion-expirada'));
  }

  if (!response.ok) {
    throw new Error(payload.mensaje || 'Ocurrió un error inesperado.');
  }

  return payload;
}

export const ApiClient = {
  get: (path) => request(path, { method: 'GET' }),
  post: (path, body, opts = {}) => request(path, { method: 'POST', body, ...opts }),
  put: (path, body) => request(path, { method: 'PUT', body }),
  del: (path) => request(path, { method: 'DELETE' }),
};
