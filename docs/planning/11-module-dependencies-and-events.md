# Dependencias y eventos entre módulos

## Regla principal

Un módulo no consulta ni modifica directamente las tablas internas de otro módulo para ejecutar reglas de negocio. La colaboración se realiza mediante:

- Contratos públicos de aplicación.
- DTOs públicos.
- Eventos internos.
- Actions coordinadoras de flujo.
- Proyecciones de lectura explícitamente publicadas.

Las claves foráneas pueden existir para integridad física sin conceder acceso libre a la implementación interna.

## Dirección general de dependencias

```text
Shared
  ^
  |
Identity     People     Catalog
  ^            ^          ^
  |            |          |
  +------ módulos operativos ------+
                                     |
Inventory   Recipes   Finance   Attachments   Audit
     ^         ^         ^
     |         |         |
Purchasing   Production   CashManagement
     ^          ^   ^          ^
     |          |   |          |
     +-------- Orders -------- Sales
                         |
                    CostAccounting
                         |
                      Reporting
```

El diagrama expresa disponibilidad conceptual, no herencia entre módulos.

## Contratos públicos por módulo

### Identity

Publica:

- Verificación de permisos.
- Resolución del usuario y persona actual.
- Validación de autorizaciones excepcionales.
- Revocación de sesiones.

No conoce inventario, ventas ni finanzas.

### People

Publica:

- Identidad y clasificaciones de personas.
- Consulta de condición de habitante, empleado, cliente o proveedor.
- Datos mínimos de contacto autorizados.

No calcula cartera ni movimientos personales.

### Catalog

Publica:

- Artículos y presentaciones activas.
- Unidades y conversiones.
- Composición de paquetes.
- Precios vigentes y mínimos.

No conoce existencias ni movimientos.

### Inventory

Publica:

- Consulta de existencia física, reservada y disponible.
- Registro atómico de entradas y salidas.
- Reserva y liberación.
- Conversión de paquetes.
- Valorización y costo promedio.

No confirma ventas, compras o producciones por sí mismo; recibe una solicitud con documento origen.

### Recipes

Publica:

- Versión vigente de una receta.
- Escalado por cantidad de harina.
- Productos compatibles y peso de masa.
- Estimación de rendimiento.

No descuenta inventario ni crea producciones.

### Production

Publica:

- Planeación y estado de órdenes.
- Productos obtenidos y consumos.
- Relación con demanda de pedidos.
- Kilogramos procesados por periodo.

Consume contratos de Recipes, Inventory, CostAccounting y Orders.

### Purchasing

Publica:

- Compras, recepciones y estado de obligaciones.
- Datos de costo de una recepción.

Consume Inventory y Finance mediante contratos.

### Orders

Publica:

- Demanda pendiente.
- Reservas requeridas.
- Asignación a producciones.
- Cantidades listas y entregadas.

Consume Catalog, Inventory y consulta Production mediante contratos públicos.

### Sales

Publica:

- Venta confirmada y costo de venta.
- Cartera originada.
- Devoluciones.
- Historial del cliente.

Consume Catalog, Inventory, Orders, Finance y CashManagement.

### CashManagement

Publica:

- Estado de caja y sesión actual.
- Registro de movimientos permitidos.
- Traslados y cierres.

Consume Finance para asientos y saldos contables.

### Finance

Publica:

- Cuentas financieras.
- Registro de asientos balanceados.
- Cuentas por cobrar y pagar.
- Pagos y aplicaciones.
- Saldos por cuenta y tercero.

No decide cómo se fabrica o vende un producto.

### CostAccounting

Publica:

- Periodo mensual vigente.
- Tarifas efectivas.
- Aplicación de costos a producción.
- Fondos y variaciones.

Consume Finance para costos reales y Production para kilos procesados mediante consultas públicas, evitando ciclos directos mediante DTOs o eventos de cierre.

### Household

Publica:

- Presupuestos.
- Solicitudes de fondos.
- Deudas, préstamos y metas.
- Visibilidad de movimientos por habitante.

Consume People y Finance. Una Action coordinadora registra pagos desde negocio sin que Finance dependa de Household.

### Reporting

Solo lectura:

- Consume proyecciones o contratos de consulta.
- No modifica documentos operativos.
- Puede mantener tablas de resumen reconstruibles.

## Coordinadores de flujos críticos

Los casos que atraviesan varios módulos tendrán una Action coordinadora ubicada en el módulo que representa la intención principal.

| Acción | Módulo dueño | Colaboradores |
|---|---|---|
| `ReceivePurchase` | Purchasing | Inventory, Finance, Audit |
| `CompleteProduction` | Production | Recipes, Inventory, CostAccounting, Orders, Audit |
| `ConfirmSale` | Sales | Catalog, Inventory, Orders, CashManagement, Finance, Audit |
| `RegisterCustomerPayment` | Finance | Sales, CashManagement, Audit |
| `ConvertPackageForSale` | Sales | Catalog, Inventory |
| `PayFundRequestFromBusiness` | Household | People, Finance, CostAccounting, Audit |
| `CloseCashSession` | CashManagement | Finance, Audit |
| `CloseCostPeriod` | CostAccounting | Production, Finance, Audit |

