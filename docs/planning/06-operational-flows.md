# Flujos operativos

Este documento describe los recorridos completos, sus efectos y las excepciones que deben manejarse.

## 1. Configuración inicial

```text
Crear propietario
-> configurar panadería y hogar
-> crear cuentas iniciales
-> configurar caja principal y caja menor
-> crear categorías
-> crear métodos de pago
-> crear roles y permisos
-> registrar saldos iniciales autorizados
-> crear catálogo básico
```

Los saldos iniciales generan movimientos de apertura auditados. No se insertan balances directos sin documento origen.

## 2. Apertura del periodo mensual de costos

```text
Inicia nuevo mes
-> sistema detecta periodo pendiente
-> administrador registra servicios del mes anterior
-> asigna porcentajes negocio/hogar
-> sistema consulta harina procesada el mes anterior
-> calcula tarifas sugeridas
-> administrador acepta o ingresa tarifas manuales
-> registra motivo si hubo cambio
-> abre periodo
-> producción queda habilitada
```

Excepciones:

- Si no hubo producción anterior, se exige tarifa manual inicial.
- Si los porcentajes no suman 100 %, el sistema no confirma la distribución.
- Si falta una tarifa obligatoria, se puede planificar producción pero no completarla.
- Una autorización temporal se registra y deja el periodo marcado como pendiente de regularización.

## 3. Compra, recepción y pago

```text
Registrar proveedor
-> crear compra
-> indicar contado o crédito
-> confirmar compra
-> generar cuenta por pagar si corresponde
-> recibir artículos total o parcialmente
-> generar entrada de inventario
-> actualizar costo promedio
-> registrar pagos
-> cerrar obligación cuando saldo = 0
```

Excepciones:

- Diferencia entre lo comprado y recibido: queda saldo pendiente de recepción.
- Recepción superior a lo comprado: requiere autorización.
- Devolución: genera salida de inventario y ajuste de la obligación.
- Transporte: se distribuye entre líneas mediante una regla visible antes de confirmar.

## 4. Creación y versionado de receta

```text
Crear masa base
-> crear versión borrador
-> indicar harina de referencia
-> agregar ingredientes
-> validar porcentajes y unidades
-> definir rendimiento y merma
-> relacionar productos compatibles
-> configurar peso de masa por unidad
-> revisar costo estimado
-> activar versión con fecha de vigencia
```

Una versión activa se duplica para crear la siguiente. Nunca se edita una fórmula histórica utilizada.

## 5. Pedido anticipado

```text
Seleccionar o crear cliente
-> agregar productos y paquetes
-> aplicar lista de precios
-> definir entrega
-> registrar anticipo opcional
-> reservar inventario disponible
-> calcular faltante por producir
-> confirmar pedido
-> mostrarlo en demanda pendiente
```

El anticipo genera entrada de dinero y una obligación de entrega, no una segunda venta.

## 6. Demanda pendiente y propuesta de producción

```text
Abrir demanda pendiente
-> agrupar pedidos por masa base
-> descontar inventario disponible y reservado
-> convertir productos faltantes a peso de masa
-> convertir masa a kilos de harina
-> seleccionar pedidos
-> generar propuesta de producción
-> ajustar kilos o productos planeados
-> guardar orden planificada
```

La propuesta es editable antes de iniciar. Las diferencias quedan visibles para evitar prometer más producto del que se planea obtener.

## 7. Producción basada en harina

```text
Abrir orden planificada o crear producción directa
-> seleccionar receta y versión
-> indicar kilos de harina
-> calcular ingredientes
-> verificar disponibilidad
-> resolver faltantes o solicitar autorización
-> elegir método de mano de obra
-> revisar gas, electricidad y costos estimados
-> iniciar producción
-> registrar novedades y consumo real
-> registrar masa obtenida
-> distribuir masa entre productos
-> registrar merma
-> revisar costos finales
-> completar producción
```

Al completar, en una transacción:

1. Se generan salidas de ingredientes.
2. Se generan entradas de productos.
3. Se asignan costos.
4. Se registran mermas.
5. Se actualiza la demanda de pedidos.
6. Se reservan productos para los pedidos relacionados.
7. Se registra auditoría.

