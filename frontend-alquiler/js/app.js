/**
 * Aplicacion web del Gestor de Alquiler (frontend).
 *
 * Un solo archivo para todas las paginas. Secciones, en orden:
 *   1. API: URL base, sesion (token) y cliente HTTP
 *   2. Utilidades: DOM seguro, iconos, formatos y avisos
 *   3. Componentes: campos, formularios, modal, tarjetas y tabla
 *   4. Cabecera: guarda de sesion y menu
 *   5. Paginas: login, panel, vehiculos, clientes, reservas, disponibilidad e historial
 */

/* ===========================================================================
 * Acceso a la API del backend: URL base, token de sesion y cliente HTTP.
 * ========================================================================= */

/**
 * URL base de la API. Se calcula a partir de donde esta servido el frontend, asumiendo que
 * las carpetas frontend-alquiler y backend-alquiler estan juntas (como en XAMPP):
 *   http://localhost:8080/taller_monolitico/frontend-alquiler/  ->
 *   http://localhost:8080/taller_monolitico/backend-alquiler/public/api
 * Si se sirve de otra forma (por ejemplo `php -S localhost:8000 -t public`), reemplaza este valor
 * por la URL real, como 'http://localhost:8000/api'.
 */
const URL_BASE_API = new URL(
  '../backend-alquiler/public/api',
  `${window.location.origin}${window.location.pathname}`,
).href;

const CLAVE_TOKEN = 'alquiler_token';
const CLAVE_USUARIO = 'alquiler_usuario';

/** Guarda en el navegador el token y el usuario de la sesion actual. */
const Sesion = {
  guardar(token, usuario) {
    localStorage.setItem(CLAVE_TOKEN, token);
    localStorage.setItem(CLAVE_USUARIO, usuario);
  },

  obtenerToken() {
    return localStorage.getItem(CLAVE_TOKEN);
  },

  obtenerUsuario() {
    return localStorage.getItem(CLAVE_USUARIO);
  },

  haySesion() {
    return Boolean(localStorage.getItem(CLAVE_TOKEN));
  },

  limpiar() {
    localStorage.removeItem(CLAVE_TOKEN);
    localStorage.removeItem(CLAVE_USUARIO);
  },
};

/** Error devuelto por la API (o por la red) con el codigo HTTP y los errores por campo. */
class ApiError extends Error {
  /**
   * @param {string} mensaje
   * @param {number} codigoEstado 0 cuando no hubo respuesta del servidor
   * @param {Record<string, string> | null} errores errores de validacion por campo (422)
   */
  constructor(mensaje, codigoEstado, errores = null) {
    super(mensaje);
    this.name = 'ApiError';
    this.codigoEstado = codigoEstado;
    this.errores = errores;
  }
}

/** Cliente HTTP. Todas las respuestas de la API tienen la forma { ok, mensaje, data }. */
class ApiClient {
  /** Funcion que se ejecuta cuando el token falta o expiro (401). */
  static alNoAutorizarse = null;

  /**
   * @param {string} metodo
   * @param {string} ruta ruta de la API que empieza con "/"
   * @param {{ cuerpo?: object, consulta?: Record<string, unknown> }} [opciones]
   * @returns {Promise<{ data: any, mensaje: string }>}
   */
  static async solicitar(metodo, ruta, { cuerpo, consulta } = {}) {
    const url = new URL(`${URL_BASE_API}${ruta}`);

    for (const [clave, valor] of Object.entries(consulta ?? {})) {
      if (valor !== null && valor !== undefined && valor !== '') {
        url.searchParams.set(clave, String(valor));
      }
    }

    const cabeceras = { Accept: 'application/json' };
    const token = Sesion.obtenerToken();

    if (token) {
      cabeceras.Authorization = `Bearer ${token}`;
    }

    if (cuerpo !== undefined) {
      cabeceras['Content-Type'] = 'application/json';
    }

    let respuesta;

    try {
      respuesta = await fetch(url, {
        method: metodo,
        headers: cabeceras,
        body: cuerpo === undefined ? null : JSON.stringify(cuerpo),
      });
    } catch {
      throw new ApiError('No se pudo conectar con el servidor. Revise su conexión.', 0);
    }

    const cuerpoRespuesta = await respuesta.json().catch(() => null);

    if (respuesta.status === 401 && ruta !== '/auth/login') {
      Sesion.limpiar();
      ApiClient.alNoAutorizarse?.();
    }

    if (!respuesta.ok || !cuerpoRespuesta?.ok) {
      const datos = cuerpoRespuesta?.data;
      const errores = datos && typeof datos === 'object' && !Array.isArray(datos) ? datos : null;

      throw new ApiError(
        cuerpoRespuesta?.mensaje ?? 'Ocurrió un error al comunicarse con el servidor.',
        respuesta.status,
        errores,
      );
    }

    return { data: cuerpoRespuesta.data, mensaje: cuerpoRespuesta.mensaje };
  }

  static get(ruta, consulta) {
    return ApiClient.solicitar('GET', ruta, { consulta });
  }

  static post(ruta, cuerpo = {}) {
    return ApiClient.solicitar('POST', ruta, { cuerpo });
  }

  static put(ruta, cuerpo = {}) {
    return ApiClient.solicitar('PUT', ruta, { cuerpo });
  }

  static delete(ruta) {
    return ApiClient.solicitar('DELETE', ruta);
  }
}

/* ===========================================================================
 * Utilidades compartidas: construccion segura del DOM, iconos, formatos y avisos.
 * Los datos de la API siempre se insertan como texto (nunca con innerHTML).
 * ========================================================================= */

const ESPACIO_SVG = 'http://www.w3.org/2000/svg';

/**
 * Crea un elemento HTML. Los hijos pueden ser texto, nodos, arreglos o valores vacios.
 * Atributos especiales: "class", "dataset" y los eventos "onclick", "onsubmit", etc.
 *
 * @param {string} etiqueta
 * @param {Record<string, any>} [atributos]
 * @param {...any} hijos
 * @returns {HTMLElement}
 */
function h(etiqueta, atributos = {}, ...hijos) {
  const elemento = document.createElement(etiqueta);
  aplicarAtributos(elemento, atributos);
  agregarHijos(elemento, hijos);

  return elemento;
}

/** Igual que h(), pero para elementos SVG. */
function svg(etiqueta, atributos = {}, ...hijos) {
  const elemento = document.createElementNS(ESPACIO_SVG, etiqueta);
  aplicarAtributos(elemento, atributos);
  agregarHijos(elemento, hijos);

  return elemento;
}

function aplicarAtributos(elemento, atributos) {
  for (const [nombre, valor] of Object.entries(atributos ?? {})) {
    if (valor === null || valor === undefined || valor === false) {
      continue;
    }

    if (nombre === 'dataset') {
      Object.assign(elemento.dataset, valor);
    } else if (nombre.startsWith('on') && typeof valor === 'function') {
      elemento.addEventListener(nombre.slice(2), valor);
    } else {
      elemento.setAttribute(nombre, valor === true ? '' : String(valor));
    }
  }
}

function agregarHijos(padre, hijos) {
  for (const hijo of hijos.flat(Infinity)) {
    if (hijo === null || hijo === undefined || hijo === false) {
      continue;
    }

    padre.append(hijo instanceof Node ? hijo : String(hijo));
  }
}

/* ---------------------------------------------------------------------------
 * Iconos (trazos SVG de 24x24)
 * ------------------------------------------------------------------------- */

const TRAZOS_ICONOS = {
  exito: ['M20 6 9 17l-5-5'],
  error: ['M18 6 6 18', 'M6 6l12 12'],
  alerta: [
    'M12 9v4',
    'M12 17h.01',
    'm10.29 3.86-8.18 14.18A1 1 0 0 0 3 19.5h18a1 1 0 0 0 .89-1.46L13.71 3.86a1 1 0 0 0-1.42 0z',
  ],
  vehiculo: [
    'M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11M3 11h18v5a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-1H7v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z',
  ],
  llave: ['M13 15.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0zM21 2l-9.6 9.6M15.5 7.5l3 3L22 7l-3-3'],
  taller: [
    'M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z',
  ],
  panel: ['M3 3h7v9H3zM14 3h7v5h-7zM14 12h7v9h-7zM3 16h7v5H3z'],
  flecha: ['M7 7h10v10M7 17 17 7'],
  calendario: [
    'M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM3 10h18M8 3v4M16 3v4M12 13v5M9.5 15.5h5',
  ],
  mas: ['M12 5v14M5 12h14'],
};

/**
 * Icono SVG de trazo que hereda el color del texto.
 *
 * @param {keyof typeof TRAZOS_ICONOS} nombre
 * @param {{ grosor?: number, clase?: string }} [opciones]
 */
