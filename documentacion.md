# Documentación — Sistema de Taller de Servicios

Aplicación web para la gestión de un taller de servicio técnico: ingreso de equipos,
seguimiento de órdenes de reparación, gestión de clientes/empresas/equipos y un portal
de consulta para los clientes.

> Última actualización: 2026-06-24

---

## 1. Stack tecnológico

| Componente | Versión / Detalle |
|------------|-------------------|
| Framework | Laravel 12.62.0 |
| PHP | 8.2.12 |
| Base de datos | MySQL (XAMPP) |
| Autenticación | Laravel Breeze |
| Roles y permisos | Spatie laravel-permission v6.25 |
| Front-end | Blade + Alpine.js + Tailwind CSS |
| Build | Vite |
| Testing | PHPUnit (SQLite in-memory) |

---

## 2. Puesta en marcha

### Requisitos
- XAMPP con Apache + MySQL (o equivalente), PHP 8.2+
- Composer y Node.js / npm

### Instalación

```bash
git clone https://github.com/Defenestro81/servicios
cd servicios
composer install
npm install
cp .env.example .env
php artisan key:generate

# Configurar la conexión MySQL en .env (DB_DATABASE=servicios, etc.)
php artisan migrate --seed
```

### Desarrollo

```bash
# Terminal 1: compilación de assets en vivo (necesario para Tailwind/Alpine)
npm run dev

# Terminal 2: servidor
php artisan serve
```

Para producción: `npm run build` genera los assets en `public/build`.

### Credenciales del administrador (seed)
- **Email:** `admin@servicios.com`
- **Contraseña:** `sistema`
- Email ya verificado.

---

## 3. Roles y permisos

El sistema define tres roles (Spatie). El middleware se registra en `bootstrap/app.php`
(Laravel 12 no usa `Kernel.php`):

```php
$middleware->alias([
    'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
]);
```

### `usuario` (portal del cliente)
- Es el rol **por defecto** que recibe todo usuario que se registra.
- Solo **lectura**: ve el listado y el detalle de las órdenes asociadas a su correo.
- La vinculación es **automática por email**: ve las órdenes de los clientes cuyo
  `clientes.email` coincide con su `users.email` (no requiere intervención del admin).
- No puede crear/editar/eliminar nada. No ve información interna (arreglos con terceros,
  costos), ni enlaces a fichas de cliente/equipo.

### `tecnico`
- Ve **todo** el historial de órdenes.
- Puede **tomar** órdenes sin asignar y **editar** las propias o las no asignadas.
- **No puede** modificar órdenes tomadas por otro técnico.
- ABM de clientes, empresas y equipos. Puede crear tipos de equipo "al vuelo" (modal).
- Tiene una vista propia **"Mis órdenes"** (las asignadas a él).

### `administrador`
- Todo lo del técnico, más:
  - **Asignar / reasignar** técnicos a las órdenes.
  - **Gestión de usuarios** (cambiar roles).
  - **Gestión de tipos de equipo** (alta, edición, activar/desactivar).
  - **Búsqueda avanzada**.

---

## 4. Modelo de datos

### Entidades y relaciones

```
Empresa 1───* Cliente 1───* Telefono
                  │
                  *  (ordenes)
                  │
TipoEquipo 1───* Equipo 1───* Orden *───1 Estado
                                  │
              ┌───────────────────┼───────────────────────────┐
              │                   │                            │
        OrdenTecnico        OrdenEstadoHistorial          ArregloTercero *───1 Proveedor
        (pivot con          (historial de cambios          (reparaciones
         users + flag        de estado)                     tercerizadas)
         principal)
                                  │
                              Adjunto
                       (fotos/archivos de la orden)

User 1───1 Cliente (opcional, por user_id; el portal usa email matching)
```

### Tablas y nombres explícitos

Laravel pluraliza en inglés, por lo que varios modelos con nombres en español
**requieren `$table` explícito** (de lo contrario buscan tablas inexistentes como
`ordens`, `proveedors`, etc.):

| Modelo | Tabla real | SoftDeletes |
|--------|-----------|:-----------:|
| `User` | `users` | ✅ |
| `Cliente` | `clientes` | ✅ |
| `Telefono` | `telefonos` | — |
| `Empresa` | `empresas` | — |
| `TipoEquipo` | `tipos_equipos` | — |
| `Equipo` | `equipos` | ✅ |
| `Estado` | `estados` | — |
| `Orden` | `ordenes` | ✅ |
| `OrdenTecnico` | `orden_tecnico` | — |
| `OrdenEstadoHistorial` | `orden_estado_historial` | — |
| `ArregloTercero` | `arreglos_terceros` | — |
| `Proveedor` | `proveedores` | ✅ |
| `Adjunto` | `adjuntos` | — |

