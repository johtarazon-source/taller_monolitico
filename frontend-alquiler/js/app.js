import { paginaClientes } from './paginas/clientes.js';
import { paginaDisponibilidad } from './paginas/disponibilidad.js';
import { paginaHistorial } from './paginas/historial.js';
import { paginaLogin } from './paginas/login.js';
import { paginaPanel } from './paginas/panel.js';
import { paginaReservas } from './paginas/reservas.js';
import { paginaVehiculos } from './paginas/vehiculos.js';

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