function icono(nombre, { grosor = 2, clase = 'icono' } = {}) {
  return svg(
    'svg',
    {
      class: clase,
      viewBox: '0 0 24 24',
      fill: 'none',
      stroke: 'currentColor',
      'stroke-width': grosor,
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
      'aria-hidden': 'true',
      focusable: 'false',
    },
    TRAZOS_ICONOS[nombre].map((trazo) => svg('path', { d: trazo })),
  );
}

/* ---------------------------------------------------------------------------
 * Formatos
 * ------------------------------------------------------------------------- */

const MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

const ETIQUETAS_ESTADO = {
  disponible: 'Disponible',
  alquilado: 'Alquilado',
  mantenimiento: 'Mantenimiento',
  activa: 'Activa',
  completada: 'Completada',
  cancelada: 'Cancelada',
  vencida: 'Vencida',
  programada: 'Programada',
};

/** Separa "2026-11-13" en anio, mes y dia numericos (sin usar zonas horarias). */
function partesFecha(fechaIso) {
  const [anio, mes, dia] = String(fechaIso).slice(0, 10).split('-').map(Number);

  return { anio, mes, dia };
}

/** "2026-11-13" -> "13 nov 2026" */
function formatearFecha(fechaIso) {
  if (!fechaIso) {
    return '-';
  }

  const { anio, mes, dia } = partesFecha(fechaIso);

  return `${dia} ${MESES[mes - 1]} ${anio}`;
}

/** "2026-11-20" -> "20 nov" */
function formatearFechaCorta(fechaIso) {
  if (!fechaIso) {
    return '-';
  }

  const { mes, dia } = partesFecha(fechaIso);

  return `${dia} ${MESES[mes - 1]}`;
}

/** Dias incluidos entre dos fechas (ambos extremos cuentan). */
function contarDias(fechaInicio, fechaFin) {
  const inicio = partesFecha(fechaInicio);
  const fin = partesFecha(fechaFin);
  const utcInicio = Date.UTC(inicio.anio, inicio.mes - 1, inicio.dia);
  const utcFin = Date.UTC(fin.anio, fin.mes - 1, fin.dia);

  return Math.round((utcFin - utcInicio) / 86400000) + 1;
}

/** Fecha de hoy en el navegador con formato AAAA-MM-DD. */
function hoyIso() {
  const hoy = new Date();
  const mes = String(hoy.getMonth() + 1).padStart(2, '0');
  const dia = String(hoy.getDate()).padStart(2, '0');

  return `${hoy.getFullYear()}-${mes}-${dia}`;
}

/** "Marca Modelo (anio)" */
function textoVehiculo(vehiculo) {
  return vehiculo ? `${vehiculo.marca} ${vehiculo.modelo} (${vehiculo.anio})` : '-';
}

function pluralizar(cantidad, singular, plural) {
  return cantidad === 1 ? singular : plural;
}

/** Etiqueta de estado coloreada. */
function insignia(estado) {
  return h('span', { class: `insignia insignia--${estado}` }, ETIQUETAS_ESTADO[estado] ?? estado);
}

/* ---------------------------------------------------------------------------
 * Avisos (mensajes de exito o error de la pagina)
 * ------------------------------------------------------------------------- */

/** Muestra un aviso arriba de la pagina. */
function avisar(texto, tipo = 'exito') {
  document.getElementById('avisos').replaceChildren(
    h(
      'p',
      { class: `alerta alerta--${tipo}`, role: tipo === 'error' ? 'alert' : 'status' },
      icono(tipo === 'exito' ? 'exito' : 'error'),
      h('span', {}, texto),
    ),
  );
}

/* ---------------------------------------------------------------------------
 * Direccion de la pagina (filtros en la URL) y logotipo
 * ------------------------------------------------------------------------- */

/** Parametros de la URL actual (por ejemplo "?estado=disponible"). */
function leerConsulta() {
  return new URLSearchParams(window.location.search);
}

/** Direccion con parametros de consulta, omitiendo los vacios. */
function conConsulta(direccion, parametros) {
  const consulta = new URLSearchParams();

  for (const [clave, valor] of Object.entries(parametros)) {
    if (valor !== null && valor !== undefined && valor !== '') {
      consulta.set(clave, String(valor));
    }
  }

  const texto = consulta.toString();

  return texto === '' ? direccion : `${direccion}?${texto}`;
}

/** Logotipo CarRent. */
function logo() {
  return h(
    'span',
    { class: 'logo' },
    svg(
      'svg',
      {
        class: 'logo__marca',
        viewBox: '0 0 40 40',
        fill: 'none',
        stroke: 'currentColor',
        'stroke-linecap': 'round',
        'aria-hidden': 'true',
        focusable: 'false',
      },
      svg('path', { d: 'M31 10.5A14 14 0 1 0 31 29.5', 'stroke-width': 4.5 }),
      svg('path', { d: 'M25.5 16a6.5 6.5 0 1 0 0 8', 'stroke-width': 3.5 }),
    ),
    h('span', { class: 'logo__texto' }, 'CarRent'),
  );
}

/* ===========================================================================
 * Piezas de interfaz compartidas por las paginas: campos y formularios con errores por campo,
 * dialogo de confirmacion, tarjetas y tabla de reservas.
 * ========================================================================= */


/* ===========================================================================
 * Estructura de pagina
 * ========================================================================= */

function cabeceraPagina(titulo, descripcion, accion = null) {
  return h(
    'header',
    { class: 'pagina__cabecera' },
    h(
      'div',
      {},
      h('h1', {}, titulo),
      descripcion ? h('p', { class: 'texto-suave' }, descripcion) : null,
    ),
    accion,
  );
}

/** Enlace con aspecto de boton. */
function enlaceBoton(texto, direccion, clase) {
  return h('a', { class: `boton ${clase}`, href: direccion }, texto);
}

function botonAccion(texto, clase, alHacerClic) {
  return h(
    'button',
    { type: 'button', class: `boton boton--chico ${clase}`, onclick: alHacerClic },
    texto,
  );
}

function estadoVacio(texto) {
  return h('p', { class: 'estado-vacio' }, texto);
}

/** Contenido de una pagina de error (404, 409, sin conexion, etc.). */
function paginaError(codigoEstado, mensaje) {
  return h(
    'section',
    { class: 'pagina' },
    cabeceraPagina(codigoEstado ? `Error ${codigoEstado}` : 'Sin conexión', mensaje),
    h('p', {}, enlaceBoton('Ir al inicio', 'home.html', 'boton--secundario')),
  );
}

/**
 * Pinta una pagina: ejecuta "construir" (que consulta la API y devuelve el contenido) y lo coloca
 * en la vista. Si la API falla (404, red, etc.) muestra la pagina de error.
 */
async function pintarPagina(vista, construir) {
  try {
    vista.replaceChildren(await construir());
  } catch (error) {
    if (!(error instanceof ApiError)) {
      throw error;
    }

    // Con 401 ya se limpio la sesion y se esta yendo al inicio de sesion.
    if (error.codigoEstado !== 401) {
      vista.replaceChildren(paginaError(error.codigoEstado, error.message));
    }
  }
}

/* ===========================================================================
 * Campos y formularios
 * ========================================================================= */

/** Campo de texto con etiqueta. Los errores se agregan despues con mostrarErrores(). */
function campo({ nombre, etiqueta, tipo = 'text', valor = '', ayuda = null, ancho = false, atributos = {} }) {
  const id = `campo-${nombre}`;

  return h(
    'div',
    { class: ancho ? 'campo campo--ancho' : 'campo' },
    h('label', { class: 'campo__etiqueta', for: id }, etiqueta),
    h('input', {
      class: 'campo__control',
      id,
      name: nombre,
      type: tipo,
      value: valor ?? '',
      ...atributos,
    }),
    ayuda ? h('small', { class: 'texto-suave' }, ayuda) : null,
  );
}

/** Lista desplegable con etiqueta. Las opciones son { valor, texto, deshabilitada }. */
function campoSeleccion({ nombre, etiqueta, opciones, seleccionado = '', ancho = false, requerido = false }) {
  const id = `campo-${nombre}`;

  return h(
    'div',
    { class: ancho ? 'campo campo--ancho' : 'campo' },
    h('label', { class: 'campo__etiqueta', for: id }, etiqueta),
    h(
      'select',
      { class: 'campo__control', id, name: nombre, required: requerido },
      opciones.map((opcion) =>
        h(
          'option',
          {
            value: opcion.valor,
            disabled: opcion.deshabilitada,
            selected: String(opcion.valor) === String(seleccionado),
          },
          opcion.texto,
        ),
      ),
    ),
  );
}

