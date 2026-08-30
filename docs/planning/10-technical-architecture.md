# Arquitectura técnica

## Enfoque

La aplicación será un monolito modular Laravel. Se conservará una sola aplicación y un solo despliegue, pero el código estará dividido por dominios funcionales con límites explícitos.

No se implementarán microservicios, CQRS completo ni repositorios genéricos. Se aplicará Clean Architecture en los flujos que afectan dinero, inventario, producción, costos y permisos.

## Objetivos arquitectónicos

- Proteger las reglas de negocio frente a detalles de Laravel, Inertia o MySQL.
- Mantener archivos y clases con una responsabilidad principal.
- Evitar que controladores y componentes Vue contengan cálculos del dominio.
- Permitir que los mismos casos de uso sean llamados desde Inertia y una API para Capacitor.
- Mantener operaciones críticas dentro de transacciones atómicas.
- Facilitar el reemplazo de SQLite por MySQL sin cambiar la lógica.
- Evitar abstracciones sin una necesidad concreta.

## Estructura propuesta

```text
app/
|-- Modules/
|   |-- Identity/
|   |   |-- Domain/
|   |   |-- Application/
|   |   |-- Infrastructure/
|   |   `-- Presentation/
|   |-- People/
|   |-- Catalog/
|   |-- Inventory/
|   |-- Recipes/
|   |-- Production/
|   |-- Purchasing/
|   |-- Orders/
|   |-- Sales/
|   |-- CashManagement/
|   |-- Finance/
|   |-- CostAccounting/
|   |-- Household/
|   |-- Reporting/
|   |-- Attachments/
|   |-- Audit/
|   `-- Backups/
|-- Shared/
|   |-- Domain/
|   |-- Application/
|   `-- Infrastructure/
`-- Providers/

resources/js/
|-- Pages/
|   |-- Dashboard/
|   |-- Inventory/
|   |-- Production/
|   |-- Sales/
|   `-- ...
|-- Components/
|   |-- Forms/
|   |-- Tables/
|   |-- Money/
|   `-- Feedback/
|-- Composables/
|-- Layouts/
|-- Types/
`-- Support/

routes/
|-- web.php
|-- api.php
`-- modules/
    |-- inventory.php
    |-- production.php
    `-- ...