### Campos clave de `ordenes`
- `fecha_ingreso` — alta de la orden.
- `fecha_terminado` — se completa al pasar al estado **"Listo"**.
- `fecha_retirado` — fecha de **entrega** al cliente; se completa al pasar a **"Entregado"**.
- `costo_mano_obra` / `costo_repuestos` — importes (decimal, default 0). El **total** es la
  suma de ambos, expuesto por el accessor `Orden::costo_total`.
- `created_by` / `updated_by` — auditoría (FK a `users`, **NOT NULL**, `restrictOnDelete`).
- `accesorios`, `trabajo_solicitado`, `trabajo_realizado`, `detalles`.

> **Pendiente vs. Finalizada:** una orden está *finalizada* cuando tiene `fecha_retirado`;
> mientras esté en `null` es *pendiente*.

---

## 5. Decisiones de diseño

### Sin nulls en auditoría → SoftDeletes
Las columnas de auditoría (`created_by`, `updated_by`) son **NOT NULL** con
`restrictOnDelete()`. Para no perder esas referencias, los usuarios (y demás entidades
con historial) usan **SoftDeletes**: nunca se borran físicamente, se marcan `deleted_at`.
Por eso al "eliminar" una cuenta desde el perfil, el registro persiste marcado como
eliminado.

### Portal por email, sin vínculo manual
El rol `usuario` accede a sus órdenes comparando `users.email` con `clientes.email`.
Existe una columna `clientes.user_id` (nullable) por compatibilidad, pero **el portal no
la usa**.

### Registro robusto
Al registrarse, se asigna el rol `usuario`. El controlador hace `Role::firstOrCreate`
antes de asignarlo, de modo que el alta **nunca falla** aunque el entorno no esté seedeado.

### Fechas automáticas por estado
Al cambiar el estado de una orden:
- → **"Listo"**: setea `fecha_terminado` (si está vacía).
- → **"Entregado"**: setea `fecha_retirado` (si está vacía), pasándola a *finalizadas*.

### Creación "inline" (modales)
Para agilizar el alta de órdenes, hay componentes Alpine+fetch que crean entidades sin
salir del formulario:
- `<x-empresa-select>` → modal "Nueva empresa" (`empresas.inline`).
- `<x-tipo-equipo-select>` → modal "Nuevo tipo" (`tipos-equipos.inline`).

### Precedencia de rutas
Las rutas estáticas (`ordenes/buscar`, `ordenes/mias`, `ordenes/create`) se registran
**antes** de `ordenes/{orden}` para que no sean capturadas por el parámetro dinámico.

---

## 6. Rutas

Definidas en `routes/web.php`. Todo requiere autenticación (`auth`).

### Órdenes
| Verbo | URI | Nombre | Acceso |
|-------|-----|--------|--------|
| GET | `/ordenes` | `ordenes.index` | todos |
| GET | `/ordenes/{orden}` | `ordenes.show` | todos (control de propiedad en controlador) |
| GET | `/ordenes/buscar` | `ordenes.buscar` | técnico, admin |
| GET | `/ordenes/mias` | `ordenes.mias` | técnico, admin |
| GET/POST | `/ordenes/create`, `/ordenes` | `ordenes.create/store` | técnico, admin |
| GET/PUT | `/ordenes/{orden}/edit`, `/ordenes/{orden}` | `ordenes.edit/update` | técnico, admin (y autor del cambio según `puedeEditarTecnico`) |
| DELETE | `/ordenes/{orden}` | `ordenes.destroy` | técnico, admin |
| POST | `/ordenes/{orden}/estado` | `ordenes.cambiarEstado` | técnico, admin |
| POST | `/ordenes/{orden}/tomar` | `ordenes.tomar` | técnico, admin |
| POST | `/ordenes/{orden}/tecnico` | `ordenes.asignarTecnico` | admin |
| POST/DELETE | `/ordenes/{orden}/adjuntos[...]` | `adjuntos.*` | técnico, admin |
| POST/PATCH/DELETE | `/ordenes/{orden}/arreglos[...]` | `arreglos.*` | técnico, admin |

### Clientes / Empresas / Equipos (técnico, admin)
- `clientes.*` (resource completo)
- `empresas.*` (resource excepto `show`, `destroy`) + `empresas.inline`
- `equipos.*` (resource excepto `destroy`)
- `tipos-equipos.inline` (alta rápida)

### Admin (`/admin`, solo administrador)
- `admin.users.index`, `admin.users.role.update`
- `admin.tipos-equipos.index/store/update/toggle`