/** Parrafo de alerta oculto donde se muestran los errores generales de un formulario. */
function alertaGeneral() {
  return h('p', { class: 'alerta alerta--error', role: 'alert', hidden: true });
}

function mostrarErrorGeneral(alerta, texto) {
  alerta.replaceChildren(icono('alerta'), h('span', {}, texto));
  alerta.hidden = false;
}

/** Quita los errores pintados por mostrarErrores() y oculta la alerta general. */
function limpiarErrores(formulario, alerta) {
  formulario.querySelectorAll('.mensaje-error').forEach((nodo) => nodo.remove());
  formulario
    .querySelectorAll('[aria-invalid]')
    .forEach((nodo) => nodo.removeAttribute('aria-invalid'));
  alerta.hidden = true;
}

/**
 * Pinta cada error de validacion bajo su campo.
 *
 * @param {HTMLElement} formulario
 * @param {Record<string, string>} errores
 * @returns {string[]} mensajes que no corresponden a ningun campo del formulario
 */
function mostrarErrores(formulario, errores) {
  const sinCampo = [];
  let primerControl = null;

  for (const [nombre, mensaje] of Object.entries(errores)) {
    const control = formulario.elements?.[nombre] ?? formulario.querySelector(`[name="${nombre}"]`);
    const contenedor = control?.closest?.('.campo');

    if (!contenedor) {
      sinCampo.push(mensaje);
      continue;
    }

    control.setAttribute('aria-invalid', 'true');
    contenedor.append(
      h('small', { class: 'mensaje-error' }, icono('alerta'), h('span', {}, mensaje)),
    );
    primerControl ??= control;
  }

  primerControl?.focus();

  return sinCampo;
}

/**
 * Conecta el envio de un formulario con una accion que llama a la API. Los errores 422 se pintan
 * bajo cada campo y los demas (409, red, etc.) en la alerta general.
 */
function manejarEnvio(formulario, alerta, accion) {
  formulario.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    limpiarErrores(formulario, alerta);

    const boton = formulario.querySelector('button[type="submit"]');
    boton.disabled = true;

    try {
      await accion(Object.fromEntries(new FormData(formulario)));
    } catch (error) {
      if (!(error instanceof ApiError)) {
        throw error;
      }

      if (error.codigoEstado === 422 && error.errores) {
        const sinCampo = mostrarErrores(formulario, error.errores);

        if (sinCampo.length > 0) {
          mostrarErrorGeneral(alerta, sinCampo.join(' '));
        }
      } else {
        mostrarErrorGeneral(alerta, error.message);
      }
    } finally {
      boton.disabled = false;
    }
  });
}

/* ===========================================================================
 * Dialogo de confirmacion y acciones
 * ========================================================================= */

/**
 * Pide confirmacion en un dialogo modal.
 *
 * @returns {Promise<{ aceptado: boolean, mantenimiento: boolean }>}
 */
function confirmar({ titulo, mensaje, textoBoton, peligrosa = false, opcionMantenimiento = false }) {
  return new Promise((resolver) => {
    const casilla = opcionMantenimiento
      ? h('input', { type: 'checkbox', name: 'mantenimiento' })
      : null;

    const dialogo = h(
      'dialog',
      { class: 'dialogo', 'aria-labelledby': 'dialogo-titulo' },
      h(
        'form',
        { class: 'formulario', method: 'dialog' },
        h('h1', { id: 'dialogo-titulo' }, peligrosa ? icono('alerta') : null, titulo),
        h('p', {}, mensaje),
        casilla
          ? h('label', { class: 'casilla' }, casilla, 'Enviar el vehículo a mantenimiento')
          : null,
        h(
          'div',
          { class: 'formulario__pie' },
          h(
            'button',
            {
              type: 'button',
              class: 'boton boton--secundario',
              onclick: () => dialogo.close('cancelar'),
            },
            'Volver',
          ),
          h(
            'button',
            {
              type: 'submit',
              value: 'aceptar',
              class: `boton ${peligrosa ? 'boton--peligro' : 'boton--primario'}`,
            },
            textoBoton,
          ),
        ),
      ),
    );

    dialogo.addEventListener('close', () => {
      resolver({
        aceptado: dialogo.returnValue === 'aceptar',
        mantenimiento: casilla?.checked ?? false,
      });
      dialogo.remove();
    });

    document.body.append(dialogo);
    dialogo.showModal();
  });
}

/** Ejecuta una accion de la API desde un boton, avisa el resultado y vuelve a pintar la lista. */
async function ejecutarAccion(accion, recargar) {
  try {
    const { mensaje } = await accion();
    avisar(mensaje);
  } catch (error) {
    if (!(error instanceof ApiError)) {
      throw error;
    }

    avisar(error.message, 'error');
  }

  await recargar();
}

/** Pide confirmacion y elimina un recurso (vehiculo o cliente). */
async function eliminarRecurso({ ruta, titulo, mensaje, recargar }) {
  const { aceptado } = await confirmar({ titulo, mensaje, textoBoton: 'Eliminar', peligrosa: true });

  if (aceptado) {
    await ejecutarAccion(() => ApiClient.delete(ruta), recargar);
  }
}

/* ===========================================================================
 * Tarjetas y tabla
 * ========================================================================= */

function tarjetaVehiculo(vehiculo, acciones, nota = null) {
  return h(
    'article',
    { class: 'tarjeta-vehiculo' },
    h(
      'header',
      { class: 'tarjeta-vehiculo__cabecera' },
      h('h3', {}, `${vehiculo.marca} ${vehiculo.modelo}`),
      insignia(vehiculo.estado),
    ),
    h('p', { class: 'texto-suave' }, `${vehiculo.anio} · ${vehiculo.categoria || 'Sin categoría'}`),
    nota ? h('small', { class: 'texto-suave' }, nota) : null,
    h('div', { class: 'tarjeta-vehiculo__acciones' }, acciones),
  );
}

function tarjetaCliente(cliente, acciones) {
  return h(
    'article',
    { class: 'tarjeta-cliente' },
    h(
      'header',
      { class: 'tarjeta-cliente__cabecera' },
      h('h3', {}, cliente.nombre),
      cliente.numero_licencia
        ? null
        : h('span', { class: 'insignia insignia--cancelada' }, 'Sin licencia'),
    ),
    h(
      'p',
      { class: 'texto-suave' },
      `${cliente.telefono || 'Sin teléfono'} · ${cliente.correo || 'Sin correo'}`,
    ),
    cliente.numero_licencia
      ? h('small', { class: 'texto-suave' }, `Licencia: ${cliente.numero_licencia}`)
      : null,
    h('div', { class: 'tarjeta-cliente__acciones' }, acciones),
  );
}

function tarjetaDato(etiqueta, valor, variante = null) {
  return h(
    'article',
    { class: variante ? `tarjeta-dato tarjeta-dato--${variante}` : 'tarjeta-dato' },
    h('span', { class: 'tarjeta-dato__etiqueta' }, etiqueta),
    h('strong', { class: 'tarjeta-dato__valor' }, valor),
  );
}

/**
 * Tabla de reservas. Con "acciones" muestra los botones de devolucion y cancelacion.
 *
 * @param {object[]} reservas
 * @param {{ conCliente?: boolean, conVehiculo?: boolean,
 *           acciones?: { alDevolver: Function, alCancelar: Function } | null }} [opciones]
 */
function tablaReservas(reservas, { conCliente = true, conVehiculo = true, acciones = null } = {}) {
  const celda = (titulo, ...contenido) => h('td', { dataset: { titulo } }, contenido);

  const filas = reservas.map((reserva) => {
    const estadoVisible = reserva.estado_visible;
    const botones = [];

    if (acciones && reserva.estado === 'activa') {
      // Si todavia no comienza (programada) solo se puede cancelar.
      if (estadoVisible !== 'programada') {
        botones.push(
          botonAccion('Registrar devolución', 'boton--primario', () => acciones.alDevolver(reserva)),
        );
      }

      botones.push(
        botonAccion('Cancelar', 'boton--texto-peligro', () => acciones.alCancelar(reserva)),
      );
    }

    return h(
      'tr',
      { class: estadoVisible === 'vencida' ? 'tabla__fila--alerta' : null },
      celda('#', reserva.id),
      conCliente ? celda('Cliente', reserva.cliente?.nombre ?? 'Cliente eliminado') : null,
      conVehiculo ? celda('Vehículo', textoVehiculo(reserva.vehiculo)) : null,
      celda('Desde', formatearFecha(reserva.fecha_inicio)),
      celda('Hasta', formatearFecha(reserva.fecha_fin)),
      celda('Días', contarDias(reserva.fecha_inicio, reserva.fecha_fin)),
      celda('Estado', insignia(estadoVisible)),
      acciones ? h('td', { class: 'tabla__acciones' }, botones) : null,
    );
  });

  return h(
    'div',
    { class: 'tabla-contenedor' },
    h(
      'table',
      { class: 'tabla' },
      h(
        'thead',
        {},
        h(
          'tr',
          {},
          h('th', {}, '#'),
          conCliente ? h('th', {}, 'Cliente') : null,
          conVehiculo ? h('th', {}, 'Vehículo') : null,
          h('th', {}, 'Desde'),
          h('th', {}, 'Hasta'),
          h('th', {}, 'Días'),
          h('th', {}, 'Estado'),
          acciones ? h('th', {}, h('span', { class: 'solo-lectores-pantalla' }, 'Acciones')) : null,
        ),
      ),
      h('tbody', {}, filas),
    ),
  );
}

