/**
 * Muestra un mensaje de exito o error en el contenedor de alertas indicado.
 */
export function mostrarMensaje(contenedor, texto, tipo = 'error') {
  contenedor.textContent = texto;
  contenedor.className = `alerta alerta--${tipo}`;
  contenedor.hidden = false;
}

export function ocultarMensaje(contenedor) {
  contenedor.hidden = true;
  contenedor.textContent = '';
}