database/
|-- migrations/
|-- factories/
`-- seeders/

tests/
|-- Unit/
|-- Feature/
`-- Support/
```

## Responsabilidad de cada capa

### Domain

Contiene comportamiento y lenguaje del negocio:

- Entidades y agregados.
- Objetos de valor.
- Enums de dominio.
- Reglas, especificaciones y políticas puras.
- Eventos de dominio.
- Contratos estrictamente necesarios.

No depende de controladores, Requests, Inertia, Vue ni respuestas HTTP.

### Application

Orquesta casos de uso:

- Actions o use cases.
- DTOs de entrada y salida.
- Queries de aplicación.
- Coordinadores de transacciones.
- Puertos hacia otros módulos o infraestructura.
- Manejadores de eventos.

Ejemplos:

```text
CompleteProduction
ConfirmSale
ReceivePurchase
RegisterCustomerPayment
PayFundRequestFromBusiness
CloseCostPeriod
```

### Infrastructure

Implementa detalles técnicos:

- Modelos Eloquent.
- Repositorios cuando sean necesarios.
- Persistencia de agregados.
- Almacenamiento de archivos.
- Generación de respaldos.
- Adaptadores de reloj, identificadores y servicios externos.
- Implementaciones de contratos entre módulos.

### Presentation

Adapta entradas y salidas:

- Controladores web y API.
- Form Requests.
- Resources JSON.
- Presentadores y ViewModels de Inertia.
- Definiciones de rutas.

No contiene cálculos financieros, escalado de recetas ni mutaciones directas de múltiples modelos.

## Flujo de una solicitud

```text
Vue/Inertia o Capacitor
-> Controller
-> FormRequest
-> DTO
-> Application Action
-> Domain + Contracts
-> Infrastructure/Eloquent
-> Result DTO
-> Inertia Props o JSON Resource
```

## Actions y transacciones

Una Action representa una intención completa del usuario, no una operación técnica pequeña.

Ejemplo `CompleteProduction`:

1. Carga la orden y valida su estado.
2. Verifica permisos y autorizaciones recibidas.
3. Comprueba el periodo de costos.
4. Calcula consumos, productos y costos.
5. Coordina movimientos de inventario.
6. Actualiza pedidos vinculados.
7. Confirma todo dentro de una transacción.
8. Publica eventos después del commit cuando corresponda.

No se crearán Actions para getters o asignaciones triviales.

## Entidades y modelos Eloquent

Para los agregados críticos se diferenciará:

- Entidad o modelo de dominio: comportamiento e invariantes.
- Modelo Eloquent: persistencia y relaciones.

Para catálogos simples podrá utilizarse Eloquent directamente desde una Action o Query, siempre que no filtre reglas importantes hacia el controlador.

Esta excepción evita sobrearquitectura sin debilitar producción, inventario, ventas, caja y finanzas.

## Repositorios

Se crearán repositorios únicamente cuando:

- Un agregado requiera cargar y persistir varias tablas como unidad.
- Exista una consulta de persistencia que deba aislarse del dominio.
- Sea necesario bloquear registros durante una transacción.
- Haya una implementación técnica reemplazable.

Repositorios iniciales probables:

```text
ProductionOrderRepository
InventoryLedgerRepository
SaleRepository
PurchaseRepository
FinancialJournalRepository
CostPeriodRepository
FundRequestRepository
```

No existirá un `BaseRepository` genérico con métodos CRUD para todo.

## Objetos de valor compartidos

El directorio `Shared/Domain` será pequeño:

- `Money`: importe entero y moneda COP.
- `Quantity`: valor, unidad y reglas de precisión.
- `Percentage`: porcentaje validado.
- `DateRange`: periodo válido.
- `DocumentNumber`: consecutivo legible.
- `Ulid`: identificador cuando resulte útil abstraerlo.

No se colocarán reglas específicas de producción o ventas dentro de `Shared`.

## Consultas y lectura

No se implementará CQRS completo. Se separarán conceptualmente:

- Actions para comandos que cambian estado.
- Query objects para listados, tableros y reportes.

Las consultas de lectura pueden usar Eloquent Query Builder y proyecciones optimizadas sin reconstruir agregados completos.

Ejemplos:

```text
GetDailyOwnerDashboard
ListPendingProductionDemand
GetInventoryValuation
GetCustomerStatement
GetHouseholdBudgetExecution
```

## Frontend

### Páginas

Cada Page corresponde a una pantalla enrutable y recibe props tipadas.

### Componentes

- Componentes visuales genéricos: botones, tablas, modales y campos.
- Componentes de dominio: selector de receta, resumen de caja o distribución de masa.
- Los componentes de dominio permanecen cerca de la página o módulo que los utiliza.

### Estado

- Estado local para formularios y componentes.
- Props de Inertia para datos del servidor.
- No se añadirá un store global hasta que exista una necesidad compartida real.
- Los cálculos autoritativos se realizan en el servidor; el frontend puede mostrar una previsualización que será validada nuevamente al confirmar.

### Tipos

- DTOs de presentación definidos explícitamente en TypeScript.
- Estados y permisos compartidos mediante tipos generados o mantenidos desde una fuente controlada.
- Nunca confiar en valores monetarios calculados únicamente por el navegador.

## Validación

Se aplican tres niveles:

1. Validación de forma en `FormRequest`.
2. Autorización mediante Policies o permisos.
3. Invariantes del dominio dentro de la Action o agregado.

Una validación del frontend mejora la experiencia, pero no reemplaza ninguna de las anteriores.

## Autorización

- Middleware para acceso general al módulo.
- Policies para recursos y ámbito propio/global.
- Permisos atómicos para acciones.
- `AuthorizationRequest` para excepciones de una operación concreta.
- Nueva autenticación para acciones críticas definidas en la matriz de permisos.

## Concurrencia e idempotencia

- Las confirmaciones críticas aceptan una clave de idempotencia.
- MySQL utiliza bloqueo de filas para inventario, caja, consecutivos y documentos que cambian de estado.
- La validación del estado se repite dentro de la transacción.
- SQLite se usa para desarrollo, sin asumir que su comportamiento de concurrencia representa producción.

## Eventos y trabajos diferidos

Los efectos que definen la consistencia de una operación se ejecutan sincrónicamente dentro de la transacción o mediante un coordinador explícito.

Se pueden diferir después del commit:

- Actualización de reportes pesados.
- Generación de archivos.
- Respaldos.
- Limpieza de archivos temporales.
- Futuras notificaciones.

No se diferirá una salida de inventario o asiento financiero cuya ausencia deje un documento confirmado inconsistente.

## Auditoría

La auditoría se genera desde las Actions o eventos confirmados:

- Actor.
- Acción.
- Documento.
- Fecha efectiva y técnica.
- Dirección IP cuando exista.
- Autorización relacionada.
- Cambios relevantes, sin secretos.

## Errores

- Excepciones de dominio con mensajes traducibles y códigos estables.
- Errores de validación asociados al campo correspondiente.
- Errores inesperados registrados con identificador de correlación.
- El usuario recibe un mensaje seguro y el identificador para soporte.

## API para Capacitor

- Prefijo `/api/mobile/v1`.
- Controladores y Resources propios.
- Reutilización de Actions y Policies.
- Autenticación revocable por dispositivo.
- Sin acceso directo a modelos desde el controlador.
- Sin lógica duplicada respecto a Inertia.

## Decisiones técnicas registradas

1. Monolito modular antes que microservicios.
2. Eloquent como implementación de persistencia.
3. Repositorios solo para agregados y necesidades concretas.
4. Actions para casos de uso que cambian estado.
5. Queries directas y optimizadas para lectura.
6. Eventos internos para desacoplar módulos, sin eventual consistency en efectos críticos.
7. Dinero como entero COP.
8. Cantidades mediante objeto de valor y precisión decimal.
9. SQLite en desarrollo y MySQL en producción.
10. Inertia para web y API versionada para Capacitor futuro.

