/**
 * Muestra un mensaje de exito o error en el contenedor de alertas indicado.
 */
export function mostrarMensaje(contenedor, textoMensaje, tipo = 'error') {
  contenedor.textContent = textoMensaje;
  contenedor.className = `alerta alerta--${tipo}`;
  contenedor.hidden = false;
}

export function ocultarMensaje(contenedor) {
  contenedor.hidden = true;
  contenedor.textContent = '';
}
