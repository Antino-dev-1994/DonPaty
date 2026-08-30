# Esquema físico de datos

Este documento define las tablas previstas, sus columnas principales e invariantes. No sustituye las migraciones, pero será su referencia.

## Convenciones

- Clave primaria: `id` ULID almacenado como `CHAR(26)` compatible con SQLite y MySQL.
- Claves foráneas: sufijo `_id`.
- Dinero: `BIGINT` en pesos colombianos.
- Cantidades: `DECIMAL(18,4)` en unidad base.
- Tarifas: `DECIMAL(18,6)`.
- Porcentajes: `DECIMAL(7,4)`.
- Estados: cadena corta validada por enum de aplicación, no enum nativo del motor.
- Fechas operativas: `DATE` o `DATETIME` según necesidad.
- Auditoría técnica: `created_at`, `updated_at` y, solo en catálogos, `deleted_at`.
- Metadatos no consultables: JSON opcional; las reglas importantes usan columnas normales.
- Los documentos confirmados no usan `deleted_at`.

## Identity y People

### `people`

Columnas principales:

- `id`, `name`, `document_type`, `document_number`.
- `email`, `phone`, `notes`, `is_active`.
- Timestamps y eliminación lógica.

Índices:

- Documento, cuando exista.
- Nombre.
- Correo y teléfono para búsqueda.

### `person_classifications`

- `id`, `person_id`, `classification`.
- `effective_from`, `effective_to` opcionales.

Restricción única por persona, clasificación y periodo activo controlada por aplicación.

### `users`

- Columnas de autenticación de Laravel.
- `person_id` opcional y único cuando exista.
- `status`, `last_login_at`.

### `roles`, `permissions`, `role_user`, `permission_role`

- Roles y permisos configurables.
- Restricción única por nombre técnico.
- Las tablas pivote tienen pares únicos.

### `authorization_requests`

- `id`, `operation_type`, `resource_type`, `resource_id`.
- `requested_by`, `approved_by` opcional.
- `reason`, `decision_notes`, `status`.
- `expires_at`, `decided_at`, `used_at`.

Índice por recurso, estado y expiración.

### `user_sessions`

- Identificador de sesión o dispositivo.
- `user_id`, nombre de dispositivo, última actividad, dirección IP resumida y revocación.

## Catalog

### `units`

- `id`, `code`, `name`, `dimension`, `scale_to_base`, `precision`, `is_active`.
- Código único.

Dimensiones iniciales: mass, volume y count.

### `items`

- `id`, `code`, `name`, `type`, `base_unit_id`.
- `minimum_stock`, `allow_negative_stock`, `is_active`, `notes`.
- Eliminación lógica.

Índices por código único, nombre, tipo y estado.

### `product_presentations`

- `id`, `item_id`, `sku`, `name`.
- `stock_unit_id`, `conversion_to_item_base`.
- `is_purchasable`, `is_sellable`, `is_stockable`, `is_active`.
- `barcode` opcional, `minimum_sale_price`.

SKU único; código de barras único cuando exista.

### `package_components`

- `id`, `package_presentation_id`, `component_presentation_id`, `quantity`.
- Par de presentaciones único.
- La aplicación valida que ambas pertenezcan al mismo artículo.

### `price_lists`

- `id`, `name`, `type`, `starts_at`, `ends_at`, `is_default`, `is_active`.

### `price_list_items`

- `id`, `price_list_id`, `presentation_id`, `price`, `minimum_price` opcional.
- Par lista/presentación único.

### `customer_profiles` y `supplier_profiles`

- `id`, `person_id` único.
- Condiciones comerciales, lista de precios o términos de pago predeterminados.
- No duplican datos personales básicos.

## Inventory

### `inventory_movements`

- `id`, `document_number`, `movement_type`, `effective_at`.
- `source_type`, `source_id`, `status`, `notes`.
- `created_by`, `authorization_request_id` opcional.
- `reversal_of_id` opcional.

Índices por fecha, tipo, documento origen y estado.

### `inventory_movement_lines`

- `id`, `inventory_movement_id`, `presentation_id`.
- `quantity_in`, `quantity_out`.
- `unit_cost`, `total_cost`.
- `balance_before`, `balance_after`.

Una línea tiene entrada o salida, nunca ambas. La cantidad debe ser positiva.

### `inventory_balances`

- `id`, `presentation_id` único.
- `physical_quantity`, `reserved_quantity`, `average_unit_cost`, `updated_at`.

Es una proyección transaccional reconstruible desde movimientos y reservas.

### `inventory_reservations`

- `id`, `presentation_id`, `sales_order_line_id`.
- `quantity`, `status`, `reserved_at`, `released_at`.