---

## 7. Controladores

| Controlador | Responsabilidad |
|-------------|-----------------|
| `OrdenController` | Listado agrupado, búsqueda, búsqueda avanzada, "mis órdenes", CRUD, cambio de estado, tomar/asignar. Helper privado `queryBase()` centraliza el alcance por rol y todos los filtros. |
| `ClienteController` | CRUD de clientes (con teléfonos múltiples y empresa). |
| `EmpresaController` | CRUD de empresas + `storeInline` (JSON para el modal). |
| `EquipoController` | CRUD de equipos (sin `destroy`); etiqueta única requerida. |
| `AdjuntoController` | Subida/eliminación de archivos de la orden. |
| `ArregloTerceroController` | Reparaciones tercerizadas asociadas a una orden. |
| `Admin\UserRoleController` | Listado de usuarios y cambio de rol (no permite auto-cambiarse el rol). |
| `Admin\TipoEquipoController` | Gestión de tipos + `storeInline`, `toggleActivo`. |
| `Auth\RegisteredUserController` | Registro + asignación del rol `usuario`. |

### `OrdenController::queryBase()`
Construye la consulta base aplicando:
- Alcance por rol (el `usuario` solo ve por su email).
- Búsqueda rápida `q` (apellido, nombre, **empresa** del cliente, **etiqueta** del equipo).
- Filtros avanzados: `apellido`, `nombre`, `empresa`, `etiqueta`, `nro_serie`,
  `estado_id`, `tecnico_id`, `fecha_desde`, `fecha_hasta`.

Luego se divide en **pendientes** (`fecha_retirado` nula) y **finalizadas**.

---

## 8. Vistas y componentes

### Componentes Blade reutilizables (`resources/views/components`)
- `estado-badge` — badge de color por estado.
- `empresa-select` — selector de empresa + modal de alta (Alpine + fetch + `x-teleport`).
- `tipo-equipo-select` — selector de tipo + modal de alta.
- `nav-dropdown` — menú desplegable del navbar.

### Vistas de órdenes (`resources/views/ordenes`)
- `index` — listado agrupado **Pendientes / Finalizadas** con buscador.
- `_tabla` — partial de tabla reutilizado por `index`, `buscar` y `mias`.
- `buscar` — formulario de búsqueda avanzada + resultados agrupados.
- `mias` — órdenes asignadas al técnico autenticado.
- `create` / `edit` / `show` — alta (con cliente/equipo inline), edición y detalle.

### Navegación (`layouts/navigation.blade.php`)
- **Usuario:** solo "Órdenes".
- **Técnico:** Órdenes · Mis órdenes · *Clientes y Empresas* ▾ · *Equipos* ▾ · *Búsqueda* ▾.
- **Admin:** Órdenes · *Clientes y Empresas* ▾ · *Equipos* ▾ (incluye Tipos) · *Búsqueda* ▾
  (incluye Mis órdenes asignadas). "Gestión de Usuarios" en el menú de perfil.

---

## 9. Flujos principales

### Alta de una orden (técnico/admin)
1. Se elige cliente existente **o** se crea uno nuevo en el mismo formulario
   (con teléfonos y empresa; la empresa puede crearse por modal).
2. Se elige equipo existente **o** se registra uno nuevo (tipo, marca, modelo, serie,
   **etiqueta requerida y única**; el tipo puede crearse por modal).
3. Se cargan datos de la orden y, opcionalmente, fotos de ingreso.
4. La orden nace en estado **"Ingresado"** y se registra la primera entrada del historial.

### Ciclo de estados
`Ingresado → En diagnóstico → En reparación → Esperando repuesto → Listo → Entregado`
(+ `Sin reparación`). Cada cambio queda en `orden_estado_historial` con nota, autor y
fecha. "Listo" y "Entregado" completan sus fechas automáticamente.

### Costos de la orden
Desde la edición (técnico/admin) se cargan **mano de obra** y **repuestos**; el formulario
muestra el **total** calculado en vivo. En el detalle, técnico/admin ven el desglose y el
total; el cliente (portal) ve solo el **total a abonar** (si es mayor a cero).

### Antecedentes en el detalle (técnico/admin)
Debajo de los datos de la orden, el detalle muestra dos secciones:
- **Otras órdenes de este equipo** — historial del mismo equipo, **incluyendo** las que
  ingresaron otros clientes (marcadas con una etiqueta "otro cliente").
- **Otras órdenes de este cliente** — el resto de las órdenes del cliente, con sus equipos.

Estas secciones no se muestran al rol `usuario`.

