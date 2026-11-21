import { ApiClient, relanzarSiNoEsErrorDeApi, Sesion } from './api.js';
import { logo, paginaError } from './componentes.js';
import { crearElemento } from './utilidades.js';

export async function pintarPagina(vista, construir) {
  try {
    vista.replaceChildren(await construir());
  } catch (error) {
    relanzarSiNoEsErrorDeApi(error);

    const sesionYaCerrada = error.codigoEstado === 401;

    if (!sesionYaCerrada) {
      vista.replaceChildren(paginaError(error.codigoEstado, error.message));
    }
  }
}

const TITULO_APLICACION = 'Gestor de Alquiler';

const SECCIONES = [
  ['panel', 'home.html', 'Panel'],
  ['vehiculos', 'vehiculos.html', 'Vehículos'],
  ['clientes', 'clientes.html', 'Clientes'],
  ['reservas', 'reservas.html', 'Reservas'],
  ['disponibilidad', 'disponibilidad.html', 'Disponibilidad'],
];

function irAlInicioDeSesion() {
  window.location.replace('index.html');
}

async function cerrarSesion() {
  await ApiClient.post('/auth/logout').catch(() => null);
  Sesion.limpiar();
  irAlInicioDeSesion();
}

export function iniciarPagina({ seccion, titulo }) {
  if (!Sesion.haySesion()) {
    irAlInicioDeSesion();

    return null;
  }

  ApiClient.alNoAutorizarse = irAlInicioDeSesion;
  document.title = `${titulo} | ${TITULO_APLICACION}`;

  document.getElementById('cabecera').append(
    crearElemento(
      'div',
      { class: 'barra' },
      crearElemento('a', { class: 'barra__marca', href: 'home.html', 'aria-label': 'CarRent, ir al panel' }, logo()),
      crearElemento(
        'nav',
        { class: 'barra__navegacion', 'aria-label': 'Secciones' },
        SECCIONES.map(([clave, direccion, texto]) =>
          crearElemento(
            'a',
            {
              class: clave === seccion ? 'barra__enlace barra__enlace--activo' : 'barra__enlace',
              href: direccion,
              'aria-current': clave === seccion ? 'page' : null,
            },
            texto,
          ),
        ),
      ),
      crearElemento(
        'button',
        { type: 'button', class: 'boton boton--secundario boton--chico', onclick: cerrarSesion },
        'Cerrar sesión',
      ),
    ),
  );

  return document.getElementById('vista');
}