Excepciones:

- Ingrediente insuficiente: advertencia y autorización de inventario negativo.
- Consumo diferente: novedad obligatoria cuando supera la tolerancia configurada.
- Masa no distribuida: debe declararse como remanente intermedio o merma.
- Productos que exceden la masa disponible: bloqueo hasta corregir pesos o registrar una novedad autorizada.
- Periodo mensual sin tarifa: bloqueo de finalización.

## 8. Armado de paquetes

```text
Seleccionar producto
-> elegir presentación de paquete
-> indicar cantidad
-> calcular unidades requeridas
-> mostrar existencias antes y después
-> confirmar
-> generar salida de unidades y entrada de paquetes
```

## 9. Desarmado automático propuesto

```text
Venta solicita unidades
-> unidades insuficientes
-> existen paquetes equivalentes
-> sistema calcula paquetes mínimos a desarmar
-> usuario acepta
-> convertir paquete a unidades
-> continuar venta
```

La conversión y la venta son operaciones relacionadas. Si la venta falla, no debe quedar una conversión parcial no deseada.

## 10. Venta directa de contado

```text
Abrir caja menor
-> agregar productos
-> seleccionar cliente opcional
-> aplicar precios
-> resolver paquetes e inventario
-> seleccionar medios de pago
-> confirmar venta
-> descontar inventario
-> registrar costo de venta
-> registrar ingreso y movimiento de caja
-> entregar comprobante interno
```

Si la caja menor no está abierta, una venta con efectivo se bloquea. Otros medios podrán seguir reglas configurables.

## 11. Venta a crédito

```text
Crear venta
-> seleccionar cliente obligatorio
-> indicar pago inicial opcional
-> definir vencimiento
-> confirmar
-> descontar inventario
-> registrar ingreso de venta
-> crear cuenta por cobrar por el saldo
```

Un cliente bloqueado o por encima del límite de crédito requiere autorización.

## 12. Entrega de pedido y conversión a venta

```text
Abrir pedido listo
-> verificar productos reservados
-> registrar entrega total o parcial
-> generar venta
-> aplicar anticipo existente
-> cobrar diferencia o crear cartera
-> liberar reservas no utilizadas
-> actualizar estado del pedido
```

## 13. Registro de abono de cliente

```text
Seleccionar cliente
-> consultar obligaciones abiertas
-> ingresar valor y cuenta receptora
-> distribuir pago
-> confirmar
-> aumentar caja o banco
-> disminuir cartera
-> actualizar estados
```

Un excedente no asignado debe devolverse o quedar como saldo a favor documentado.

## 14. Apertura y cierre de caja menor

### Apertura

```text
Seleccionar caja
-> declarar base inicial
-> identificar origen del dinero
-> generar traslado desde caja principal si corresponde
-> abrir sesión
```

### Cierre

```text
Consultar resumen del turno
-> revisar movimientos
-> declarar efectivo contado
-> calcular diferencia
-> justificar o autorizar diferencia
-> trasladar excedente a caja principal opcionalmente
-> cerrar sesión
```

Una sesión cerrada no acepta nuevos movimientos. Una reapertura requiere autorización y auditoría.

## 15. Gasto pagado o pendiente

```text
Elegir ámbito negocio u hogar
-> seleccionar categoría y tercero
-> ingresar valor y fecha
-> indicar pagado, parcial o pendiente
-> adjuntar comprobante opcional
-> confirmar
-> generar gasto y obligación
-> registrar salida de dinero cuando se pague
```

Un gasto productivo asignable alimenta el fondo de costos correspondiente; no se duplica al aplicarlo a productos.

## 16. Solicitud de fondos de un habitante

```text
Habitante crea solicitud
-> elige origen hogar o negocio
-> indica valor, motivo y fecha necesaria
-> propietario revisa
-> aprueba o rechaza
-> responsable paga
-> habitante confirma recepción
```

### Pago desde negocio

```text
Salida de caja/banco del negocio
-> gasto real de mano de obra
-> fondo de costo de mano de obra
-> entrada en cuenta del habitante
-> solicitud pagada
```

### Pago desde hogar