Índices por presentación, pedido y estado.

### `inventory_adjustments`

- `id`, `document_number`, `effective_at`, `status`, `reason`.
- `created_by`, `confirmed_by`, `inventory_movement_id`.

### `inventory_adjustment_lines`

- Presentación, saldo esperado, conteo físico, diferencia y costo.

### `package_conversions`

- `id`, `document_number`, `conversion_type`, `package_presentation_id`.
- `package_quantity`, `component_presentation_id`, `component_quantity`.
- `total_cost`, `source_type`, `source_id`, `status`, `created_by`.
- Relación al movimiento de inventario generado.

### `negative_stock_incidents`

- `id`, `presentation_id`, `inventory_movement_line_id`.
- `authorization_request_id`, `negative_quantity`, `estimated_unit_cost`.
- `status`, `regularized_at`.

## Recipes

### `recipes`

- `id`, `code`, `name`, `description`, `is_active`.
- Código único y eliminación lógica.

### `recipe_versions`

- `id`, `recipe_id`, `version_number`, `status`.
- `effective_from`, `reference_flour_quantity`.
- `expected_dough_yield`, `expected_waste_percentage`.
- `instructions`, `created_by`, `activated_by`, `activated_at`.

Par receta/número de versión único.

### `recipe_ingredients`

- `id`, `recipe_version_id`, `item_id`, `presentation_id` opcional.
- `ingredient_role`, `quantity`, `unit_id`, `baker_percentage`.
- `allows_substitution`, `sort_order`.

### `recipe_compatible_products`

- `id`, `recipe_version_id`, `presentation_id`.
- `dough_weight_per_unit`, `baking_loss_percentage`, `cost_weight_factor`.
- Par versión/presentación único.

### `product_finishing_components`

- `id`, `recipe_compatible_product_id`, `item_id`, `quantity_per_unit`, `unit_id`.
- Incluye rellenos, cubiertas y empaques propios.

## CostAccounting

### `cost_periods`

- `id`, `year`, `month`, `status`.
- `processed_flour_quantity`, `opened_at`, `opened_by`.
- `closed_at`, `closed_by`, `reopened_at`, `reopened_by`.

Par año/mes único.

### `utility_cost_records`

- `id`, `cost_period_id`, `utility_type`.
- `billed_from`, `billed_to`, `paid_at`.
- `total_amount`, `business_percentage`, `household_percentage`.
- `business_amount`, `household_amount`, `physical_consumption` opcional.
- `attachment_id` opcional.

### `overhead_rates`

- `id`, `cost_period_id`, `cost_type`.
- `suggested_rate`, `manual_rate` opcional, `effective_rate`.
- `calculation_base_quantity`, `method`, `override_reason`, `set_by`.
- Par periodo/tipo único.

### `cost_pool_entries`

- `id`, `cost_period_id`, `cost_type`, `amount`.
- `source_type`, `source_id`, `effective_at`.

### `cost_allocations`

- `id`, `cost_period_id`, `production_order_id`, `cost_type`.
- `base_quantity`, `rate`, `amount`.

### `cost_variances`

- `id`, `cost_period_id`, `cost_type`.
- `actual_amount`, `allocated_amount`, `variance_amount`.
- Par periodo/tipo único.

## Production

### `production_orders`

- `id`, `document_number`, `recipe_version_id`, `cost_period_id`.
- `planned_for`, `started_at`, `completed_at`.
- `status`, `flour_quantity`, `expected_dough_quantity`, `actual_dough_quantity`.
- `labor_method`, `responsible_person_id`, `created_by`.
- `reversal_of_id` opcional.

Índices por estado, fecha, receta y periodo de costos.

### `production_planned_outputs`

- `id`, `production_order_id`, `presentation_id`.
- `planned_quantity`, `planned_dough_quantity`.

### `production_consumptions`

- `id`, `production_order_id`, `item_id`, `presentation_id` opcional.
- `calculated_quantity`, `actual_quantity`, `unit_id`.
- `unit_cost`, `total_cost`, `difference_reason`.

### `production_outputs`

- `id`, `production_order_id`, `presentation_id`.
- `quantity`, `dough_quantity`, `waste_quantity`.
- `allocated_cost`, `unit_cost`.

### `production_incidents`

- `id`, `production_order_id`, `incident_type`, `description`.
- `quantity` y `amount` opcionales.
- `recorded_by`, `recorded_at`.

### `production_labor_entries`

- `id`, `production_order_id`, `person_id` opcional.
- `method`, `hours`, `hourly_rate`, `flour_rate`, `manual_amount`, `total_amount`.
- Solo las columnas correspondientes al método son obligatorias.

### `production_order_allocations`

