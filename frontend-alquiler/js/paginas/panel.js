import { ApiClient } from '../api.js';
import { enlaceBoton, icono, insignia } from '../componentes.js';
import { iniciarPagina, pintarPagina } from '../layout.js';
import {
  contarDias,
  crearElemento,
  crearElementoSvg,
  formatearFecha,
  formatearFechaCorta,
  pluralizar,
} from '../utilidades.js';

function filaReserva(reserva) {
  const vehiculo = reserva.vehiculo;
  const dias = contarDias(reserva.fecha_inicio, reserva.fecha_fin);
  const rango = `${formatearFechaCorta(reserva.fecha_inicio)} → ${formatearFechaCorta(reserva.fecha_fin)}`;
  const contenido = [
    vehiculo
      ? crearElemento('span', { class: 'fila-reserva__enlace', 'aria-hidden': 'true' }, icono('flecha'))
      : null,
    crearElemento(
      'div',
      { class: 'fila-reserva__datos' },
      crearElemento(
        'strong',
        { class: 'fila-reserva__titulo' },
        vehiculo ? `${vehiculo.marca} ${vehiculo.modelo}` : 'Vehículo eliminado',
      ),
      crearElemento('span', { class: 'fila-reserva__detalle' }, reserva.cliente?.nombre ?? 'Cliente eliminado'),
      crearElemento(
        'span',
        { class: 'fila-reserva__detalle' },
        `${rango} · ${dias} ${pluralizar(dias, 'día', 'días')}`,
      ),
      crearElemento('span', { class: 'fila-reserva__estado' }, insignia(reserva.estado_visible)),
    ),
  ];

  return crearElemento(
    'li',
    { class: 'fila-reserva' },
    vehiculo
      ? crearElemento(
          'a',
          {
            class: 'fila-reserva__contenido',
            href: `historial.html?tipo=vehiculo&id=${vehiculo.id}`,
          },
          contenido,
        )
      : crearElemento('div', { class: 'fila-reserva__contenido' }, contenido),
  );
}

function indicador({ variante, etiqueta, valor, detalle, porcentaje, destino, textoAccesible }) {
  const relleno = Math.max(0, Math.min(100, porcentaje));

  return crearElemento(
    'a',
    { class: `indicador indicador--${variante}`, href: destino },
    crearElemento('span', { class: 'indicador__etiqueta' }, etiqueta),
    crearElemento('strong', { class: 'indicador__valor' }, valor),
    crearElemento('span', { class: 'indicador__detalle' }, detalle),
    crearElemento(
      'div',
      { class: 'indicador__medidor' },
      crearElemento(
        'span',
        { class: 'indicador__barra', 'aria-hidden': 'true' },
        crearElemento('span', { class: 'indicador__relleno', style: `--porcentaje-relleno: ${relleno}` }),
      ),
      crearElemento('span', { class: 'indicador__porcentaje' }, `${relleno} %`),
    ),
    crearElemento('span', { class: 'solo-lectores-pantalla' }, textoAccesible),
  );
}

function dona(porcentaje, disponibles, total) {
  const radio = 52;
  const circunferencia = 2 * Math.PI * radio;
  const arco = Number(((circunferencia * Math.max(0, Math.min(100, porcentaje))) / 100).toFixed(2));

  return crearElemento(
    'div',
    {
      class: 'dona',
      role: 'img',
      'aria-label': `${porcentaje}% de disponibilidad: ${disponibles} de ${total} vehículos disponibles`,
    },
    crearElementoSvg(
      'svg',
      { class: 'dona__svg', viewBox: '0 0 120 120', 'aria-hidden': 'true', focusable: 'false' },
      crearElementoSvg('circle', { class: 'dona__pista', cx: 60, cy: 60, r: radio }),
      arco > 0
        ? crearElementoSvg('circle', {
            class: 'dona__arco',
            cx: 60,
            cy: 60,
            r: radio,
            'stroke-dasharray': `${arco} ${Number(circunferencia.toFixed(2))}`,
          })
        : null,
    ),
    crearElemento(
      'div',
      { class: 'dona__centro', 'aria-hidden': 'true' },
      crearElemento('strong', { class: 'dona__valor' }, `${porcentaje}%`),
    ),
  );
}

function vacioPanel(nombreIcono, titulo, descripcion) {
  return crearElemento(
    'div',
    { class: 'vacio' },
    icono(nombreIcono, { grosor: 1.3, clase: 'vacio__icono' }),
    crearElemento('strong', { class: 'vacio__titulo' }, titulo),
    crearElemento('p', { class: 'vacio__texto' }, descripcion),
  );
}