```text
Salida de cuenta compartida
-> entrada en cuenta personal controlada
-> transferencia interna del hogar
```

Si el dinero sale del patrimonio controlado, el responsable debe clasificarlo como gasto.

## 17. Presupuesto del hogar

```text
Crear presupuesto mensual
-> asignar montos por categoría
-> asignar habitante opcional
-> confirmar
-> registrar movimientos durante el mes
-> comparar ejecutado y disponible
-> mostrar alertas de consumo
```

Transferencias internas no consumen presupuesto salvo que una regla explícita las reclasifique como gasto.

## 18. Deuda o préstamo

```text
Registrar tercero y tipo
-> ingresar capital, interés y fechas
-> generar plan de cuotas
-> confirmar
-> registrar pagos parciales o anticipados
-> aplicar primero según regla configurada
-> actualizar capital, interés y estado
```

La regla predeterminada de aplicación será primero interés vencido y después capital, mostrando siempre el resultado antes de confirmar.

## 19. Devolución de venta

```text
Seleccionar venta original
-> seleccionar líneas y cantidades
-> declarar estado físico del producto
-> calcular devolución de dinero o saldo a favor
-> confirmar
-> generar documento de devolución
-> devolver inventario solo si es apto
-> revertir costo proporcional
-> ajustar caja o cartera
```

## 20. Reversión de documento

```text
Abrir documento confirmado
-> solicitar reversión
-> indicar motivo
-> validar dependencias posteriores
-> obtener autorización
-> generar documento inverso
-> conservar documento original
-> actualizar documentos relacionados
```

Si existen operaciones posteriores incompatibles, se bloquea la reversión hasta resolverlas en orden.

## 21. Cierre mensual de costos

```text
Finaliza mes
-> revisar facturas y pagos reales
-> totalizar kilos procesados
-> totalizar costos aplicados
-> calcular variaciones
-> revisar producciones pendientes
-> confirmar cierre
-> congelar tarifas y resultados
```

Reabrir un periodo requiere autorización. Los reportes identifican claramente periodos abiertos y cerrados.

## 22. Respaldo y restauración

### Respaldo

```text
Administrador solicita respaldo
-> sistema genera copia consistente
-> incluye base, adjuntos y manifiesto
-> calcula verificación de integridad
-> almacena historial
-> habilita descarga autorizada
```

### Restauración

```text
Administrador selecciona respaldo
-> sistema verifica integridad y compatibilidad
-> muestra impacto
-> exige nueva autenticación y confirmación
-> restaura en procedimiento controlado
-> registra auditoría
```

La restauración nunca se ejecuta silenciosamente desde una pantalla operativa.

## 23. Acceso desde Internet

```text
Usuario abre dominio HTTPS
-> inicia sesión
-> servidor valida estado, credenciales y permisos
-> crea sesión revocable
-> presenta interfaz según roles
-> registra accesos y operaciones sensibles
```

Los adjuntos permanecen privados. La aplicación entrega acceso temporal únicamente después de validar permisos.

## 24. Cliente Capacitor futuro

```text
Aplicación instalada
-> conecta por HTTPS a API versionada
-> autentica dispositivo
-> consume los mismos casos de uso
-> servidor aplica los mismos permisos
-> sesión puede revocarse
```

El cliente no accede directamente a MySQL y no almacena una copia autoritativa de inventario o finanzas.

## Consistencia entre módulos

| Acción                 | Inventario |          Finanzas |          Costos |             Pedidos |
| ---------------------- | ---------: | ----------------: | --------------: | ------------------: |
| Recibir compra         |         Sí |  Según obligación | Actualiza costo |                  No |
| Completar producción   |         Sí | No necesariamente |              Sí |                  Sí |
| Confirmar venta        |         Sí |                Sí |  Costo de venta |     Puede completar |
| Registrar abono        |         No |                Sí |              No | Puede reducir saldo |
| Armar/desarmar paquete |         Sí |                No |  Conserva costo |                  No |
| Pagar mano de obra     |         No |                Sí |  Alimenta fondo |                  No |
| Cerrar periodo         |         No |  Ajuste/variación |              Sí |                  No |