- `id`, `production_order_id`, `sales_order_line_id`.
- `planned_quantity`, `produced_quantity`, `reserved_quantity`.
- Par orden/línea único.

## Orders

### `sales_orders`

- `id`, `document_number`, `customer_person_id`.
- `ordered_at`, `due_at`, `priority`, `status`.
- `price_list_id`, `subtotal`, `discount`, `total`, `advance_amount`, `balance_amount`.
- `notes`, `created_by`.

Índices por cliente, vencimiento y estado.

### `sales_order_lines`

- `id`, `sales_order_id`, `presentation_id`.
- `ordered_quantity`, `reserved_quantity`, `produced_quantity`, `delivered_quantity`.
- `unit_price`, `discount`, `line_total`, `notes`.

### `order_status_history`

- `id`, `sales_order_id`, `from_status`, `to_status`, `changed_by`, `changed_at`, `notes`.

## Sales

### `sales`

- `id`, `document_number`, `customer_person_id` opcional, `sales_order_id` opcional.
- `sold_at`, `status`, `price_list_id`.
- `subtotal`, `discount`, `total`, `paid_amount`, `balance_amount`.
- `cost_of_goods_sold`, `gross_profit`, `created_by`.
- `reversal_of_id` opcional.

### `sale_lines`

- `id`, `sale_id`, `presentation_id`, `quantity`.
- `list_unit_price`, `applied_unit_price`, `discount`, `line_total`.
- `unit_cost`, `total_cost`, `price_override_id` opcional.

### `price_overrides`

- `id`, `presentation_id`, `requested_price`, `minimum_price`.
- `reason`, `requested_by`, `authorization_request_id`.

### `sale_returns` y `sale_return_lines`

- Referencia a venta y líneas originales.
- Cantidad, condición física, valor devuelto y costo revertido.
- Movimiento de inventario solo cuando el producto sea apto.

## Purchasing

### `purchases`

- `id`, `document_number`, `supplier_person_id`.
- `supplier_document_number`, `issued_at`, `due_at`, `status`.
- `subtotal`, `additional_costs`, `total`, `paid_amount`, `balance_amount`.
- `created_by`, `reversal_of_id` opcional.

### `purchase_lines`

- `id`, `purchase_id`, `presentation_id`.
- `ordered_quantity`, `received_quantity`, `unit_price`, `allocated_additional_cost`, `line_total`.

### `purchase_receipts`

- `id`, `document_number`, `purchase_id`, `received_at`, `status`, `received_by`.
- Relación al movimiento de inventario.

### `purchase_receipt_lines`

- `id`, `purchase_receipt_id`, `purchase_line_id`, `quantity`, `unit_cost`, `total_cost`.

### `purchase_returns` y `purchase_return_lines`

- Documento, compra, cantidades, costos y movimientos inversos.

## Finance y CashManagement

### `financial_accounts`

- `id`, `code`, `name`, `account_type`, `scope`.
- `person_id` opcional, `parent_id` opcional, `is_active`.
- Código único.

Ámbitos: business, household y personal.

### `journal_entries`

- `id`, `document_number`, `effective_at`, `description`, `status`.
- `source_type`, `source_id`, `posted_by`, `reversal_of_id` opcional.

### `journal_lines`

- `id`, `journal_entry_id`, `financial_account_id`.
- `debit_amount`, `credit_amount`, `person_id` opcional, `description`.

Cada línea tiene débito o crédito. El total débito debe igualar el total crédito antes de publicar.

### `receivables` y `payables`

- `id`, tercero, documento origen, fecha, vencimiento.
- `original_amount`, `paid_amount`, `balance_amount`, `status`.
- Índices por tercero, vencimiento y estado.

### `payments`

- `id`, `document_number`, `direction`, `person_id` opcional.
- `paid_at`, `amount`, `financial_account_id`, `status`, `reference`, `created_by`.

### `payment_allocations`

- `id`, `payment_id`, `allocatable_type`, `allocatable_id`, `amount`.
- La suma aplicada no puede superar el pago ni la obligación.

### `cash_sessions`

- `id`, `financial_account_id`, `opened_by`, `opened_at`, `opening_amount`.
- `expected_closing_amount`, `counted_closing_amount`, `difference_amount`.
- `closed_by`, `closed_at`, `status`, `authorization_request_id` opcional.

Solo una sesión abierta por cuenta, reforzado por aplicación y bloqueo transaccional.

### `cash_transfers`

- `id`, `document_number`, `from_account_id`, `to_account_id`.
- `amount`, `transferred_at`, `reason`, `created_by`, `status`.
- Las cuentas origen y destino deben diferir.

### `expense_records` y `income_records`

