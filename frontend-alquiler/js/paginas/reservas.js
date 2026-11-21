import { ApiClient, ApiError } from '../api.js';
import { avisar, cabeceraPagina, estadoVacio, icono, tablaReservas } from '../componentes.js';
import {
  alertaGeneral,
  campo,
  campoSeleccion,
  confirmar,
  ejecutarAccion,
  manejarEnvio,
  mostrarErrores,
} from '../formularios.js';
import { iniciarPagina, pintarPagina } from '../layout.js';
import {
  conConsulta,
  crearElemento,
  formatearFecha,
  hoyIso,
  leerConsulta,
  textoVehiculo,
} from '../utilidades.js';

const OPCIONES_ESTADO_RESERVA = [
  ['activa', 'Activas'],
  ['completada', 'Completadas'],
  ['cancelada', 'Canceladas'],
  ['todas', 'Todas'],
];

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
  const lista = crearElemento('div');
  const panel = crearElemento('div', { hidden: true });

  async function construirPanelNuevaReserva(parametros) {
    const fechaInicio = parametros.get('fecha_inicio') ?? hoyIso();
    const fechaFin = parametros.get('fecha_fin') ?? '';
    const vehiculoElegido = parametros.get('vehiculo_id') ?? '';
    const clienteElegido = parametros.get('cliente_id') ?? '';
    const hoy = hoyIso();

    const formularioPeriodo = crearElemento(
      'form',
      { class: 'formulario', method: 'get' },
      crearElemento('input', { type: 'hidden', name: 'nueva', value: 1 }),
      vehiculoElegido ? crearElemento('input', { type: 'hidden', name: 'vehiculo_id', value: vehiculoElegido }) : null,
      crearElemento('h2', {}, '1. Periodo'),
      crearElemento(
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
      crearElemento(
        'div',
        { class: 'formulario__pie' },
        crearElemento('button', { type: 'button', class: 'boton boton--texto', onclick: cerrarPanel }, 'Cancelar'),
        crearElemento('button', { type: 'submit', class: 'boton boton--secundario' }, 'Ver vehículos disponibles'),
      ),
    );

    const contenido = crearElemento(
      'div',
      { class: 'pagina pagina--angosta' },
      crearElemento('h2', {}, 'Nueva reserva'),
      crearElemento('p', { class: 'texto-suave' }, 'Elija las fechas para ver los vehículos disponibles.'),
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
    const hayClientesConLicencia = clientes.some(
      (cliente) => String(cliente.numero_licencia ?? '').trim() !== '',
    );
    const direccionVolverAReserva = conConsulta('reservas.html', {
      nueva: 1,
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      vehiculo_id: vehiculoElegido,
    });

    const formularioReserva = crearElemento(
      'form',
      { class: 'formulario' },
      crearElemento('h2', {}, '2. Cliente y vehículo'),
      crearElemento(
        'p',
        { class: 'texto-suave' },
        `Del ${formatearFecha(fechaInicio)} al ${formatearFecha(fechaFin)}: ${vehiculos.length} vehículo(s) disponible(s).`,
      ),
      alerta,
      crearElemento(
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
      hayClientesConLicencia
        ? null
        : crearElemento(
            'a',
            {
              class: 'aviso aviso--alerta',
              href: conConsulta('clientes.html', { nuevo: 1, volver: direccionVolverAReserva }),
            },
            icono('alerta'),
            'No hay clientes con licencia para reservar. Agregar cliente',
          ),
      crearElemento(
        'div',
        { class: 'formulario__pie' },
        crearElemento('button', { type: 'button', class: 'boton boton--secundario', onclick: cerrarPanel }, 'Cancelar'),
        crearElemento(
          'button',
          { type: 'submit', class: 'boton boton--primario', disabled: !hayClientesConLicencia },
          'Reservar',
        ),
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
    await pintarPagina(panel, () => construirPanelNuevaReserva(parametros));
    panel.hidden = false;
  }

  function cerrarPanel() {
    panel.hidden = true;
    panel.replaceChildren();
  }

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

  const filtros = crearElemento(
    'form',
    { class: 'filtros', method: 'get' },
    crearElemento(
      'div',
      { class: 'campo filtros__campo' },
      crearElemento('label', { class: 'campo__etiqueta', for: 'reservas-estado' }, 'Estado'),
      crearElemento(
        'select',
        { class: 'campo__control', id: 'reservas-estado', name: 'estado' },
        OPCIONES_ESTADO_RESERVA.map(([valor, texto]) =>
          crearElemento('option', { value: valor, selected: valor === estado }, texto),
        ),
      ),
    ),
    crearElemento('button', { type: 'submit', class: 'boton boton--secundario' }, 'Filtrar'),
  );

  vista.replaceChildren(
    crearElemento(
      'section',
      { class: 'pagina' },
      cabeceraPagina(
        'Reservas',
        'Alquileres registrados, devoluciones y cancelaciones.',
        crearElemento(
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

export function paginaReservas() {
  const vista = iniciarPagina({ seccion: 'reservas', titulo: 'Reservas' });

  if (vista) {
    iniciarReservas(vista);
  }
}
