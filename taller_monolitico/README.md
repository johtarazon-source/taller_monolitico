# Gestor de Alquiler de Vehículos

Aplicación monolítica (MVC) para registrar vehículos y clientes, hacer reservas,
registrar devoluciones y consultar historial y disponibilidad.

Está hecha solo con **PHP, HTML y CSS** (sin JavaScript): las páginas se generan en el
servidor y todo funciona con formularios y enlaces normales.

## Estructura (`backend-alquiler/`)

| Carpeta | Contenido |
|---|---|
| `app/Models` | Modelos Eloquent: `Vehiculo`, `Cliente`, `Reserva` |
| `app/Services` | Reglas de negocio (validaciones, disponibilidad, estados) |
| `app/Controllers/Web` | Controladores de las páginas HTML |
| `app/Controllers/Api` | Controladores de la API JSON |
| `app/Views/web` | Plantillas PHP (`layout.php` y una por página; las que empiezan con `_` son fragmentos reutilizables) |
| `app/Middleware` | Sesión web, protección CSRF y token de la API |
| `app/Routes` | `web.php` (páginas) y `api.php` (API bajo `/api`) |
| `public/` | Punto de entrada `index.php`, `.htaccess` y `css/styles.css` |

Seguridad de la interfaz web: sesión PHP con cookie `HttpOnly` y `SameSite=Lax`,
token CSRF en todos los formularios `POST` y escape de todo el texto mostrado.

## Puesta en marcha con XAMPP

1. Encender **Apache** y **MySQL** en XAMPP.
2. Crear la base de datos importando `backend-alquiler/database/alquiler_vehiculos_db.sql`
   en phpMyAdmin.
3. Instalar dependencias del backend:

   ```bash
   cd backend-alquiler
   composer install
   ```

4. Copiar `backend-alquiler/.env.example` a `backend-alquiler/.env` y generar el hash
   de la contraseña del administrador:

   ```bash
   php -r "echo password_hash('12345', PASSWORD_DEFAULT);"
   ```

   Pegar el resultado entre comillas simples: `ADMIN_PASSWORD_HASH='$2y$10$...'`.

5. Abrir <http://localhost/taller_monolitico/>.

Credenciales de desarrollo: usuario `Admin`, contraseña `12345`
(cámbielas en el `.env`: `ADMIN_USER` y un nuevo hash en `ADMIN_PASSWORD_HASH`).

## Reglas de negocio

- **Estado del vehículo**: `mantenimiento` lo asigna el usuario; `alquilado` y
  `disponible` los calcula el sistema. Un vehículo está alquilado si tiene una reserva
  activa que ya comenzó. Una reserva futura lo pasa a alquilado sola el día de inicio.
- **Reservas**: la fecha de inicio no puede ser anterior a hoy y la de fin no puede ser
  anterior a la de inicio. El cliente debe tener número de licencia. El vehículo no puede
  estar en mantenimiento ni tener otra reserva activa que se cruce en fechas.
- **Reservas vencidas**: si una reserva activa pasó su fecha de fin sin devolución, el
  vehículo sigue fuera y no se puede volver a reservar hasta registrar la devolución.
- **Devolución**: la reserva pasa a `completada` y el vehículo vuelve a `disponible`
  (o a `mantenimiento` si se marca la opción). Solo aplica a reservas ya iniciadas.
- **Cancelación**: la reserva pasa a `cancelada` y el vehículo queda libre.
- **Eliminar** vehículos o clientes con reservas está bloqueado: la base de datos
  borraría su historial en cascada.

## API (opcional)

Además de las páginas, la misma lógica se expone como API JSON bajo
`http://localhost/taller_monolitico/taller_monolitico/backend-alquiler/public/api`.
Las rutas de la tabla van después de ese prefijo.
Todas, excepto el login, requieren `Authorization: Bearer <token>`.
Respuesta estándar: `{ "ok": bool, "mensaje": string, "data": mixed }`.
Códigos: `422` datos inválidos (con detalle por campo en `data`), `404` no existe,
`409` conflicto de negocio, `401` sin sesión.

| Método | Ruta | Descripción |
|---|---|---|
| POST | `/auth/login` | Recibe `{ usuario, password }` y devuelve `{ token }` |
| POST | `/auth/logout` | Cierra la sesión |
| GET | `/resumen` | Conteos de flota, reservas vencidas y próximas |
| GET | `/vehiculos?estado=&categoria=&buscar=` | Lista de vehículos |
| POST | `/vehiculos` | `{ marca, modelo, anio, categoria?, estado? }` |
| GET / PUT / DELETE | `/vehiculos/{id}` | Ver, editar o eliminar |
| GET | `/vehiculos/disponibles?fecha_inicio=&fecha_fin=` | Disponibles en el rango (por defecto hoy) |
| GET | `/vehiculos/{id}/historial` | Reservas del vehículo |
| GET | `/clientes?buscar=` | Lista de clientes |
| POST | `/clientes` | `{ nombre, telefono?, correo?, numero_licencia? }` |
| GET / PUT / DELETE | `/clientes/{id}` | Ver, editar o eliminar |
| GET | `/clientes/{id}/historial` | Reservas del cliente |
| GET | `/reservas?estado=&cliente_id=&vehiculo_id=` | Lista de reservas |
| POST | `/reservas` | `{ cliente_id, vehiculo_id, fecha_inicio, fecha_fin }` |
| GET | `/reservas/{id}` | Ver una reserva |
| POST | `/reservas/{id}/devolucion` | `{ mantenimiento?: bool }` |
| POST | `/reservas/{id}/cancelacion` | Cancela una reserva activa |
