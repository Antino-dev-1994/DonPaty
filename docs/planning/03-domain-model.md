# Modelo de dominio

Este documento describe agregados y relaciones conceptuales. Los nombres definitivos de migraciones y clases se validarán durante el diseño técnico detallado.

## Convenciones de persistencia

- Identificadores ULID.
- Dinero COP como entero.
- Cantidades en unidad base con precisión decimal.
- Estados mediante enums.
- Fechas efectivas separadas de fechas de creación y auditoría.
- Claves foráneas y restricciones compatibles con SQLite y MySQL.
- Reglas críticas reforzadas en el dominio, no dependientes únicamente del motor de base de datos.

## Módulos

```text
Identity
People
Catalog
Inventory
Recipes
Production
Purchasing
Orders
Sales
CashManagement
Finance
CostAccounting
Household
Reporting
Attachments
Audit
Backups
Shared
```

Cada módulo se organizará conceptualmente en:

```text
Domain/
Application/
Infrastructure/
Presentation/
```

## Identity y People

### Entidades

- `Person`: persona natural relacionada con el sistema.
- `PersonClassification`: propietario, habitante, empleado, cliente u otra clasificación.
- `User`: credenciales asociadas opcionalmente a una persona.
- `Role` y `Permission`: autorización configurable.
- `AuthorizationRequest`: aprobación de operaciones excepcionales.
- `UserSession`: control de sesiones y revocación por dispositivo.

## Catalog

### Entidades

- `Item`: materia prima, insumo, empaque, intermedio, producto terminado o reventa.
- `Unit`: gramo, kilogramo, mililitro, litro o unidad.
- `UnitConversion`: conversión válida entre unidades compatibles.
- `ProductPresentation`: SKU comprable, vendible o inventariable.
- `PackageComponent`: composición de una presentación empacada.
- `PriceList`: minorista, mayorista, promocional u otra.
- `PriceListItem`: precio de una presentación dentro de una lista.
- `CustomerPriceList`: lista predeterminada de un cliente.
- `PriceOverride`: precio excepcional y autorización.

## Inventory

### Agregado `InventoryMovement`

- Encabezado con tipo, fecha efectiva, documento origen, usuario y estado.
- Líneas con artículo, presentación, entrada, salida, costo y existencias anterior/posterior.

### Proyecciones y documentos

- `InventoryBalance`: cantidad física, reservada, disponible y costo promedio.
- `InventoryReservation`: reserva originada por un pedido.
- `InventoryAdjustment`: ajuste documentado.
- `PackageConversion`: armado o desarmado de paquetes.
- `NegativeStockIncident`: excepción pendiente de regularización.

### Tipos de movimiento

```text
PurchaseReceipt
ProductionConsumption
ProductionOutput
Sale
SaleReturn
Waste
PackageAssembly
PackageDisassembly
ManualAdjustment
Reversal
```

## Recipes

### Agregado `Recipe`

- `Recipe`: identidad de la masa base.
- `RecipeVersion`: fórmula histórica y vigencia.
- `RecipeIngredient`: cantidad, unidad y porcentaje panadero.
- `RecipeCompatibleProduct`: producto que puede fabricarse con la masa.
- `ProductFinishingComponent`: rellenos, cubiertas o empaques propios del producto.

Cada producto compatible define gramos de masa por unidad, merma esperada y factor de distribución de costo.

## Production

### Agregado `ProductionOrder`

- `ProductionOrder`: receta, versión, harina, fechas, responsable, método de mano de obra y estado.
- `ProductionPlannedOutput`: productos esperados y peso requerido.
- `ProductionConsumption`: cantidades calculadas y reales.
- `ProductionOutput`: productos obtenidos, peso y costo asignado.
- `ProductionIncident`: novedades y diferencias.
- `ProductionLabor`: horas reales, tarifa estándar o valor manual.
- `ProductionOverheadAllocation`: gas, electricidad y otros costos aplicados.
- `ProductionOrderAllocation`: relación entre producciones y líneas de pedidos.

Estados:

```text
Draft -> Planned -> InProgress -> Completed -> Reversed
```

## CostAccounting

- `CostPeriod`: periodo mensual pendiente, abierto o cerrado.
- `UtilityCostRecord`: factura, distribución hogar/negocio y consumo opcional.
- `OverheadRate`: tarifa sugerida, manual y efectiva.
- `CostPoolEntry`: costo real de mano de obra o servicio.
- `CostAllocation`: valor aplicado a una producción.
- `CostVariance`: diferencia al cerrar el periodo.

