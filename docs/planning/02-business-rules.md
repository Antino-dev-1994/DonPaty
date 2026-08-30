# Reglas de negocio

## Reglas transversales

1. Las operaciones compuestas deben confirmarse dentro de una única transacción de base de datos.
2. Los documentos confirmados no se editan directamente; se revierten o ajustan mediante documentos relacionados.
3. No se eliminan físicamente ventas, producciones, compras, pagos ni movimientos confirmados.
4. Los saldos de inventario y dinero se obtienen desde movimientos, no mediante edición manual directa.
5. Los reintentos y dobles clics no pueden duplicar una operación.
6. Toda excepción requiere usuario, fecha, motivo y, cuando corresponda, autorizador.
7. Los precios, costos y versiones de receta utilizados deben conservarse históricamente.
8. Las fechas se presentan en `America/Bogota`; la auditoría técnica se conserva en UTC.

## Inventario

- La fuente de verdad es el libro de movimientos de inventario.
- Existencia disponible = existencia física menos cantidad reservada.
- Una entrada por compra actualiza el costo promedio ponderado móvil.
- Una salida utiliza el costo vigente al momento de confirmarse.
- El inventario negativo se permite únicamente después de mostrar advertencia y obtener autorización.
- Una operación autorizada con inventario negativo registra existencia anterior, existencia posterior, costo estimado y motivo.
- Cuando ingrese inventario posteriormente, el sistema deberá identificar las existencias negativas pendientes de regularización.
- Los ajustes manuales siempre requieren motivo.

## Paquetes

- Un paquete se compone exclusivamente de presentaciones del mismo producto.
- Armar un paquete descuenta sus unidades componentes y aumenta el inventario del paquete.
- Desarmar un paquete descuenta el paquete y devuelve sus unidades componentes.
- El costo total se conserva durante la conversión.
- Si una venta necesita unidades y solo existen paquetes, el sistema propondrá desarmar la cantidad mínima necesaria.
- Si una venta necesita paquetes y existen unidades suficientes, el sistema podrá proponer el armado.

## Recetas

- Una receta representa una masa base.
- Cada receta posee versiones independientes e históricas.
- Solo una versión puede estar activa para una fecha determinada.
- Activar una versión no modifica producciones anteriores.
- Los ingredientes se expresan en unidad base y porcentaje panadero cuando corresponda.
- Cada producto compatible define el peso de masa requerido por unidad y su factor de distribución de costo.

## Producción

- Toda producción referencia una versión exacta de receta.
- La harina indicada determina el factor de escala de ingredientes y rendimiento.
- La producción registra cantidades calculadas y cantidades reales.
- Las diferencias no modifican la receta; se registran como novedades del lote.
- Una masa puede distribuirse entre varios productos.
- Completar una producción genera simultáneamente consumos, productos obtenidos, merma, costos y atención de demanda de pedidos.
- Una producción completada no puede editarse.
- Una reversión genera movimientos opuestos y reabre, cuando corresponda, demanda de pedidos.

## Mano de obra

- Cada producción elige entre tarifa estándar por kilogramo, horas reales o tarifa manual autorizada.
- Las horas reales identifican persona, duración y tarifa.
- Un pago real de mano de obra alimenta el fondo de costos del periodo.
- El costo aplicado a una producción es analítico y no debe duplicar el gasto financiero real.

## Electricidad, gas y costos indirectos

- Cada mes debe existir un periodo de costos antes de completar producciones.
- La factura del periodo anterior registra valor total y porcentajes manuales del negocio y hogar.
- Tarifa sugerida = valor productivo del periodo anterior dividido entre los kilogramos de harina procesados en ese periodo.
- Si no hay historial suficiente, se exige una tarifa manual inicial.
- Una tarifa manual requiere motivo y responsable.
- Al cierre mensual se calcula la diferencia entre costo real y costo aplicado a producciones.
- La rentabilidad durante un mes abierto se identifica como provisional.

## Pedidos

- Un pedido puede recibir anticipos y entregas parciales.
- Los productos pedidos pueden reservar inventario existente.
- La demanda pendiente considera cantidad pedida, reservada, producida, entregada e inventario disponible.
- El sistema convierte la demanda pendiente en masa y harina sugeridas mediante los productos compatibles de la receta.
- Una producción puede atender varios pedidos y un pedido puede atenderse con varias producciones.
- Los pedidos próximos se muestran desde 24 horas antes de su entrega.

## Precios y ventas

- La lista minorista es la predeterminada.
- Un cliente puede tener asignada una lista mayorista u otra lista vigente.
- El usuario puede aplicar un precio personalizado si posee permiso.
- Vender por debajo del mínimo requiere autorización y motivo.
- Confirmar una venta afecta inventario, costo de venta, ingreso, caja o cartera en una sola operación.
- Registrar un abono reduce cartera y aumenta caja o banco; no crea una nueva venta.
- Las devoluciones se documentan de forma independiente.
- Un producto devuelto solo regresa al inventario si se declara apto.

## Compras y obligaciones

- Compra, recepción y pago son hechos diferentes.
- Una compra puede estar pendiente, parcialmente recibida, recibida, parcialmente pagada o pagada.
- La recepción afecta inventario.
- El pago afecta caja, banco o cuentas por pagar.
- Un costo de transporte puede distribuirse entre los artículos recibidos.

## Caja

- Solo puede existir una sesión abierta por caja menor.
- La apertura registra base inicial y responsable.
- El saldo esperado se deriva de los movimientos confirmados.
- El cierre registra saldo contado y diferencia.
- Faltantes y sobrantes requieren explicación; los valores que superen el límite configurado requieren autorización.
- Los traslados entre caja principal y caja menor no son ingresos ni gastos.
- Efectivo total = caja principal más caja menor.

## Hogar y solicitudes

- Un habitante ve sus movimientos y solicitudes; los propietarios ven el consolidado autorizado.
- Una solicitud tiene origen hogar o negocio.
- El solicitante no puede aprobar su propia solicitud salvo que sea propietario y la política configurada lo permita.
- Una solicitud pagada por el negocio genera gasto de mano de obra, salida de la cuenta del negocio y entrada en la cuenta del habitante.
- Una solicitud pagada por el hogar se trata como transferencia interna cuando el destino sigue dentro del patrimonio familiar.
- Préstamos y deudas separan capital, intereses, cuotas y pagos.

## Contabilidad administrativa

- Internamente, cada operación financiera genera un asiento balanceado.
- Las transferencias no alteran el resultado económico.
- Las compras de inventario no son gasto completo del día; se reconocen como inventario y posteriormente como costo de venta.
- El tablero diferencia flujo de caja de rentabilidad.
- Los costos reales y aplicados se concilian para impedir doble contabilización.

## Adjuntos, auditoría y respaldos

- Los comprobantes son opcionales salvo que una política futura los haga obligatorios.
- Los adjuntos se almacenan de forma privada y se entregan mediante acceso temporal autorizado.
- La auditoría registra actor, acción, documento, fecha, dirección IP y cambios relevantes.
- Los respaldos incluyen base de datos, adjuntos, manifiesto y verificación de integridad.
- La restauración será una acción administrativa protegida.

