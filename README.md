# Gestor de Alquiler de Vehículos

Sistema de gestión de alquiler de vehículos compuesto por dos proyectos independientes dentro de este repositorio:

- **[`backend-alquiler/`](backend-alquiler/)** — API y aplicación web en PHP (Slim Framework 4 + Eloquent), con arquitectura MVC. Es la aplicación funcional principal: expone tanto vistas web renderizadas en servidor como una API JSON.
- **[`frontend-alquiler/`](frontend-alquiler/)** — SPA en JavaScript vanilla (sin framework ni bundler), en construcción. Actualmente solo el flujo de login está implementado; los módulos de Vehículos, Clientes, Reservas y Disponibilidad están pendientes.

## Arquitectura del backend (MVC)

`backend-alquiler/app/` sigue una estructura MVC con autoload PSR-4 (`App\` → `app/`):

| Carpeta | Responsabilidad |
|---|---|
| `Controllers/Web` | Controladores de las vistas HTML renderizadas en servidor |
| `Controllers/Api` | Controladores de la API JSON |
| `Models` | Modelos Eloquent (`Vehiculo`, `Cliente`, `Reserva`) |
| `Views` | Vistas PHP (`Views/web/*.php`) y helpers de renderizado (`HtmlView`, `JsonView`, `Formato`) |
| `Services` | Lógica de negocio por entidad |
| `Routes` | Definición de rutas web (`web.php`) y API (`api.php`) |
| `Middleware` | Autenticación, sesión web y protección CSRF |
| `Exceptions` | Excepciones de dominio (no encontrado, conflicto, validación, etc.) |
| `Config` | Bootstrap de contenedor DI y conexión a base de datos |
| `Support` | Utilidades compartidas (sesión, validador) |

## Instalación y ejecución (XAMPP)

1. Clonar/ubicar el repositorio en `htdocs/` (ej. `c:\xampp\htdocs\taller_monolitico`).
2. Instalar dependencias del backend:
   ```bash
   cd backend-alquiler
   composer install
   ```
3. Copiar `backend-alquiler/.env.example` a `backend-alquiler/.env` y completar las credenciales de base de datos y demás variables.
4. Crear la base de datos importando `backend-alquiler/database/alquiler_vehiculos_db.sql` (ej. desde phpMyAdmin o `mysql -u root -p < database/alquiler_vehiculos_db.sql`).
5. Acceder a la aplicación en:
   ```
   http://localhost/taller_monolitico/backend-alquiler/public/
   ```

> Nota: la ruta de acceso cambió respecto a versiones anteriores del repositorio (antes tenía una carpeta duplicada `taller_monolitico/taller_monolitico/`). Si tenías un marcador o configuración de Apache apuntando a la ruta antigua, actualízalo a la ruta de arriba.

## Estado del frontend

`frontend-alquiler/` es un esqueleto temprano: el login funciona contra la API, pero los módulos de Vehículos, Clientes, Reservas y Disponibilidad (`js/views/`, `js/models/`) aún no están implementados.

## Pendientes conocidos

- No existe aún una suite de pruebas automatizadas (ni PHPUnit en el backend ni un test runner en el frontend).
- `frontend-alquiler` tiene configuración de lint/formato (`.eslintrc.json`, `.prettierrc`) pero no cuenta con `package.json` para gestionar esas herramientas.