## Eventos principales

### Personas e identidad

| Evento | Emisor | Consumidores principales |
|---|---|---|
| `PersonClassificationsChanged` | People | Identity, Reporting |
| `UserBlocked` | Identity | Revocación de sesiones, Audit |
| `AuthorizationApproved` | Identity | Acción solicitante, Audit |

### Catálogo e inventario

| Evento | Emisor | Consumidores principales |
|---|---|---|
| `ProductPresentationChanged` | Catalog | Sales read model, Reporting |
| `InventoryMovementPosted` | Inventory | Reporting, alertas internas |
| `InventoryWentBelowMinimum` | Inventory | Dashboard |
| `NegativeStockAuthorized` | Inventory | Audit, Dashboard |
| `PackageConverted` | Inventory | Sales, Reporting |

### Compras

| Evento | Emisor | Consumidores principales |
|---|---|---|
| `PurchaseConfirmed` | Purchasing | Finance para obligación, Audit |
| `PurchaseReceived` | Purchasing | Inventory, Reporting |
| `SupplierPaymentRegistered` | Purchasing/Finance | Purchasing read model, CashManagement |
| `PurchaseReturned` | Purchasing | Inventory, Finance |

### Recetas y producción

| Evento | Emisor | Consumidores principales |
|---|---|---|
| `RecipeVersionActivated` | Recipes | Production, Reporting |
| `ProductionPlanned` | Production | Orders, Dashboard |
| `ProductionStarted` | Production | Dashboard |
| `ProductionCompleted` | Production | Orders, CostAccounting, Reporting |
| `ProductionReversed` | Production | Inventory, Orders, CostAccounting, Reporting |

Los movimientos críticos de inventario de una producción se coordinan dentro de `CompleteProduction`; `ProductionCompleted` informa el resultado después de una confirmación consistente.

### Pedidos y ventas

| Evento | Emisor | Consumidores principales |
|---|---|---|
| `SalesOrderConfirmed` | Orders | Inventory reservations, Production demand |
| `SalesOrderDueSoon` | Orders | Dashboard |
| `SalesOrderReady` | Orders | Sales, Dashboard |
| `SaleConfirmed` | Sales | Reporting, customer statement projection |
| `SaleReturned` | Sales | Reporting |
| `CustomerPaymentRegistered` | Finance | Sales receivable projection, CashManagement |

Los efectos obligatorios de inventario y finanzas forman parte de `ConfirmSale`; el evento se publica después de que la venta quedó consistente.

### Caja y finanzas

| Evento | Emisor | Consumidores principales |
|---|---|---|
| `CashSessionOpened` | CashManagement | Dashboard, Audit |
| `CashSessionClosed` | CashManagement | Finance, Reporting, Audit |
| `CashDifferenceDetected` | CashManagement | Dashboard, Audit |
| `JournalEntryPosted` | Finance | Reporting |
| `ReceivableOverdue` | Finance | Dashboard |
| `PayableOverdue` | Finance | Dashboard |

### Costos y hogar

| Evento | Emisor | Consumidores principales |
|---|---|---|
| `CostPeriodOpened` | CostAccounting | Production, Dashboard |
| `CostPeriodClosed` | CostAccounting | Finance, Reporting |
| `FundRequestCreated` | Household | Dashboard |
| `FundRequestApproved` | Household | Responsable de pago, Audit |
| `FundRequestPaid` | Household | Finance, CostAccounting, Reporting |
| `BudgetThresholdReached` | Household | Dashboard |

## Eventos sincrónicos y posteriores al commit

### Deben resolverse antes de confirmar

- Validación de permisos y autorizaciones.
- Movimientos de inventario.
- Asientos financieros.
- Actualización del estado principal del documento.
- Aplicación de pagos.
- Reservas necesarias para mantener consistencia.

### Pueden ejecutarse después del commit

- Actualizar indicadores y proyecciones reconstruibles.
- Alertas del tablero.
- Exportaciones.
- Respaldos.
- Futuras notificaciones externas.

## Prevención de ciclos

- Finance no importa clases internas de Sales, Purchasing o Household.
- Inventory no conoce las entidades completas de documentos origen.
- Reporting nunca es dependencia de un módulo operativo.
- CostAccounting consulta kilos procesados mediante un contrato de lectura, no accediendo a tablas privadas de Production desde el dominio.
- Orders y Production se relacionan mediante identificadores y servicios públicos de asignación.
- Los DTOs compartidos entre módulos viven en el contrato público del módulo proveedor, no en `Shared` de forma indiscriminada.

## Versionado de contratos

Dentro del monolito, los contratos cambian junto con sus consumidores en una misma entrega. La API para Capacitor sí utilizará versión explícita:

```text
/api/mobile/v1
```

Un cambio incompatible en la API exige una nueva versión o un periodo de compatibilidad.

