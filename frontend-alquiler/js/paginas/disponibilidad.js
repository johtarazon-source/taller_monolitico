import { ApiClient, ApiError } from '../api.js';
import { cabeceraPagina, enlaceBoton, estadoVacio, tarjetaVehiculo } from '../componentes.js';
import { mostrarErrores } from '../formularios.js';
import { iniciarPagina, pintarPagina } from '../layout.js';
import { conConsulta, crearElemento, formatearFecha, hoyIso, leerConsulta } from '../utilidades.js';

async function construirDisponibilidad() {
  const consulta = leerConsulta();
  const fechaInicio = consulta.get('fecha_inicio') ?? hoyIso();
  const fechaFin = consulta.get('fecha_fin') ?? fechaInicio;

  const filtros = crearElemento(
    'form',
    { class: 'filtros', method: 'get' },
    crearElemento(
      'div',
      { class: 'campo filtros__campo' },
      crearElemento('label', { class: 'campo__etiqueta', for: 'campo-fecha_inicio' }, 'Desde'),
      crearElemento('input', {
        class: 'campo__control',
        id: 'campo-fecha_inicio',
        name: 'fecha_inicio',
        type: 'date',
        required: true,
        value: fechaInicio,
      }),
    ),
    crearElemento(
      'div',
      { class: 'campo filtros__campo' },
      crearElemento('label', { class: 'campo__etiqueta', for: 'campo-fecha_fin' }, 'Hasta'),
      crearElemento('input', {
        class: 'campo__control',
        id: 'campo-fecha_fin',
        name: 'fecha_fin',
        type: 'date',
        required: true,
        value: fechaFin,
      }),
    ),
    crearElemento('button', { type: 'submit', class: 'boton boton--primario' }, 'Consultar'),
  );

  const pagina = crearElemento(
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
    crearElemento('p', { class: 'texto-suave' }, `${vehiculos.length} vehículo(s) disponible(s) ${textoRango}.`),
    vehiculos.length === 0
      ? estadoVacio('No hay vehículos disponibles en ese periodo.')
      : crearElemento(
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

              vehiculo.estado === 'alquilado'
                ? 'Hoy está alquilado; se libera antes de esa fecha.'
                : null,
            ),
          ),
        ),
  );

  return pagina;
}

export function paginaDisponibilidad() {
  const vista = iniciarPagina({ seccion: 'disponibilidad', titulo: 'Disponibilidad' });

  if (vista) {
    pintarPagina(vista, construirDisponibilidad);
  }
}
