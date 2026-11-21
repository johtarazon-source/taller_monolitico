import { ApiClient } from '../api.js';
import { estadoVacio, insignia, paginaError, tablaReservas, tarjetaDato } from '../componentes.js';
import { iniciarPagina, pintarPagina } from '../layout.js';
import { contarDias, crearElemento, leerConsulta, textoVehiculo } from '../utilidades.js';

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

  return crearElemento(
    'section',
    { class: 'pagina' },
    crearElemento(
      'header',
      { class: 'pagina__cabecera' },
      crearElemento(
        'div',
        {},
        crearElemento(
          'p',
          {},
          crearElemento(
            'a',
            { href: esVehiculo ? 'vehiculos.html' : 'clientes.html' },
            `Volver a ${esVehiculo ? 'Vehículos' : 'Clientes'}`,
          ),
        ),
        crearElemento('h1', {}, `Historial: ${esVehiculo ? textoVehiculo(propietario) : propietario.nombre}`),
        crearElemento('p', { class: 'texto-suave' }, subtitulo),
      ),
    ),
    crearElemento(
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

export function paginaHistorial() {
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
