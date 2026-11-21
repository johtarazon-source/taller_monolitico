import { ApiClient, relanzarSiNoEsErrorDeApi } from './api.js';
import { avisar, icono } from './componentes.js';
import { crearElemento } from './utilidades.js';

export function campo({ nombre, etiqueta, tipo = 'text', valor = '', ayuda = null, ancho = false, atributos = {} }) {
  const id = `campo-${nombre}`;

  return crearElemento(
    'div',
    { class: ancho ? 'campo campo--ancho' : 'campo' },
    crearElemento('label', { class: 'campo__etiqueta', for: id }, etiqueta),
    crearElemento('input', {
      class: 'campo__control',
      id,
      name: nombre,
      type: tipo,
      value: valor ?? '',
      ...atributos,
    }),
    ayuda ? crearElemento('small', { class: 'texto-suave' }, ayuda) : null,
  );
}

export function campoSeleccion({ nombre, etiqueta, opciones, seleccionado = '', ancho = false, requerido = false }) {
  const id = `campo-${nombre}`;

  return crearElemento(
    'div',
    { class: ancho ? 'campo campo--ancho' : 'campo' },
    crearElemento('label', { class: 'campo__etiqueta', for: id }, etiqueta),
    crearElemento(
      'select',
      { class: 'campo__control', id, name: nombre, required: requerido },
      opciones.map((opcion) =>
        crearElemento(
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

export function alertaGeneral() {
  return crearElemento('p', { class: 'alerta alerta--error', role: 'alert', hidden: true });
}

function mostrarErrorGeneral(alerta, texto) {
  alerta.replaceChildren(icono('alerta'), crearElemento('span', {}, texto));
  alerta.hidden = false;
}

export function limpiarErrores(formulario, alerta) {
  formulario.querySelectorAll('.mensaje-error').forEach((nodo) => nodo.remove());
  formulario
    .querySelectorAll('[aria-invalid]')
    .forEach((nodo) => nodo.removeAttribute('aria-invalid'));
  alerta.hidden = true;
}

export function mostrarErrores(formulario, errores) {
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
      crearElemento('small', { class: 'mensaje-error' }, icono('alerta'), crearElemento('span', {}, mensaje)),
    );
    primerControl ??= control;
  }

  primerControl?.focus();

  return sinCampo;
}

export function manejarEnvio(formulario, alerta, accion) {
  formulario.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    limpiarErrores(formulario, alerta);

    const boton = formulario.querySelector('button[type="submit"]');
    boton.disabled = true;

    try {
      await accion(Object.fromEntries(new FormData(formulario)));
    } catch (error) {
      relanzarSiNoEsErrorDeApi(error);

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

export function confirmar({ titulo, mensaje, textoBoton, peligrosa = false, opcionMantenimiento = false }) {
  return new Promise((resolver) => {
    const casilla = opcionMantenimiento
      ? crearElemento('input', { type: 'checkbox', name: 'mantenimiento' })
      : null;

    const dialogo = crearElemento(
      'dialog',
      { class: 'dialogo', 'aria-labelledby': 'dialogo-titulo' },
      crearElemento(
        'form',
        { class: 'formulario', method: 'dialog' },
        crearElemento('h1', { id: 'dialogo-titulo' }, peligrosa ? icono('alerta') : null, titulo),
        crearElemento('p', {}, mensaje),
        casilla
          ? crearElemento('label', { class: 'casilla' }, casilla, 'Enviar el vehículo a mantenimiento')
          : null,
        crearElemento(
          'div',
          { class: 'formulario__pie' },
          crearElemento(
            'button',
            {
              type: 'button',
              class: 'boton boton--secundario',
              onclick: () => dialogo.close('cancelar'),
            },
            'Volver',
          ),
          crearElemento(
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

export async function ejecutarAccion(accion, recargar) {
  try {
    const { mensaje } = await accion();
    avisar(mensaje);
  } catch (error) {
    relanzarSiNoEsErrorDeApi(error);

    avisar(error.message, 'error');
  }

  await recargar();
}

export async function eliminarRecurso({ ruta, titulo, mensaje, recargar }) {
  const { aceptado } = await confirmar({ titulo, mensaje, textoBoton: 'Eliminar', peligrosa: true });

  if (aceptado) {
    await ejecutarAccion(() => ApiClient.delete(ruta), recargar);
  }
}
