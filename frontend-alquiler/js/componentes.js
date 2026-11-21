import {
  contarDias,
  crearElemento,
  crearElementoSvg,
  formatearFecha,
  textoVehiculo,
} from './utilidades.js';

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

export function icono(nombre, { grosor = 2, clase = 'icono' } = {}) {
  return crearElementoSvg(
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
    TRAZOS_ICONOS[nombre].map((trazo) => crearElementoSvg('path', { d: trazo })),
  );
}

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

export function insignia(estado) {
  return crearElemento('span', { class: `insignia insignia--${estado}` }, ETIQUETAS_ESTADO[estado] ?? estado);
}

export function avisar(texto, tipo = 'exito') {
  document.getElementById('avisos').replaceChildren(
    crearElemento(
      'p',
      { class: `alerta alerta--${tipo}`, role: tipo === 'error' ? 'alert' : 'status' },
      icono(tipo === 'exito' ? 'exito' : 'error'),
      crearElemento('span', {}, texto),
    ),
  );
}

export function logo() {
  return crearElemento(
    'span',
    { class: 'logo' },
    crearElementoSvg(
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
      crearElementoSvg('path', { d: 'M31 10.5A14 14 0 1 0 31 29.5', 'stroke-width': 4.5 }),
      crearElementoSvg('path', { d: 'M25.5 16a6.5 6.5 0 1 0 0 8', 'stroke-width': 3.5 }),
    ),
    crearElemento('span', { class: 'logo__texto' }, 'CarRent'),
  );
}

export function cabeceraPagina(titulo, descripcion, accion = null) {
  return crearElemento(
    'header',
    { class: 'pagina__cabecera' },
    crearElemento(
      'div',
      {},
      crearElemento('h1', {}, titulo),
      descripcion ? crearElemento('p', { class: 'texto-suave' }, descripcion) : null,
    ),
    accion,
  );
}

export function enlaceBoton(texto, direccion, clase) {
  return crearElemento('a', { class: `boton ${clase}`, href: direccion }, texto);
}

export function botonAccion(texto, clase, alHacerClic) {
  return crearElemento(
    'button',
    { type: 'button', class: `boton boton--chico ${clase}`, onclick: alHacerClic },
    texto,
  );
}

export function estadoVacio(texto) {
  return crearElemento('p', { class: 'estado-vacio' }, texto);
}

export function paginaError(codigoEstado, mensaje) {
  return crearElemento(
    'section',
    { class: 'pagina' },
    cabeceraPagina(codigoEstado ? `Error ${codigoEstado}` : 'Sin conexión', mensaje),
    crearElemento('p', {}, enlaceBoton('Ir al inicio', 'home.html', 'boton--secundario')),
  );
}

export function tarjetaVehiculo(vehiculo, acciones, nota = null) {
  return crearElemento(
    'article',
    { class: 'tarjeta-vehiculo' },
    crearElemento(
      'header',
      { class: 'tarjeta-vehiculo__cabecera' },
      crearElemento('h3', {}, `${vehiculo.marca} ${vehiculo.modelo}`),
      insignia(vehiculo.estado),
    ),
    crearElemento('p', { class: 'texto-suave' }, `${vehiculo.anio} · ${vehiculo.categoria || 'Sin categoría'}`),
    nota ? crearElemento('small', { class: 'texto-suave' }, nota) : null,
    crearElemento('div', { class: 'tarjeta-vehiculo__acciones' }, acciones),
  );
}

export function tarjetaCliente(cliente, acciones) {
  return crearElemento(
    'article',
    { class: 'tarjeta-cliente' },
    crearElemento(
      'header',
      { class: 'tarjeta-cliente__cabecera' },
      crearElemento('h3', {}, cliente.nombre),
      cliente.numero_licencia
        ? null
        : crearElemento('span', { class: 'insignia insignia--cancelada' }, 'Sin licencia'),
    ),
    crearElemento(
      'p',
      { class: 'texto-suave' },
      `${cliente.telefono || 'Sin teléfono'} · ${cliente.correo || 'Sin correo'}`,
    ),
    cliente.numero_licencia
      ? crearElemento('small', { class: 'texto-suave' }, `Licencia: ${cliente.numero_licencia}`)
      : null,
    crearElemento('div', { class: 'tarjeta-cliente__acciones' }, acciones),
  );
}

export function tarjetaDato(etiqueta, valor, variante = null) {
  return crearElemento(
    'article',
    { class: variante ? `tarjeta-dato tarjeta-dato--${variante}` : 'tarjeta-dato' },
    crearElemento('span', { class: 'tarjeta-dato__etiqueta' }, etiqueta),
    crearElemento('strong', { class: 'tarjeta-dato__valor' }, valor),
  );
}

export function tablaReservas(reservas, { conCliente = true, conVehiculo = true, acciones = null } = {}) {
  const celda = (titulo, ...contenido) => crearElemento('td', { dataset: { titulo } }, contenido);

  const filas = reservas.map((reserva) => {
    const estadoVisible = reserva.estado_visible;
    const botones = [];

    if (acciones && reserva.estado === 'activa') {
      const puedeDevolverse = estadoVisible !== 'programada';

      if (puedeDevolverse) {
        botones.push(
          botonAccion('Registrar devolución', 'boton--primario', () => acciones.alDevolver(reserva)),
        );
      }

      botones.push(
        botonAccion('Cancelar', 'boton--texto-peligro', () => acciones.alCancelar(reserva)),
      );
    }

    return crearElemento(
      'tr',
      { class: estadoVisible === 'vencida' ? 'tabla__fila--alerta' : null },
      celda('#', reserva.id),
      conCliente ? celda('Cliente', reserva.cliente?.nombre ?? 'Cliente eliminado') : null,
      conVehiculo ? celda('Vehículo', textoVehiculo(reserva.vehiculo)) : null,
      celda('Desde', formatearFecha(reserva.fecha_inicio)),
      celda('Hasta', formatearFecha(reserva.fecha_fin)),
      celda('Días', contarDias(reserva.fecha_inicio, reserva.fecha_fin)),
      celda('Estado', insignia(estadoVisible)),
      acciones ? crearElemento('td', { class: 'tabla__acciones' }, botones) : null,
    );
  });

  return crearElemento(
    'div',
    { class: 'tabla-contenedor' },
    crearElemento(
      'table',
      { class: 'tabla' },
      crearElemento(
        'thead',
        {},
        crearElemento(
          'tr',
          {},
          crearElemento('th', {}, '#'),
          conCliente ? crearElemento('th', {}, 'Cliente') : null,
          conVehiculo ? crearElemento('th', {}, 'Vehículo') : null,
          crearElemento('th', {}, 'Desde'),
          crearElemento('th', {}, 'Hasta'),
          crearElemento('th', {}, 'Días'),
          crearElemento('th', {}, 'Estado'),
          acciones ? crearElemento('th', {}, crearElemento('span', { class: 'solo-lectores-pantalla' }, 'Acciones')) : null,
        ),
      ),
      crearElemento('tbody', {}, filas),
    ),
  );
}
