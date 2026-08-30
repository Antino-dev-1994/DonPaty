# Matriz de permisos

## Principios

- Los permisos se validan siempre en el servidor.
- Ocultar un botón no reemplaza una política de autorización.
- Los roles son conjuntos reutilizables de permisos, no condiciones codificadas directamente en las pantallas.
- Una persona puede tener varios roles y recibe la unión de sus permisos.
- Los permisos de habitante se limitan a información propia salvo autorización explícita.
- Las operaciones críticas separan permiso para ejecutar y permiso para autorizar.
- El propietario puede delegar funciones sin entregar acceso a configuración, respaldos o auditoría completa.

## Roles iniciales

### Propietario

Acceso completo al negocio y hogar. Puede autorizar excepciones, administrar permisos, cerrar periodos y gestionar respaldos.

### Administrador

Administra la operación cotidiana, catálogos, compras, ventas, inventario y finanzas. No puede asignar el rol de propietario, restaurar respaldos ni alterar auditoría.

### Producción

Consulta inventario y pedidos; planifica, inicia y completa producciones. No administra finanzas generales ni autoriza sus propias excepciones.

### Ventas y caja

Gestiona clientes, pedidos, ventas, abonos y caja menor. No cambia costos, recetas ni cuentas principales.

### Habitante

Consulta sus movimientos, presupuestos permitidos, solicitudes, cuotas y metas relacionadas. No accede al consolidado salvo permiso adicional.

### Consulta

Acceso de solo lectura a módulos y reportes expresamente asignados.

## Leyenda

- `Total`: crear, consultar, modificar borradores, confirmar y revertir según política.
- `Operar`: ejecutar el flujo habitual sin cambiar configuración.
- `Consultar`: solo lectura.
- `Propio`: acceso únicamente a registros vinculados con la persona.
- `Autorizar`: puede aprobar excepciones de terceros.
- `—`: sin acceso predeterminado.

## Matriz general

| Área | Propietario | Administrador | Producción | Ventas/Caja | Habitante | Consulta |
|---|---|---|---|---|---|---|
| Tablero del negocio | Total | Total | Consultar operativo | Consultar operativo | — | Consultar asignado |
| Tablero del hogar | Total | Consultar asignado | — | — | Propio | Consultar asignado |
| Personas | Total | Operar | Consultar asignado | Clientes | Propio | Consultar asignado |
| Usuarios | Total | Operar sin propietarios | — | — | Propio | — |
| Roles y permisos | Total | Consultar | — | — | — | — |
| Catálogo | Total | Total | Consultar | Consultar | — | Consultar |
| Inventario | Total | Total | Operar producción | Operar ventas/paquetes | — | Consultar |
| Ajustes de inventario | Total | Operar | Solicitar | Solicitar | — | — |
| Recetas y versiones | Total | Total | Operar borradores | Consultar | — | Consultar |
| Periodos de costos | Total | Operar | Consultar | — | — | Consultar |
| Producción | Total | Total | Operar | Consultar pedidos | — | Consultar |
| Clientes y pedidos | Total | Total | Consultar demanda | Operar | — | Consultar |
| Ventas y devoluciones | Total | Total | — | Operar | — | Consultar |
| Precios | Total | Total | Consultar costo permitido | Aplicar vigentes | — | Consultar |
| Compras y proveedores | Total | Total | Consultar recepción | — | — | Consultar |
| Caja menor | Total | Total | — | Operar | — | Consultar |
| Caja principal | Total | Operar asignado | — | Consultar saldo permitido | — | Consultar asignado |
| Ingresos y gastos del negocio | Total | Total | — | Registrar asignados | — | Consultar |
| Cartera y obligaciones | Total | Total | — | Operar cobros | — | Consultar |
| Hogar consolidado | Total | Asignado | — | — | Propio | Consultar asignado |
| Solicitudes de fondos | Total | Autorizar asignadas | — | Pagar asignadas | Propio | — |
| Presupuestos | Total | Operar asignado | — | — | Propio | Consultar asignado |
| Deudas y préstamos | Total | Operar asignado | — | — | Propio | Consultar asignado |
| Reportes | Total | Total | Operativos | Ventas/caja | Propio | Asignados |
| Auditoría | Total | Consultar limitada | — | — | Propia limitada | — |
| Respaldos y restauración | Total | Consultar estado | — | — | — | — |
| Configuración global | Total | Consultar/operar asignada | — | — | — | — |

## Autorizaciones críticas

| Operación | Quién puede solicitar | Quién puede autorizar por defecto |
|---|---|---|
| Continuar con inventario negativo | Producción, Ventas/Caja, Administrador | Propietario o Administrador diferente |
| Precio inferior al mínimo | Ventas/Caja, Administrador | Propietario o Administrador diferente |
| Recepción superior a compra | Administrador | Propietario |
| Diferencia relevante de caja | Ventas/Caja, Administrador | Propietario o Administrador diferente |
| Reabrir caja cerrada | Ventas/Caja, Administrador | Propietario |
| Tarifa mensual manual | Administrador | Propietario; el propietario puede registrarla directamente |
| Producción sin tarifa mensual definitiva | Producción, Administrador | Propietario, dejando pendiente de regularización |
| Revertir producción | Producción, Administrador | Propietario o Administrador diferente |
| Revertir venta o devolución | Ventas/Caja, Administrador | Propietario o Administrador diferente |
| Reabrir periodo mensual | Administrador | Propietario |
| Restaurar respaldo | Propietario | Nueva autenticación del mismo propietario |
| Cambiar roles de propietario | Propietario | Otro propietario si existe; si no, nueva autenticación |

## Separación solicitante-autorizador

- Un usuario no autoriza su propia excepción cuando exista otro autorizador disponible.
- Si el propietario ejecuta directamente una acción crítica, debe volver a autenticarse cuando la acción pueda alterar periodos cerrados, respaldos o permisos de propietario.
- Toda autorización puede incluir una fecha de expiración y debe utilizarse solo para la operación solicitada.

## Visibilidad de información sensible

- Los costos, márgenes y resultados se controlan con permisos separados de la operación de venta.
- Un vendedor puede conocer el precio mínimo sin ver necesariamente el costo completo.
- Un productor puede consultar disponibilidad y cantidades sin acceder a saldos bancarios.
- Un habitante no consulta movimientos de otro habitante salvo permiso explícito.
- Los adjuntos heredan los permisos del documento al que pertenecen.
- Los reportes respetan los mismos filtros de ámbito y persona que las pantallas operativas.

## Permisos atómicos previstos

Los nombres son conceptuales y permitirán construir roles adicionales:

```text
people.view
people.manage
users.manage
roles.manage

catalog.view
catalog.manage
inventory.view
inventory.adjust
inventory.authorize-negative
packages.convert

recipes.view
recipes.manage
recipes.activate-version
production.view
production.manage
production.complete
production.reverse

cost-periods.view
cost-periods.manage
cost-periods.close
cost-periods.reopen

orders.view
orders.manage
sales.create
sales.reverse
prices.manage
prices.override
prices.authorize-below-minimum

purchases.manage
purchases.receive
payables.manage
receivables.manage

cash.open
cash.operate
cash.close
cash.authorize-difference
finance.view
finance.manage

household.view-own
household.view-all
household.manage
fund-requests.create
fund-requests.approve
fund-requests.pay

reports.view-operational
reports.view-financial
audit.view
backups.manage
backups.restore
settings.manage
```