/* ===========================================================================
 * Parte comun de las paginas de la aplicacion: guarda de sesion, cabecera con el menu y cierre
 * de sesion. Cada pagina (vehiculos.html, clientes.html, ...) la usa al empezar.
 * ========================================================================= */


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
  try {
    await ApiClient.post('/auth/logout');
  } catch {
    // Si el token ya no era valido, de todas formas se cierra la sesion local.
  } finally {
    Sesion.limpiar();
    irAlInicioDeSesion();
  }
}

/**
 * Prepara la pagina: si no hay sesion, vuelve al inicio de sesion; si la hay, pinta la
 * cabecera y devuelve el contenedor donde la pagina coloca su contenido.
 *
 * @param {{ seccion: string, titulo: string }} opciones
 * @returns {HTMLElement | null} el contenedor "#vista", o null si se redirigio al inicio de sesion
 */
function iniciarPagina({ seccion, titulo }) {
  if (!Sesion.haySesion()) {
    irAlInicioDeSesion();

    return null;
  }

  // Un token vencido (401) en cualquier llamada lleva de nuevo al inicio de sesion.
  ApiClient.alNoAutorizarse = irAlInicioDeSesion;
  document.title = `${titulo} | ${TITULO_APLICACION}`;

  document.getElementById('cabecera').append(
    h(
      'div',
      { class: 'barra' },
      h('a', { class: 'barra__marca', href: 'home.html', 'aria-label': 'CarRent, ir al panel' }, logo()),
      h(
        'nav',
        { class: 'barra__navegacion', 'aria-label': 'Secciones' },
        SECCIONES.map(([clave, direccion, texto]) =>
          h(
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
      h(
        'button',
        { type: 'button', class: 'boton boton--secundario boton--chico', onclick: cerrarSesion },
        'Cerrar sesión',
      ),
    ),
  );

  return document.getElementById('vista');
}

/* ===========================================================================
 * Inicio de sesion (index.html). Guarda el token y pasa al panel.
 * ========================================================================= */

function paginaLogin() {
  const formulario = document.getElementById('formulario-inicio-sesion');
  const errorInicioSesion = document.getElementById('inicio-sesion-error');

  function mostrarError(texto) {
    errorInicioSesion.replaceChildren(h('span', {}, texto));
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

      Sesion.guardar(data.token, data.usuario);
      window.location.href = 'home.html';
    } catch (error) {
      if (!(error instanceof ApiError)) {
        throw error;
      }

      mostrarError(error.message);
      boton.disabled = false;
    }
  }

  // Si ya hay una sesion iniciada, se va directo al panel.
  if (Sesion.haySesion()) {
    window.location.replace('home.html');
  }

  formulario.addEventListener('submit', iniciarSesion);
}

/* ===========================================================================
 * Panel general (home.html): reservas recientes, resumen de la flota y vehiculos.
 * ========================================================================= */


function filaReserva(reserva) {
  const vehiculo = reserva.vehiculo;
  const dias = contarDias(reserva.fecha_inicio, reserva.fecha_fin);
  const rango = `${formatearFechaCorta(reserva.fecha_inicio)} → ${formatearFechaCorta(reserva.fecha_fin)}`;
  const contenido = [
    vehiculo
      ? h('span', { class: 'fila-reserva__enlace', 'aria-hidden': 'true' }, icono('flecha'))
      : null,
    h(
      'div',
      { class: 'fila-reserva__datos' },
      h(
        'strong',
        { class: 'fila-reserva__titulo' },
        vehiculo ? `${vehiculo.marca} ${vehiculo.modelo}` : 'Vehículo eliminado',
      ),
      h('span', { class: 'fila-reserva__detalle' }, reserva.cliente?.nombre ?? 'Cliente eliminado'),
      h(
        'span',
        { class: 'fila-reserva__detalle' },
        `${rango} · ${dias} ${pluralizar(dias, 'día', 'días')}`,
      ),
      h('span', { class: 'fila-reserva__estado' }, insignia(reserva.estado_visible)),
    ),
  ];

  return h(
    'li',
    { class: 'fila-reserva' },
    vehiculo
      ? h(
          'a',
          {
            class: 'fila-reserva__contenido',
            href: `historial.html?tipo=vehiculo&id=${vehiculo.id}`,
          },
          contenido,
        )
      : h('div', { class: 'fila-reserva__contenido' }, contenido),
  );
}

function indicador({ variante, etiqueta, valor, detalle, porcentaje, destino, textoAccesible }) {
  const relleno = Math.max(0, Math.min(100, porcentaje));

  return h(
    'a',
    { class: `indicador indicador--${variante}`, href: destino },
    h('span', { class: 'indicador__etiqueta' }, etiqueta),
    h('strong', { class: 'indicador__valor' }, valor),
    h('span', { class: 'indicador__detalle' }, detalle),
    h(
      'div',
      { class: 'indicador__medidor' },
      h(
        'span',
        { class: 'indicador__barra', 'aria-hidden': 'true' },
        h('span', { class: 'indicador__relleno', style: `--porcentaje-relleno: ${relleno}` }),
      ),
      h('span', { class: 'indicador__porcentaje' }, `${relleno} %`),
    ),
    h('span', { class: 'solo-lectores-pantalla' }, textoAccesible),
  );
}

/** Grafico de dona con el porcentaje de vehiculos disponibles. */
function dona(porcentaje, disponibles, total) {
  const radio = 52;
  const circunferencia = 2 * Math.PI * radio;
  const arco = Number(((circunferencia * Math.max(0, Math.min(100, porcentaje))) / 100).toFixed(2));

  return h(
    'div',
    {
      class: 'dona',
      role: 'img',
      'aria-label': `${porcentaje}% de disponibilidad: ${disponibles} de ${total} vehículos disponibles`,
    },
    svg(
      'svg',
      { class: 'dona__svg', viewBox: '0 0 120 120', 'aria-hidden': 'true', focusable: 'false' },
      svg('circle', { class: 'dona__pista', cx: 60, cy: 60, r: radio }),
      arco > 0
        ? svg('circle', {
            class: 'dona__arco',
            cx: 60,
            cy: 60,
            r: radio,
            'stroke-dasharray': `${arco} ${Number(circunferencia.toFixed(2))}`,
          })
        : null,
    ),
    h(
      'div',
      { class: 'dona__centro', 'aria-hidden': 'true' },
      h('strong', { class: 'dona__valor' }, `${porcentaje}%`),
    ),
  );
}

function vacioPanel(nombreIcono, titulo, descripcion) {
  return h(
    'div',
    { class: 'vacio' },
    icono(nombreIcono, { grosor: 1.3, clase: 'vacio__icono' }),
    h('strong', { class: 'vacio__titulo' }, titulo),
    h('p', { class: 'vacio__texto' }, descripcion),
  );
}

function iconoRecuadro(nombreIcono) {
  return h(
    'span',
    { class: 'icono-recuadro', 'aria-hidden': 'true' },
    icono(nombreIcono, { grosor: 1.8 }),
  );
}

async function construirPanel() {
  const { data: resumen } = await ApiClient.get('/resumen');
  const flota = resumen.vehiculos;
  const vencidas = resumen.vencidas.length;
  const porcentaje = (parte, total) => (total > 0 ? Math.round((parte / total) * 100) : 0);
  const porcentajeDisponible = porcentaje(flota.disponible, flota.total);
  const detalleVehiculos = `de ${flota.total} ${pluralizar(flota.total, 'vehículo', 'vehículos')}`;

  const indicadores = [
    {
      variante: 'disponibles',
      etiqueta: 'Disponibles',
      valor: flota.disponible,
      detalle: detalleVehiculos,
      porcentaje: porcentajeDisponible,
      destino: 'vehiculos.html?estado=disponible',
      textoAccesible: 'Ver vehículos disponibles',
    },
    {
      variante: 'rentados',
      etiqueta: 'Rentados',
      valor: flota.alquilado,
      detalle: detalleVehiculos,
      porcentaje: porcentaje(flota.alquilado, flota.total),
      destino: 'vehiculos.html?estado=alquilado',
      textoAccesible: 'Ver vehículos rentados',
    },
    {
      variante: 'clientes',
      etiqueta: 'Clientes',
      valor: resumen.clientes,
      detalle: `${resumen.clientes_activos} con reserva activa`,
      porcentaje: porcentaje(resumen.clientes_activos, resumen.clientes),
      destino: 'clientes.html',
      textoAccesible: 'Ver clientes',
    },
    {
      variante: 'reservas',
      etiqueta: 'Reservas',
      valor: resumen.reservas_activas,
      detalle: `activas · ${resumen.reservas_total} en total`,
      porcentaje: porcentaje(resumen.reservas_activas, resumen.reservas_total),
      destino: 'reservas.html?estado=activa',
      textoAccesible: 'Ver reservas activas',
    },
  ];

  return h(
    'section',
    { class: 'pagina panel' },
    h(
      'header',
      { class: 'panel__cabecera' },
      h('h1', { class: 'panel__titulo' }, 'Panel de ', h('strong', {}, 'Gestor')),
      h(
        'div',
        { class: 'panel__acciones' },
        h(
          'a',
          { class: 'boton boton--destacado boton--grande', href: 'reservas.html?nueva=1' },
          h('span', { class: 'boton__icono', 'aria-hidden': 'true' }, icono('mas')),
          'Nueva reserva',
        ),
        vencidas > 0
          ? h(
              'a',
              { class: 'aviso aviso--alerta', href: 'reservas.html?estado=activa' },
              icono('alerta'),
              `${vencidas} ${pluralizar(vencidas, 'devolución vencida', 'devoluciones vencidas')}`,
            )
          : null,
      ),
    ),

    resumen.marcas.length > 0
      ? h(
          'ul',
          { class: 'panel__marcas', 'aria-label': 'Marcas de la flota' },
          resumen.marcas.map((marca) => h('li', {}, marca)),
        )
      : null,

    h(
      'section',
      { class: 'panel__recientes', 'aria-labelledby': 'titulo-recientes' },
      h('h2', { id: 'titulo-recientes', class: 'panel__seccion' }, 'Reservas recientes'),
      resumen.recientes.length === 0
        ? vacioPanel(
            'calendario',
            'Aún no hay reservas.',
            'Crea tu primera reserva haciendo clic en "Nueva reserva".',
          )
        : h('ul', { class: 'lista-reservas' }, resumen.recientes.map(filaReserva)),
    ),

    h(
      'section',
      { class: 'panel__resumen', 'aria-labelledby': 'titulo-resumen' },
      h(
        'header',
        { class: 'panel__resumen-cabecera' },
        iconoRecuadro('panel'),
        h('h2', { id: 'titulo-resumen' }, 'Resumen de la flota'),
        h(
          'a',
          { class: 'icono-recuadro', href: 'vehiculos.html', 'aria-label': 'Ver vehículos' },
          icono('flecha', { grosor: 1.8 }),
        ),
      ),
      h(
        'p',
        { class: 'panel__fecha' },
        'Situación de la flota al ',
        h('span', {}, formatearFecha(resumen.fecha)),
      ),
      h('div', { class: 'panel__indicadores' }, indicadores.map(indicador)),
    ),

    h(
      'section',
      { class: 'panel__flota', 'aria-labelledby': 'titulo-flota' },
      h(
        'header',
        { class: 'panel__flota-cabecera' },
        h('h2', { id: 'titulo-flota' }, 'Vehículos'),
        enlaceBoton('Ver vehículos', 'vehiculos.html', 'boton--destacado boton--chico'),
      ),
      h(
        'div',
        { class: 'panel__escena' },
        h('img', {
          class: 'panel__auto',
          src: 'assets/img/hero-vehiculo.png',
          alt: '',
          width: 1320,
          height: 733,
          decoding: 'async',
        }),
      ),
      flota.total === 0
        ? vacioPanel(
            'vehiculo',
            'Aún no hay vehículos registrados.',
            'Agrega tu primer vehículo para comenzar a gestionar tu flota.',
          )
        : [
            h(
              'div',
              { class: 'panel__flota-datos' },
              dona(porcentajeDisponible, flota.disponible, flota.total),
              h(
                'div',
                { class: 'panel__flota-texto' },
                h('span', { class: 'panel__flota-etiqueta' }, 'Disponibilidad'),
                h(
                  'p',
                  { class: 'panel__flota-resumen' },
                  `${flota.disponible} de ${flota.total} vehículos disponibles`,
                ),
              ),
            ),
            h(
              'ul',
              { class: 'especificaciones', 'aria-label': 'Estado de la flota' },
              [
                ['vehiculo', flota.disponible, 'Disponibles'],
                ['llave', flota.alquilado, 'Alquilados'],
                ['taller', flota.mantenimiento, 'En mantenimiento'],
              ].map(([nombreIcono, cantidad, texto]) =>
                h(
                  'li',
                  {},
                  iconoRecuadro(nombreIcono),
                  h('strong', {}, cantidad),
                  h('span', {}, texto),
                ),
              ),
            ),
          ],
    ),
  );
}


function paginaPanel() {
  const vista = iniciarPagina({ seccion: 'panel', titulo: 'Panel general' });

  if (vista) {
    pintarPagina(vista, construirPanel);
  }
}

/* ===========================================================================
 * Vehiculos (vehiculos.html): lista con filtros, formulario para crear y editar, y eliminacion.
 * Los filtros viajan en la URL (vehiculos.html?estado=disponible&buscar=toyota).
 * ========================================================================= */


const OPCIONES_ESTADO_VEHICULO = [
  ['', 'Todos'],
  ['disponible', 'Disponible'],
  ['alquilado', 'Alquilado'],
  ['mantenimiento', 'Mantenimiento'],
];

function iniciarVehiculos(vista) {
  const consulta = leerConsulta();
  const buscar = consulta.get('buscar') ?? '';
  const estado = consulta.get('estado') ?? '';
  const lista = h('div', { id: 'lista' });
  let idEditando = null;

  /* ---- Formulario (panel que se abre y se cierra) ---- */

  const titulo = h('h2', {});
  const alerta = alertaGeneral();
  const bloqueEstado = campoSeleccion({
    nombre: 'estado',
    etiqueta: 'Estado',
    ancho: true,
    opciones: [
      { valor: 'disponible', texto: 'Disponible' },
      { valor: 'mantenimiento', texto: 'En mantenimiento' },
    ],
  });
  const avisoAlquilado = h(
    'div',
    { class: 'campo campo--ancho', hidden: true },
    h('span', { class: 'campo__etiqueta' }, 'Estado'),
    h(
      'p',
      { class: 'texto-suave' },
      'Alquilado. El estado se vuelve a calcular al registrar la devolución.',
    ),
  );
  const botonGuardar = h('button', { type: 'submit', class: 'boton boton--primario' });

  const formulario = h(
    'form',
    { class: 'formulario', hidden: true },
    titulo,
    alerta,
    h(
      'div',
      { class: 'rejilla-campos' },
      campo({ nombre: 'marca', etiqueta: 'Marca *', atributos: { required: true, maxlength: 100 } }),
      campo({ nombre: 'modelo', etiqueta: 'Modelo *', atributos: { required: true, maxlength: 100 } }),
      campo({
        nombre: 'anio',
        etiqueta: 'Año *',
        tipo: 'number',
        atributos: { required: true, min: 1901, max: new Date().getFullYear() + 1 },
      }),
      campo({
        nombre: 'categoria',
        etiqueta: 'Categoría',
        atributos: { maxlength: 50, placeholder: 'Sedán, SUV, Camioneta...' },
      }),
      bloqueEstado,
      avisoAlquilado,
    ),
    h(
      'div',
      { class: 'formulario__pie' },
      h('button', { type: 'button', class: 'boton boton--secundario', onclick: cerrarFormulario }, 'Cancelar'),
      botonGuardar,
    ),
  );

  /** Abre el formulario vacio (nuevo) o con los datos del vehiculo (editar). */
  function abrirFormulario(vehiculo = null) {
    const alquilado = vehiculo?.estado === 'alquilado';

    limpiarErrores(formulario, alerta);
    idEditando = vehiculo?.id ?? null;
    titulo.textContent = vehiculo ? 'Editar vehículo' : 'Nuevo vehículo';
    botonGuardar.textContent = vehiculo ? 'Guardar cambios' : 'Registrar';
    formulario.elements.marca.value = vehiculo?.marca ?? '';
    formulario.elements.modelo.value = vehiculo?.modelo ?? '';
    formulario.elements.anio.value = vehiculo?.anio ?? '';
    formulario.elements.categoria.value = vehiculo?.categoria ?? '';
    formulario.elements.estado.value = vehiculo?.estado === 'mantenimiento' ? 'mantenimiento' : 'disponible';
    // Un vehiculo alquilado no permite cambiar el estado desde el formulario.
    bloqueEstado.hidden = alquilado;
    avisoAlquilado.hidden = !alquilado;
    formulario.hidden = false;
    formulario.elements.marca.focus();
  }

  function cerrarFormulario() {
    formulario.hidden = true;
    limpiarErrores(formulario, alerta);
  }

  manejarEnvio(formulario, alerta, async (valores) => {
    const cuerpo = {
      marca: valores.marca,
      modelo: valores.modelo,
      anio: valores.anio === '' ? '' : Number(valores.anio),
      categoria: valores.categoria,
    };

    if (valores.estado !== undefined && !bloqueEstado.hidden) {
      cuerpo.estado = valores.estado;
    }

    const { mensaje } = idEditando
      ? await ApiClient.put(`/vehiculos/${idEditando}`, cuerpo)
      : await ApiClient.post('/vehiculos', cuerpo);

    cerrarFormulario();
    avisar(mensaje);
    await recargar();
  });

  /* ---- Lista ---- */

  function tarjeta(vehiculo) {
    return tarjetaVehiculo(vehiculo, [
      botonAccion('Editar', 'boton--secundario', () => abrirFormulario(vehiculo)),
      enlaceBoton(
        'Historial',
        `historial.html?tipo=vehiculo&id=${vehiculo.id}`,
        'boton--chico boton--secundario',
      ),
      botonAccion('Eliminar', 'boton--texto-peligro', () =>
        eliminarRecurso({
          ruta: `/vehiculos/${vehiculo.id}`,
          titulo: `¿Eliminar ${vehiculo.marca} ${vehiculo.modelo}?`,
          mensaje: 'Esta acción no se puede deshacer.',
          recargar,
        }),
      ),
    ]);
  }

  function recargar() {
    return pintarPagina(lista, async () => {
      const { data: vehiculos } = await ApiClient.get('/vehiculos', { buscar, estado });

      return vehiculos.length === 0
        ? estadoVacio('No hay vehículos que coincidan. Registre uno con "Nuevo vehículo".')
        : h('div', { class: 'rejilla-tarjetas' }, vehiculos.map(tarjeta));
    });
  }

  /* ---- Pagina ---- */

  const filtros = h(
    'form',
    { class: 'filtros', role: 'search', method: 'get' },
    h(
      'div',
      { class: 'campo filtros__campo' },
      h('label', { class: 'campo__etiqueta', for: 'vehiculos-buscar' }, 'Buscar'),
      h('input', {
        class: 'campo__control',
        id: 'vehiculos-buscar',
        name: 'buscar',
        type: 'search',
        placeholder: 'Marca, modelo o categoría',
        value: buscar,
      }),
    ),
    h(
      'div',
      { class: 'campo filtros__campo' },
      h('label', { class: 'campo__etiqueta', for: 'vehiculos-estado' }, 'Estado'),
      h(
        'select',
        { class: 'campo__control', id: 'vehiculos-estado', name: 'estado' },
        OPCIONES_ESTADO_VEHICULO.map(([valor, texto]) =>
          h('option', { value: valor, selected: valor === estado }, texto),
        ),
      ),
    ),
    h('button', { type: 'submit', class: 'boton boton--secundario' }, 'Filtrar'),
  );

  vista.replaceChildren(
    h(
      'section',
      { class: 'pagina' },
      cabeceraPagina(
        'Vehículos',
        'Flota registrada para alquiler.',
        h(
          'button',
          { type: 'button', class: 'boton boton--primario', onclick: () => abrirFormulario() },
          'Nuevo vehículo',
        ),
      ),
      formulario,
      filtros,
      lista,
    ),
  );

  return recargar();
}


function paginaVehiculos() {
  const vista = iniciarPagina({ seccion: 'vehiculos', titulo: 'Vehículos' });

  if (vista) {
    iniciarVehiculos(vista);
  }
}

/* ===========================================================================
 * Clientes (clientes.html): lista con busqueda, formulario para crear y editar, y eliminacion.
 * La busqueda viaja en la URL (clientes.html?buscar=maria).
 * ========================================================================= */


function iniciarClientes(vista) {
  const buscar = leerConsulta().get('buscar') ?? '';
  const lista = h('div', { id: 'lista' });
  let idEditando = null;

  /* ---- Formulario (panel que se abre y se cierra) ---- */

  const titulo = h('h2', {});
  const alerta = alertaGeneral();
  const botonGuardar = h('button', { type: 'submit', class: 'boton boton--primario' });

  const formulario = h(
    'form',
    { class: 'formulario', hidden: true },
    titulo,
    alerta,
    h(
      'div',
      { class: 'rejilla-campos' },
      campo({
        nombre: 'nombre',
        etiqueta: 'Nombre completo *',
        ancho: true,
        atributos: { required: true, maxlength: 150 },
      }),
      campo({ nombre: 'telefono', etiqueta: 'Teléfono', tipo: 'tel', atributos: { maxlength: 50 } }),
      campo({ nombre: 'correo', etiqueta: 'Correo', tipo: 'email', atributos: { maxlength: 100 } }),
      campo({
        nombre: 'numero_licencia',
        etiqueta: 'Número de licencia',
        ancho: true,
        ayuda: 'Es obligatoria para poder hacer reservas.',
        atributos: { maxlength: 50 },
      }),
    ),
    h(
      'div',
      { class: 'formulario__pie' },
      h('button', { type: 'button', class: 'boton boton--secundario', onclick: cerrarFormulario }, 'Cancelar'),
      botonGuardar,
    ),
  );

  /** Abre el formulario vacio (nuevo) o con los datos del cliente (editar). */
  function abrirFormulario(cliente = null) {
    limpiarErrores(formulario, alerta);
    idEditando = cliente?.id ?? null;
    titulo.textContent = cliente ? 'Editar cliente' : 'Nuevo cliente';
    botonGuardar.textContent = cliente ? 'Guardar cambios' : 'Registrar';
    formulario.elements.nombre.value = cliente?.nombre ?? '';
    formulario.elements.telefono.value = cliente?.telefono ?? '';
    formulario.elements.correo.value = cliente?.correo ?? '';
    formulario.elements.numero_licencia.value = cliente?.numero_licencia ?? '';
    formulario.hidden = false;
    formulario.elements.nombre.focus();
  }

  function cerrarFormulario() {
    formulario.hidden = true;
    limpiarErrores(formulario, alerta);
  }

  manejarEnvio(formulario, alerta, async (valores) => {
    const cuerpo = {
      nombre: valores.nombre,
      telefono: valores.telefono,
      correo: valores.correo,
      numero_licencia: valores.numero_licencia,
    };
    const { mensaje } = idEditando
      ? await ApiClient.put(`/clientes/${idEditando}`, cuerpo)
      : await ApiClient.post('/clientes', cuerpo);

    cerrarFormulario();
    avisar(mensaje);
    await recargar();
  });

  /* ---- Lista ---- */

  function tarjeta(cliente) {
    return tarjetaCliente(cliente, [
      botonAccion('Editar', 'boton--secundario', () => abrirFormulario(cliente)),
      enlaceBoton(
        'Historial',
        `historial.html?tipo=cliente&id=${cliente.id}`,
        'boton--chico boton--secundario',
      ),
      botonAccion('Eliminar', 'boton--texto-peligro', () =>
        eliminarRecurso({
          ruta: `/clientes/${cliente.id}`,
          titulo: `¿Eliminar a ${cliente.nombre}?`,
          mensaje: 'Esta acción no se puede deshacer.',
          recargar,
        }),
      ),
    ]);
  }

  function recargar() {
    return pintarPagina(lista, async () => {
      const { data: clientes } = await ApiClient.get('/clientes', { buscar });

      return clientes.length === 0
        ? estadoVacio('No hay clientes que coincidan. Registre uno con "Nuevo cliente".')
        : h('div', { class: 'rejilla-tarjetas' }, clientes.map(tarjeta));
    });
  }

  /* ---- Pagina ---- */

  const filtros = h(
    'form',
    { class: 'filtros', role: 'search', method: 'get' },
    h(
      'div',
      { class: 'campo filtros__campo' },
      h('label', { class: 'campo__etiqueta', for: 'clientes-buscar' }, 'Buscar'),
      h('input', {
        class: 'campo__control',
        id: 'clientes-buscar',
        name: 'buscar',
        type: 'search',
        placeholder: 'Nombre, correo, teléfono o licencia',
        value: buscar,
      }),
    ),
    h('button', { type: 'submit', class: 'boton boton--secundario' }, 'Buscar'),
  );

  vista.replaceChildren(
    h(
      'section',
      { class: 'pagina' },
      cabeceraPagina(
        'Clientes',
        'Personas registradas para alquilar vehículos.',
        h(
          'button',
          { type: 'button', class: 'boton boton--primario', onclick: () => abrirFormulario() },
          'Nuevo cliente',
        ),
      ),
      formulario,
      filtros,
      lista,
    ),
  );

  return recargar();
}


function paginaClientes() {
  const vista = iniciarPagina({ seccion: 'clientes', titulo: 'Clientes' });

  if (vista) {
    iniciarClientes(vista);
  }
}

/* ===========================================================================
 * Reservas (reservas.html): lista con filtro por estado, formulario de nueva reserva en dos pasos
 * (periodo y luego cliente y vehiculo disponible), devolucion y cancelacion.
 *
 * Direcciones utiles:
 *   reservas.html?estado=activa
 *   reservas.html?nueva=1&fecha_inicio=2026-12-01&fecha_fin=2026-12-05&vehiculo_id=3
 * ========================================================================= */


const OPCIONES_ESTADO_RESERVA = [
  ['activa', 'Activas'],
  ['completada', 'Completadas'],
  ['cancelada', 'Canceladas'],
  ['todas', 'Todas'],
];

/** Acciones de devolucion y cancelacion de una reserva, con su dialogo de confirmacion. */
function accionesReserva(recargar) {
  return {
    async alDevolver(reserva) {
      const { aceptado, mantenimiento } = await confirmar({
        titulo: 'Registrar devolución',
        mensaje: `${reserva.cliente?.nombre ?? 'El cliente'} devuelve el ${textoVehiculo(reserva.vehiculo)}.`,
        textoBoton: 'Registrar devolución',
        opcionMantenimiento: true,
      });

      if (aceptado) {
        await ejecutarAccion(
          () => ApiClient.post(`/reservas/${reserva.id}/devolucion`, { mantenimiento }),
          recargar,
        );
      }
    },

    async alCancelar(reserva) {
      const { aceptado } = await confirmar({
        titulo: `¿Cancelar la reserva #${reserva.id}?`,
        mensaje: `Reserva de ${reserva.cliente?.nombre ?? 'un cliente'}. El vehículo quedará libre.`,
        textoBoton: 'Cancelar reserva',
        peligrosa: true,
      });

      if (aceptado) {
        await ejecutarAccion(() => ApiClient.post(`/reservas/${reserva.id}/cancelacion`), recargar);
      }
    },
  };
}

function iniciarReservas(vista) {
  const consulta = leerConsulta();
  const estado = consulta.get('estado') ?? 'activa';
  const lista = h('div', { id: 'lista' });
  const panel = h('div', { id: 'panel-reserva', hidden: true });

  /* ---- Nueva reserva ---- */

  /** Paso 1 (periodo) y, si ya hay fechas, paso 2 (cliente y vehiculo). */
  async function construirPanel(parametros) {
    const fechaInicio = parametros.get('fecha_inicio') ?? hoyIso();
    const fechaFin = parametros.get('fecha_fin') ?? '';
    const vehiculoElegido = parametros.get('vehiculo_id') ?? '';
    const clienteElegido = parametros.get('cliente_id') ?? '';
    const hoy = hoyIso();

    const formularioPeriodo = h(
      'form',
      { class: 'formulario', method: 'get' },
      h('input', { type: 'hidden', name: 'nueva', value: 1 }),
      vehiculoElegido ? h('input', { type: 'hidden', name: 'vehiculo_id', value: vehiculoElegido }) : null,
      h('h2', {}, '1. Periodo'),
      h(
        'div',
        { class: 'rejilla-campos' },
        campo({
          nombre: 'fecha_inicio',
          etiqueta: 'Fecha de inicio *',
          tipo: 'date',
          valor: fechaInicio,
          atributos: { required: true, min: hoy },
        }),
        campo({
          nombre: 'fecha_fin',
          etiqueta: 'Fecha de fin *',
          tipo: 'date',
          valor: fechaFin,
          atributos: { required: true, min: hoy },
        }),
      ),
      h(
        'div',
        { class: 'formulario__pie' },
        h('button', { type: 'button', class: 'boton boton--texto', onclick: cerrarPanel }, 'Cancelar'),
        h('button', { type: 'submit', class: 'boton boton--secundario' }, 'Ver vehículos disponibles'),
      ),
    );

    const contenido = h(
      'div',
      { class: 'pagina pagina--angosta' },
      h('h2', {}, 'Nueva reserva'),
      h('p', { class: 'texto-suave' }, 'Elija las fechas para ver los vehículos disponibles.'),
      formularioPeriodo,
    );

    if (!parametros.get('fecha_fin')) {
      return contenido;
    }

    let disponibles;
    let clientes;

    try {
      [{ data: disponibles }, { data: clientes }] = await Promise.all([
        ApiClient.get('/vehiculos/disponibles', { fecha_inicio: fechaInicio, fecha_fin: fechaFin }),
        ApiClient.get('/clientes'),
      ]);
    } catch (error) {
      if (!(error instanceof ApiError) || error.codigoEstado !== 422 || !error.errores) {
        throw error;
      }

      mostrarErrores(formularioPeriodo, error.errores);

      return contenido;
    }

    const vehiculos = disponibles.vehiculos;
    const alerta = alertaGeneral();

    const formularioReserva = h(
      'form',
      { class: 'formulario' },
      h('h2', {}, '2. Cliente y vehículo'),
      h(
        'p',
        { class: 'texto-suave' },
        `Del ${formatearFecha(fechaInicio)} al ${formatearFecha(fechaFin)}: ${vehiculos.length} vehículo(s) disponible(s).`,
      ),
      alerta,
      h(
        'div',
        { class: 'rejilla-campos' },
        campoSeleccion({
          nombre: 'cliente_id',
          etiqueta: 'Cliente *',
          ancho: true,
          requerido: true,
          seleccionado: clienteElegido,
          opciones: [
            { valor: '', texto: 'Seleccione un cliente' },
            ...clientes.map((cliente) => {
              const sinLicencia = !String(cliente.numero_licencia ?? '').trim();

              return {
                valor: cliente.id,
                texto: `${cliente.nombre}${sinLicencia ? ' (sin licencia)' : ''}`,
                deshabilitada: sinLicencia,
              };
            }),
          ],
        }),
        campoSeleccion({
          nombre: 'vehiculo_id',
          etiqueta: 'Vehículo disponible *',
          ancho: true,
          requerido: true,
          seleccionado: vehiculoElegido,
          opciones: [
            {
              valor: '',
              texto:
                vehiculos.length === 0
                  ? 'No hay vehículos disponibles en esas fechas'
                  : 'Seleccione un vehículo',
            },
            ...vehiculos.map((vehiculo) => ({
              valor: vehiculo.id,
              texto: `${textoVehiculo(vehiculo)} · ${vehiculo.categoria || 'Sin categoría'}`,
            })),
          ],
        }),
      ),
      h(
        'div',
        { class: 'formulario__pie' },
        h('button', { type: 'button', class: 'boton boton--secundario', onclick: cerrarPanel }, 'Cancelar'),
        h('button', { type: 'submit', class: 'boton boton--primario' }, 'Reservar'),
      ),
    );

    manejarEnvio(formularioReserva, alerta, async (valores) => {
      const { mensaje } = await ApiClient.post('/reservas', {
        cliente_id: valores.cliente_id === '' ? '' : Number(valores.cliente_id),
        vehiculo_id: valores.vehiculo_id === '' ? '' : Number(valores.vehiculo_id),
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
      });

      cerrarPanel();
      avisar(mensaje);
      await recargar();
    });

    contenido.append(formularioReserva);

    return contenido;
  }

  async function abrirPanel(parametros = new URLSearchParams()) {
    await pintarPagina(panel, () => construirPanel(parametros));
    panel.hidden = false;
  }

  function cerrarPanel() {
    panel.hidden = true;
    panel.replaceChildren();
  }

  /* ---- Lista ---- */

  function recargar() {
    return pintarPagina(lista, async () => {
      const { data: reservas } = await ApiClient.get('/reservas', {
        estado: estado === 'todas' ? '' : estado,
      });

      return reservas.length === 0
        ? estadoVacio('No hay reservas con ese estado.')
        : tablaReservas(reservas, { acciones: accionesReserva(recargar) });
    });
  }

  /* ---- Pagina ---- */

  const filtros = h(
    'form',
    { class: 'filtros', method: 'get' },
    h(
      'div',
      { class: 'campo filtros__campo' },
      h('label', { class: 'campo__etiqueta', for: 'reservas-estado' }, 'Estado'),
      h(
        'select',
        { class: 'campo__control', id: 'reservas-estado', name: 'estado' },
        OPCIONES_ESTADO_RESERVA.map(([valor, texto]) =>
          h('option', { value: valor, selected: valor === estado }, texto),
        ),
      ),
    ),
    h('button', { type: 'submit', class: 'boton boton--secundario' }, 'Filtrar'),
  );

  vista.replaceChildren(
    h(
      'section',
      { class: 'pagina' },
      cabeceraPagina(
        'Reservas',
        'Alquileres registrados, devoluciones y cancelaciones.',
        h(
          'button',
          { type: 'button', class: 'boton boton--primario', onclick: () => abrirPanel() },
          'Nueva reserva',
        ),
      ),
      panel,
      filtros,
      lista,
    ),
  );

  if (consulta.get('nueva')) {
    abrirPanel(consulta);
  }

  return recargar();
}


function paginaReservas() {
  const vista = iniciarPagina({ seccion: 'reservas', titulo: 'Reservas' });

  if (vista) {
    iniciarReservas(vista);
  }
}

/* ===========================================================================
 * Disponibilidad (disponibilidad.html): vehiculos que se pueden reservar en un periodo.
 * El periodo viaja en la URL (disponibilidad.html?fecha_inicio=2026-12-01&fecha_fin=2026-12-05).
 * ========================================================================= */


async function construirDisponibilidad() {
  const consulta = leerConsulta();
  const fechaInicio = consulta.get('fecha_inicio') ?? hoyIso();
  const fechaFin = consulta.get('fecha_fin') ?? fechaInicio;

  const filtros = h(
    'form',
    { class: 'filtros', method: 'get' },
    h(
      'div',
      { class: 'campo filtros__campo' },
      h('label', { class: 'campo__etiqueta', for: 'campo-fecha_inicio' }, 'Desde'),
      h('input', {
        class: 'campo__control',
        id: 'campo-fecha_inicio',
        name: 'fecha_inicio',
        type: 'date',
        required: true,
        value: fechaInicio,
      }),
    ),
    h(
      'div',
      { class: 'campo filtros__campo' },
      h('label', { class: 'campo__etiqueta', for: 'campo-fecha_fin' }, 'Hasta'),
      h('input', {
        class: 'campo__control',
        id: 'campo-fecha_fin',
        name: 'fecha_fin',
        type: 'date',
        required: true,
        value: fechaFin,
      }),
    ),
    h('button', { type: 'submit', class: 'boton boton--primario' }, 'Consultar'),
  );

  const pagina = h(
    'section',
    { class: 'pagina' },
    cabeceraPagina('Disponibilidad', 'Vehículos que se pueden reservar en un periodo.'),
    filtros,
  );

  let resultado;

  try {
    ({ data: resultado } = await ApiClient.get('/vehiculos/disponibles', {
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
    }));
  } catch (error) {
    if (!(error instanceof ApiError) || error.codigoEstado !== 422 || !error.errores) {
      throw error;
    }

    mostrarErrores(filtros, error.errores);

    return pagina;
  }

  const { vehiculos } = resultado;
  const textoRango =
    resultado.fecha_inicio === resultado.fecha_fin
      ? `el ${formatearFecha(resultado.fecha_inicio)}`
      : `del ${formatearFecha(resultado.fecha_inicio)} al ${formatearFecha(resultado.fecha_fin)}`;

  pagina.append(
    h('p', { class: 'texto-suave' }, `${vehiculos.length} vehículo(s) disponible(s) ${textoRango}.`),
    vehiculos.length === 0
      ? estadoVacio('No hay vehículos disponibles en ese periodo.')
      : h(
          'div',
          { class: 'rejilla-tarjetas' },
          vehiculos.map((vehiculo) =>
            tarjetaVehiculo(
              vehiculo,
              [
                enlaceBoton(
                  'Reservar',
                  conConsulta('reservas.html', {
                    nueva: 1,
                    fecha_inicio: resultado.fecha_inicio,
                    fecha_fin: resultado.fecha_fin,
                    vehiculo_id: vehiculo.id,
                  }),
                  'boton--chico boton--primario',
                ),
              ],
              // El estado actual puede ser "alquilado" si se libera antes del periodo consultado.
              vehiculo.estado === 'alquilado'
                ? 'Hoy está alquilado; se libera antes de esa fecha.'
                : null,
            ),
          ),
        ),
  );

  return pagina;
}


function paginaDisponibilidad() {
  const vista = iniciarPagina({ seccion: 'disponibilidad', titulo: 'Disponibilidad' });

  if (vista) {
    pintarPagina(vista, construirDisponibilidad);
  }
}

/* ===========================================================================
 * Historial de alquileres de un vehiculo o de un cliente (historial.html).
 * Direcciones: historial.html?tipo=vehiculo&id=3 e historial.html?tipo=cliente&id=1
 * ========================================================================= */


async function construirHistorial(tipo, id) {
  const esVehiculo = tipo === 'vehiculo';
  const base = esVehiculo ? `/vehiculos/${id}` : `/clientes/${id}`;
  const [{ data: propietario }, { data: reservas }] = await Promise.all([
    ApiClient.get(base),
    ApiClient.get(`${base}/historial`),
  ]);

  const contar = (estado) => reservas.filter((reserva) => reserva.estado === estado).length;
  const diasAlquilado = reservas
    .filter((reserva) => reserva.estado !== 'cancelada')
    .reduce((total, reserva) => total + contarDias(reserva.fecha_inicio, reserva.fecha_fin), 0);

  const subtitulo = esVehiculo
    ? [propietario.categoria || 'Sin categoría', ' · ', insignia(propietario.estado)]
    : [
        `Licencia: ${propietario.numero_licencia || 'sin registrar'}`,
        propietario.correo || propietario.telefono
          ? ` · ${propietario.correo || propietario.telefono}`
          : '',
      ];

  return h(
    'section',
    { class: 'pagina' },
    h(
      'header',
      { class: 'pagina__cabecera' },
      h(
        'div',
        {},
        h(
          'p',
          {},
          h(
            'a',
            { href: esVehiculo ? 'vehiculos.html' : 'clientes.html' },
            `Volver a ${esVehiculo ? 'Vehículos' : 'Clientes'}`,
          ),
        ),
        h('h1', {}, `Historial: ${esVehiculo ? textoVehiculo(propietario) : propietario.nombre}`),
        h('p', { class: 'texto-suave' }, subtitulo),
      ),
    ),
    h(
      'div',
      { class: 'tarjetas-resumen' },
      tarjetaDato('Reservas', reservas.length),
      tarjetaDato('Activas', contar('activa'), 'alquilado'),
      tarjetaDato('Completadas', contar('completada'), 'disponible'),
      tarjetaDato('Canceladas', contar('cancelada')),
      tarjetaDato('Días de alquiler', diasAlquilado),
    ),
    reservas.length === 0
      ? estadoVacio('Todavía no hay alquileres registrados.')
      : tablaReservas(reservas, { conCliente: esVehiculo, conVehiculo: !esVehiculo }),
  );
}


function paginaHistorial() {
  const vista = iniciarPagina({
    seccion: leerConsulta().get('tipo') === 'cliente' ? 'clientes' : 'vehiculos',
    titulo: 'Historial',
  });

  if (vista) {
    const consulta = leerConsulta();
    const tipo = consulta.get('tipo');
    const id = Number(consulta.get('id'));

    if ((tipo === 'vehiculo' || tipo === 'cliente') && Number.isInteger(id) && id > 0) {
      pintarPagina(vista, () => construirHistorial(tipo, id));
    } else {
      vista.replaceChildren(paginaError(404, 'Falta el vehículo o el cliente del historial.'));
    }
  }
}

/* ===========================================================================
 * Arranque: cada HTML indica su pagina con <body data-pagina="..."> y aqui se ejecuta
 * la funcion que le corresponde.
 * ========================================================================= */

const PAGINAS = {
  login: paginaLogin,
  panel: paginaPanel,
  vehiculos: paginaVehiculos,
  clientes: paginaClientes,
  reservas: paginaReservas,
  disponibilidad: paginaDisponibilidad,
  historial: paginaHistorial,
};

PAGINAS[document.body.dataset.pagina]?.();
