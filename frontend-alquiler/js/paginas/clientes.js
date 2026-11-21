import { ApiClient } from '../api.js';
import {
  avisar,
  botonAccion,
  cabeceraPagina,
  enlaceBoton,
  estadoVacio,
  tarjetaCliente,
} from '../componentes.js';
import {
  alertaGeneral,
  campo,
  eliminarRecurso,
  limpiarErrores,
  manejarEnvio,
} from '../formularios.js';
import { iniciarPagina, pintarPagina } from '../layout.js';
import { crearElemento, leerConsulta } from '../utilidades.js';

const DIRECCION_DE_RETORNO_VALIDA = /^reservas\.html(\?[\w%=&.-]*)?$/;

function iniciarClientes(vista) {
  const consulta = leerConsulta();
  const buscar = consulta.get('buscar') ?? '';
  const direccionVolver = DIRECCION_DE_RETORNO_VALIDA.test(consulta.get('volver') ?? '')
    ? consulta.get('volver')
    : null;
  const lista = crearElemento('div');
  let idEditando = null;

  const titulo = crearElemento('h2', {});
  const alerta = alertaGeneral();
  const botonGuardar = crearElemento('button', { type: 'submit', class: 'boton boton--primario' });
  const avisoVolver = direccionVolver
    ? crearElemento(
        'p',
        { class: 'texto-suave' },
        'Está registrando un cliente para una reserva. Al guardar volverá a ella. ',
        crearElemento('a', { href: direccionVolver }, 'Cancelar y volver'),
      )
    : null;

  const formulario = crearElemento(
    'form',
    { class: 'formulario', hidden: true },
    titulo,
    avisoVolver,
    alerta,
    crearElemento(
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
    crearElemento(
      'div',
      { class: 'formulario__pie' },
      crearElemento('button', { type: 'button', class: 'boton boton--secundario', onclick: cerrarFormulario }, 'Cancelar'),
      botonGuardar,
    ),
  );

  function abrirFormulario(cliente = null) {
    limpiarErrores(formulario, alerta);
    idEditando = cliente?.id ?? null;
    titulo.textContent = cliente ? 'Editar cliente' : 'Nuevo cliente';
    botonGuardar.textContent = cliente ? 'Guardar cambios' : 'Registrar';
    formulario.elements.nombre.value = cliente?.nombre ?? '';
    formulario.elements.telefono.value = cliente?.telefono ?? '';
    formulario.elements.correo.value = cliente?.correo ?? '';
    formulario.elements.numero_licencia.value = cliente?.numero_licencia ?? '';
    formulario.elements.numero_licencia.required = Boolean(direccionVolver) && !cliente;
    if (avisoVolver) {
      avisoVolver.hidden = Boolean(cliente);
    }
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
    const { data, mensaje } = idEditando
      ? await ApiClient.put(`/clientes/${idEditando}`, cuerpo)
      : await ApiClient.post('/clientes', cuerpo);

    if (direccionVolver && !idEditando) {
      const destino = new URL(direccionVolver, window.location.href);
      destino.searchParams.set('cliente_id', data.id);
      window.location.href = destino.href;

      return;
    }

    cerrarFormulario();
    avisar(mensaje);
    await recargar();
  });

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
        : crearElemento('div', { class: 'rejilla-tarjetas' }, clientes.map(tarjeta));
    });
  }

  const filtros = crearElemento(
    'form',
    { class: 'filtros', role: 'search', method: 'get' },
    crearElemento(
      'div',
      { class: 'campo filtros__campo' },
      crearElemento('label', { class: 'campo__etiqueta', for: 'clientes-buscar' }, 'Buscar'),
      crearElemento('input', {
        class: 'campo__control',
        id: 'clientes-buscar',
        name: 'buscar',
        type: 'search',
        placeholder: 'Nombre, correo, teléfono o licencia',
        value: buscar,
      }),
    ),
    crearElemento('button', { type: 'submit', class: 'boton boton--secundario' }, 'Buscar'),
  );

  vista.replaceChildren(
    crearElemento(
      'section',
      { class: 'pagina' },
      cabeceraPagina(
        'Clientes',
        'Personas registradas para alquilar vehículos.',
        crearElemento(
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

  if (consulta.get('nuevo')) {
    abrirFormulario();
  }

  return recargar();
}

export function paginaClientes() {
  const vista = iniciarPagina({ seccion: 'clientes', titulo: 'Clientes' });

  if (vista) {
    iniciarClientes(vista);
  }
}