## Orders

### Agregado `SalesOrder`

- `SalesOrder`: cliente, entrega, prioridad, anticipo, saldo y estado.
- `SalesOrderLine`: producto, cantidades y precio acordado.
- `ProductionDemand`: cantidad, masa y harina pendientes.
- `ProductionOrderAllocation`: cumplimiento mediante producciones.

Estados:

```text
Draft
-> Confirmed
-> PendingProduction
-> InProduction
-> Ready
-> Delivered
-> Cancelled
```

## Sales

### Agregado `Sale`

- `Sale`: cliente, pedido opcional, totales, costo, utilidad y estado.
- `SaleLine`: presentación, cantidad, precio, descuento y costo.
- `SaleReturn`: devolución documentada.
- `Receivable`: cartera originada por una venta.
- `Payment`: dinero recibido.
- `PaymentAllocation`: distribución de un pago entre obligaciones.

Estados principales:

```text
Draft -> Confirmed -> PartiallyPaid -> Paid -> Reversed
```

## Purchasing

- `Supplier`: proveedor.
- `Purchase`: factura u obligación de compra.
- `PurchaseLine`: productos, cantidades y valores.
- `PurchaseReceipt`: recepción física.
- `Payable`: cuenta por pagar.
- `SupplierPayment`: pago total o parcial.
- `PurchaseReturn`: devolución al proveedor.

## CashManagement y Finance

- `FinancialAccount`: caja principal, caja menor, banco, billetera o cuenta personal.
- `CashSession`: apertura y cierre de caja menor.
- `CashTransfer`: traslado entre cuentas.
- `JournalEntry`: encabezado de asiento financiero.
- `JournalLine`: débito o crédito por cuenta.
- `Receivable`: cuenta por cobrar.
- `Payable`: cuenta por pagar.

Toda operación financiera confirmada genera un asiento balanceado, aunque este detalle permanezca oculto para usuarios operativos.

## Household

- `HouseholdBudget`: presupuesto mensual por categoría y habitante opcional.
- `HouseholdTransaction`: ingreso, gasto o movimiento personal.
- `FundRequest`: solicitud al hogar o negocio.
- `Debt` y `DebtInstallment`: deuda por pagar.
- `Loan` y `LoanInstallment`: préstamo recibido u otorgado.
- `DebtPayment` y `LoanPayment`: pagos y aplicaciones.
- `SavingsGoal`: meta de ahorro.

Estados de solicitud:

```text
Requested -> Approved -> Paid -> Confirmed
          -> Rejected
          -> Cancelled
```

## Servicios transversales

- `Attachment`: archivo privado asociado a un documento.
- `AuditEvent`: historial de acciones relevantes.
- `BackupRecord`: respaldo, integridad y ubicación.
- `DocumentSequence`: consecutivos legibles para ventas, pedidos, compras y producciones.
- `IdempotencyKey`: protección frente a reintentos.

## Relaciones principales

```text
Person -- User -- Roles/Permissions

Item -- ProductPresentation -- PriceListItem
  |             |
  |             `-- PackageComponent
  `-- InventoryMovementLine -- InventoryMovement

Recipe -- RecipeVersion -- RecipeIngredient
                    |
                    `-- RecipeCompatibleProduct
                              |
                              `-- ProductionOrder
                                    |-- ProductionConsumption
                                    |-- ProductionOutput
                                    |-- ProductionIncident
                                    |-- ProductionLabor
                                    `-- ProductionOverheadAllocation

Customer -- SalesOrder -- SalesOrderLine
                |               |
                |               `-- ProductionOrderAllocation -- ProductionOrder
                `-- Sale -- SaleLine -- InventoryMovement
                       |-- Payment -- PaymentAllocation
                       `-- Receivable

Supplier -- Purchase -- PurchaseReceipt -- InventoryMovement
                |-- Payable
                `-- SupplierPayment

Person -- FundRequest -- Payment/Transfer
       |-- HouseholdBudget
       |-- Debt
       `-- Loan
```

## API y Capacitor

- La presentación web utilizará Inertia.
- Los casos de uso no dependerán de Inertia ni de controladores HTTP.
- Una API `/api/mobile/v1` podrá exponer los mismos casos de uso para Capacitor.
- La API tendrá autenticación revocable, autorización del lado del servidor y respuestas versionadas.
- Capacitor no alojará la base de datos principal ni implementará sincronización offline en la primera versión.