function iconoRecuadro(nombreIcono) {
  return crearElemento(
    'span',
    { class: 'icono-recuadro', 'aria-hidden': 'true' },
    icono(nombreIcono, { grosor: 1.8 }),
  );
}

async function construirPanelGeneral() {
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

  return crearElemento(
    'section',
    { class: 'pagina panel' },
    crearElemento(
      'header',
      { class: 'panel__cabecera' },
      crearElemento('h1', { class: 'panel__titulo' }, 'Panel de ', crearElemento('strong', {}, 'Gestor')),
      crearElemento(
        'div',
        { class: 'panel__acciones' },
        crearElemento(
          'a',
          { class: 'boton boton--destacado boton--grande', href: 'reservas.html?nueva=1' },
          crearElemento('span', { class: 'boton__icono', 'aria-hidden': 'true' }, icono('mas')),
          'Nueva reserva',
        ),
        vencidas > 0
          ? crearElemento(
              'a',
              { class: 'aviso aviso--alerta', href: 'reservas.html?estado=activa' },
              icono('alerta'),
              `${vencidas} ${pluralizar(vencidas, 'devolución vencida', 'devoluciones vencidas')}`,
            )
          : null,
      ),
    ),

    resumen.marcas.length > 0
      ? crearElemento(
          'ul',
          { class: 'panel__marcas', 'aria-label': 'Marcas de la flota' },
          resumen.marcas.map((marca) => crearElemento('li', {}, marca)),
        )
      : null,

    crearElemento(
      'section',
      { class: 'panel__recientes', 'aria-labelledby': 'titulo-recientes' },
      crearElemento('h2', { id: 'titulo-recientes', class: 'panel__seccion' }, 'Reservas recientes'),
      resumen.recientes.length === 0
        ? vacioPanel(
            'calendario',
            'Aún no hay reservas.',
            'Crea tu primera reserva haciendo clic en "Nueva reserva".',
          )
        : crearElemento('ul', { class: 'lista-reservas' }, resumen.recientes.map(filaReserva)),
    ),

    crearElemento(
      'section',
      { class: 'panel__resumen', 'aria-labelledby': 'titulo-resumen' },
      crearElemento(
        'header',
        { class: 'panel__resumen-cabecera' },
        iconoRecuadro('panel'),
        crearElemento('h2', { id: 'titulo-resumen' }, 'Resumen de la flota'),
        crearElemento(
          'a',
          { class: 'icono-recuadro', href: 'vehiculos.html', 'aria-label': 'Ver vehículos' },
          icono('flecha', { grosor: 1.8 }),
        ),
      ),
      crearElemento(
        'p',
        { class: 'panel__fecha' },
        'Situación de la flota al ',
        crearElemento('span', {}, formatearFecha(resumen.fecha)),
      ),
      crearElemento('div', { class: 'panel__indicadores' }, indicadores.map(indicador)),
    ),

    crearElemento(
      'section',
      { class: 'panel__flota', 'aria-labelledby': 'titulo-flota' },
      crearElemento(
        'header',
        { class: 'panel__flota-cabecera' },
        crearElemento('h2', { id: 'titulo-flota' }, 'Vehículos'),
        enlaceBoton('Ver vehículos', 'vehiculos.html', 'boton--destacado boton--chico'),
      ),
      crearElemento(
        'div',
        { class: 'panel__escena' },
        crearElemento('img', {
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
            crearElemento(
              'div',
              { class: 'panel__flota-datos' },
              dona(porcentajeDisponible, flota.disponible, flota.total),
              crearElemento(
                'div',
                { class: 'panel__flota-texto' },
                crearElemento('span', { class: 'panel__flota-etiqueta' }, 'Disponibilidad'),
                crearElemento(
                  'p',
                  { class: 'panel__flota-resumen' },
                  `${flota.disponible} de ${flota.total} vehículos disponibles`,
                ),
              ),
            ),
            crearElemento(
              'ul',
              { class: 'especificaciones', 'aria-label': 'Estado de la flota' },
              [
                ['vehiculo', flota.disponible, 'Disponibles'],
                ['llave', flota.alquilado, 'Alquilados'],
                ['taller', flota.mantenimiento, 'En mantenimiento'],
              ].map(([nombreIcono, cantidad, texto]) =>
                crearElemento(
                  'li',
                  {},
                  iconoRecuadro(nombreIcono),
                  crearElemento('strong', {}, cantidad),
                  crearElemento('span', {}, texto),
                ),
              ),
            ),
          ],
    ),
  );
}

export function paginaPanel() {
  const vista = iniciarPagina({ seccion: 'panel', titulo: 'Panel general' });

  if (vista) {
    pintarPagina(vista, construirPanelGeneral);
  }
}
