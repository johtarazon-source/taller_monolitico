import { ApiClient, relanzarSiNoEsErrorDeApi, Sesion } from '../api.js';
import { crearElemento } from '../utilidades.js';

export function paginaLogin() {
  const formulario = document.getElementById('formulario-inicio-sesion');
  const errorInicioSesion = document.getElementById('inicio-sesion-error');

  function mostrarError(texto) {
    errorInicioSesion.replaceChildren(crearElemento('span', {}, texto));
    errorInicioSesion.hidden = false;
  }

  async function iniciarSesion(evento) {
    evento.preventDefault();

    const boton = formulario.querySelector('button[type="submit"]');
    const usuario = formulario.usuario.value.trim();
    const password = formulario.password.value;

    errorInicioSesion.hidden = true;

    if (usuario === '' || password === '') {
      mostrarError('Usuario y contraseña son obligatorios.');

      return;
    }

    boton.disabled = true;

    try {
      const { data } = await ApiClient.post('/auth/login', { usuario, password });

      Sesion.guardar(data.token);
      window.location.href = 'home.html';
    } catch (error) {
      relanzarSiNoEsErrorDeApi(error);

      mostrarError(error.message);
      boton.disabled = false;
    }
  }

  if (Sesion.haySesion()) {
    window.location.replace('home.html');
  }

  formulario.addEventListener('submit', iniciarSesion);
}