- Ámbito, categoría, persona, importe, fecha efectiva, vencimiento, estado y documento financiero.
- Permiten representar pagado, parcial o pendiente sin sustituir el libro contable.

### `financial_categories`

- `id`, `scope`, `type`, `name`, `parent_id`, `is_active`.

## Household

### `household_budgets`

- `id`, `year`, `month`, `status`, `created_by`.
- Par año/mes único.

### `household_budget_lines`

- `id`, `household_budget_id`, `financial_category_id`, `person_id` opcional.
- `budgeted_amount`.
- Combinación presupuesto/categoría/persona única.

### `fund_requests`

- `id`, `document_number`, `requester_person_id`, `source_scope`.
- `amount`, `reason`, `needed_at`, `status`.
- `approved_by`, `approved_at`, `rejection_reason`.
- `payment_id` opcional, `confirmed_at`.

### `debts`

- `id`, `person_id`, `direction`, `description`.
- `principal_amount`, `interest_rate`, `start_date`, `status`.

### `debt_installments`

- `id`, `debt_id`, `sequence`, `due_at`.
- `principal_amount`, `interest_amount`, `paid_amount`, `status`.
- Par deuda/secuencia único.

### `loans`

Puede unificarse con `debts` mediante `direction` si el modelo final mantiene claridad. La decisión se tomará al diseñar la migración, evitando dos agregados duplicados.

### `savings_goals`

- `id`, `name`, `person_id` opcional, `financial_account_id`.
- `target_amount`, `target_date`, `status`.

## Attachments, Audit y Backups

### `attachments`

- `id`, `attachable_type`, `attachable_id`.
- `disk`, `path`, `original_name`, `mime_type`, `size`, `checksum`.
- `uploaded_by`, `created_at`.

### `audit_events`

- `id`, `actor_user_id`, `action`, `resource_type`, `resource_id`.
- `authorization_request_id` opcional.
- `before_data`, `after_data` JSON filtrado.
- `ip_address`, `correlation_id`, `created_at`.

Índices por actor, recurso, acción y fecha.

### `backup_records`

- `id`, `status`, `disk`, `path`, `manifest_path`.
- `checksum`, `size`, `started_at`, `completed_at`, `created_by`, `error_message` seguro.

### `document_sequences`

- `id`, `document_type`, `year`, `next_number`, `prefix`.
- Combinación tipo/año única y actualización con bloqueo.

### `idempotency_keys`

- `id`, `user_id`, `operation`, `key`, `resource_type`, `resource_id`.
- `request_hash`, `response_code`, `expires_at`.
- Combinación usuario/operación/clave única.

## Índices prioritarios

- Fechas y estados en pedidos, ventas, compras, producciones, obligaciones y movimientos.
- Documento origen en inventario, asientos y auditoría.
- Presentación y fecha en líneas de inventario.
- Tercero, estado y vencimiento en cartera.
- Periodo y tipo en costos.
- Usuario, operación y clave en idempotencia.
- Campos de búsqueda: códigos, SKU, nombres, documentos y teléfonos.

## Restricciones que deben reforzarse en aplicación

SQLite no aplica de la misma manera todas las restricciones de MySQL. Deben verificarse dentro del dominio y las Actions:

- Asientos balanceados.
- Una sola sesión de caja abierta.
- Una sola versión vigente por receta y fecha.
- Una tarifa efectiva por periodo y tipo.
- Porcentajes hogar/negocio iguales a 100 %.
- Entradas y salidas mutuamente excluyentes.
- No sobreaplicar pagos.
- No distribuir más masa de la disponible.
- Paquetes compuestos por el mismo producto.
- Transiciones de estado válidas.
- Autorizaciones vigentes y no reutilizadas.

## Orden recomendado de migraciones

```text
1.  people, users, roles, permissions
2.  units, items, presentations, prices
3.  financial accounts and categories
4.  inventory movements and balances
5.  recipes and versions
6.  cost periods and rates
7.  production
8.  customers, suppliers and orders
9.  purchases and receipts
10. sales and returns
11. journal, receivables, payables and payments
12. cash sessions and transfers
13. household budgets, requests, debts and goals
14. attachments, audit, backups, sequences and idempotency
```

Las claves foráneas que representen colaboraciones entre módulos se agregan cuando ambas tablas existen. Las migraciones no deben depender de SQL exclusivo de MySQL.

## Datos iniciales

Los seeders de instalación crearán únicamente:

- Unidades base.
- Roles y permisos iniciales.
- Listas de precios iniciales.
- Tipos y categorías mínimas configurables.
- Cuentas contables y financieras base.
- Configuración de COP y `America/Bogota`.

No crearán productos, recetas, personas o saldos ficticios en producción.