### Tomar / asignar
- Un técnico **toma** una orden libre (queda como principal).
- Si ya está asignada, el intento redirige con mensaje (no rompe).
- El **admin** puede asignar o reasignar el técnico desde el detalle.

### Portal del cliente (usuario)
- Ve "Mis órdenes de servicio" filtradas por su email, agrupadas y en solo lectura.
- Si no hay coincidencias, se muestra un aviso indicando que el taller registre su correo.

---

## 10. Datos iniciales (seeders)

`DatabaseSeeder` ejecuta en orden:

1. **`RoleSeeder`** — roles `usuario`, `tecnico`, `administrador` + usuario admin.
2. **`EstadoSeeder`** — 7 estados (Ingresado, En diagnóstico, En reparación,
   Esperando repuesto, Listo, Entregado, Sin reparación).
3. **`TipoEquipoSeeder`** — 12 tipos: Notebook, Computadora, All-in-One, Tablet,
   Impresora, Monitor, Teléfono celular, Router / Switch, Consola de videojuegos,
   Disco externo, Pendrive, UPS / Fuente de alimentación.

### Datos de demostración (opcional)

`DemoSeeder` genera un conjunto de datos para probar el sistema:

```bash
php artisan db:seed --class=DemoSeeder
```

Crea **3 empresas, 30 clientes** (10 con empresa, 20 sin), **40 equipos** y **50 órdenes**
con estados variados (pendientes y finalizadas), técnicos asignados, historial y algunos
arreglos con terceros. Varios equipos se reutilizan en órdenes de **distinto cliente**.
Es idempotente: omite recrear órdenes si los clientes demo ya las tienen.

Usuarios de demostración creados:

| Email | Contraseña | Rol |
|-------|-----------|-----|
| `admin@servicios.com` | `sistema` | administrador |
| `tecnico1@demo.test` / `tecnico2@demo.test` | `password` | técnico |
| `cliente1@demo.test` | `password` | usuario (portal, con órdenes para ver) |

Los clientes demo usan emails `clienteN@demo.test`, lo que permite probar el portal
iniciando sesión con `cliente1@demo.test` (su email coincide con el del cliente #1).

---

## 11. Testing

```bash
php artisan test
```

- Configuración en `phpunit.xml`: SQLite **in-memory** (`RefreshDatabase`).
- `tests/Feature/FlujoServiciosTest.php` cubre los flujos y permisos de los tres roles:
  acceso por rol, alta de orden completa (incluido el historial), portal por email,
  control de propiedad en el detalle, tomar/asignar, fecha automática de entrega,
  búsqueda, y ABM de empresas/tipos.
- Estado actual: **41 tests / 112 assertions** en verde (incluye los de Breeze).

> Nota: la migración `add_user_id_to_clientes` evita agregar la FK cuando el driver es
> SQLite (no soportado vía `ALTER TABLE`), manteniéndola en MySQL.

---

## 12. Convenciones y notas de mantenimiento

- **Modelos en español:** definir siempre `protected $table` cuando el plural inglés no
  coincida con la tabla real.
- **Borrado:** preferir SoftDeletes sobre `destroy` físico en entidades con auditoría
  o historial.
- **Rutas dinámicas:** registrar rutas estáticas antes de las que usan `{parametro}`.
- **Permisos en vistas:** usar `@role(...)`/`@elserole` y la propiedad `$puedeEditar`
  (de `Orden::puedeEditarTecnico()`) para mostrar acciones.
- **Creación inline:** seguir el patrón de `empresa-select` / `tipo-equipo-select`
  (Alpine `x-data` + `fetch` + `x-teleport="body"` para el modal).
- Tras cambios en vistas/rutas en producción: `php artisan view:cache` y
  `php artisan route:cache`.

---

## 13. Estructura resumida del proyecto

```
app/
  Http/Controllers/
    OrdenController.php          # núcleo: listado, búsqueda, CRUD, estados
    ClienteController.php
    EmpresaController.php
    EquipoController.php
    AdjuntoController.php
    ArregloTerceroController.php
    Auth/RegisteredUserController.php
    Admin/UserRoleController.php
    Admin/TipoEquipoController.php
  Models/                       # con $table y SoftDeletes según corresponda
database/
  migrations/
  seeders/                      # Role, Estado, TipoEquipo, Database
resources/views/
  ordenes/ (index,_tabla,buscar,mias,create,edit,show)
  clientes/ empresas/ equipos/
  admin/ (users, tipos-equipos)
  components/ (estado-badge, empresa-select, tipo-equipo-select, nav-dropdown, ...)
  layouts/navigation.blade.php
routes/web.php
tests/Feature/FlujoServiciosTest.php
```
