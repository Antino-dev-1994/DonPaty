# Backlog funcional

El backlog está ordenado por dependencias. Una fase no se considera terminada hasta cumplir sus criterios de aceptación y pruebas.

## Épica 0: Fundamentos técnicos

### Historias

- Como equipo, necesitamos una estructura modular para mantener límites claros entre dominios.
- Como usuario, necesito una interfaz responsive accesible desde navegador.
- Como administrador, necesito que las operaciones críticas sean auditables.

### Criterios de aceptación

- La aplicación usa Laravel, Inertia, Vue 3 y TypeScript.
- Los módulos no acceden directamente a detalles internos de otros módulos.
- Los casos de uso pueden invocarse desde Inertia y posteriormente desde una API.
- Existe configuración independiente para SQLite y MySQL.
- Se manejan correctamente COP, UTC y `America/Bogota`.
- Las pruebas pueden ejecutarse de forma repetible.

## Épica 1: Personas, usuarios y permisos

### Historias

- Como propietario, quiero crear personas y clasificarlas como habitantes, empleados o clientes.
- Como administrador, quiero conceder roles y permisos.
- Como usuario, quiero iniciar y cerrar sesión de forma segura.
- Como propietario, quiero revocar sesiones de un dispositivo.

### Criterios de aceptación

- Una persona puede poseer varias clasificaciones.
- Una persona puede existir sin usuario.
- Las rutas y acciones se autorizan del lado del servidor.
- Un usuario bloqueado no puede iniciar una nueva sesión.
- Las operaciones excepcionales conservan solicitante, autorizador y motivo.

## Épica 2: Catálogo, unidades y presentaciones

### Historias

- Como administrador, quiero crear materias primas, empaques y productos.
- Como administrador, quiero definir unidades y conversiones.
- Como vendedor, quiero vender un producto por unidad o paquete.

### Criterios de aceptación

- Cada artículo tiene tipo, unidad base, código y estado.
- No se permiten conversiones entre magnitudes incompatibles.
- Un paquete solo puede contener presentaciones del mismo producto.
- Los productos inactivos conservan su historial y no aparecen en nuevas operaciones.

## Épica 3: Inventario

### Historias

- Como administrador, quiero conocer existencias físicas, reservadas y disponibles.
- Como encargado, quiero ajustar inventario con una justificación.
- Como usuario autorizado, quiero continuar una operación con inventario negativo.
- Como vendedor, quiero armar o desarmar paquetes.

### Criterios de aceptación

- Toda variación de inventario tiene un movimiento origen.
- El saldo coincide con la suma de movimientos.
- El costo promedio se actualiza después de entradas valorizadas.
- El inventario negativo muestra advertencia y exige autorización.
- Armar y desarmar paquetes conserva cantidad equivalente y costo total.
- Las reservas reducen disponibilidad sin disminuir existencia física.

## Épica 4: Proveedores y compras

### Historias

- Como administrador, quiero registrar proveedores y compras.
- Como encargado, quiero recibir artículos total o parcialmente.
- Como responsable financiero, quiero registrar obligaciones y pagos parciales.

### Criterios de aceptación

- Compra, recepción y pago pueden ocurrir en fechas diferentes.
- Una recepción confirmada genera entradas de inventario.
- Una compra a crédito genera una cuenta por pagar.
- Los pagos reducen la obligación sin duplicar el costo de compra.
- Se puede adjuntar un comprobante opcional.

## Épica 5: Recetas versionadas

### Historias

- Como responsable de producción, quiero crear una receta de masa base.
- Como responsable, quiero publicar una nueva versión sin modificar el historial.
- Como responsable, quiero relacionar la masa con varios productos.

### Criterios de aceptación

- Una versión define harina base, ingredientes, rendimiento y merma.
- Los porcentajes panaderos y cantidades escaladas son consistentes.
- Solo una versión aplicable está activa por fecha.
- Cada producto compatible define gramos de masa y factor de costo.
- Las producciones anteriores conservan su versión original.

## Épica 6: Periodos y tarifas de costos

### Historias

- Como administrador, quiero registrar mensualmente gas y electricidad.
- Como administrador, quiero distribuir manualmente un servicio entre negocio y hogar.
- Como administrador, quiero usar la tarifa sugerida o reemplazarla manualmente.

### Criterios de aceptación

- La tarifa sugerida usa valor productivo y harina procesada del periodo anterior.
- Si no existe historial, se solicita tarifa inicial manual.
- Modificar la tarifa exige motivo.
- No se puede completar producción sin tarifa efectiva del mes.
- El periodo puede cerrarse con comparación entre costo real y aplicado.

## Épica 7: Producción

### Historias

- Como encargado, quiero planificar una producción indicando kilos de harina.
- Como encargado, quiero registrar diferencias y novedades.
- Como encargado, quiero distribuir la masa entre varios productos.
- Como administrador, quiero comparar costos y rendimientos esperados con reales.

### Criterios de aceptación

- Los ingredientes se escalan correctamente.
- Se muestran faltantes antes de iniciar.
- La mano de obra permite tarifa por kilogramo, horas reales o valor manual autorizado.
- Completar descuenta consumos e ingresa productos en una transacción.
- Los costos se distribuyen sin perder valor total.
- Se registran masa esperada, masa real y merma.
- Una producción completada solo puede corregirse mediante reversión.

## Épica 8: Clientes y pedidos

### Historias

- Como vendedor, quiero registrar pedidos con fecha de entrega y anticipo.
- Como productor, quiero conocer lo que debo producir en las próximas 24 horas.
- Como productor, quiero consolidar varios pedidos en una producción.

### Criterios de aceptación

- El tablero diferencia pedidos próximos, atrasados y listos.
- La demanda descuenta inventario disponible y reservas existentes.
- La sugerencia calcula masa y harina requeridas.
- Una producción puede atender varios pedidos.
- Se permiten cumplimientos y entregas parciales.

## Épica 9: Precios y ventas

### Historias

- Como administrador, quiero mantener listas minoristas, mayoristas y promocionales.
- Como vendedor, quiero registrar ventas individuales y precios personalizados.
- Como vendedor, quiero realizar ventas de contado o crédito.

### Criterios de aceptación

- El cliente obtiene automáticamente su lista vigente.
- El precio aplicado conserva referencia al precio original.
- Un precio inferior al mínimo exige autorización.
- Confirmar una venta actualiza inventario, costo, ingreso, caja o cartera.
- Una venta puede originarse desde un pedido.
- Una venta puede recibir varios pagos y quedar parcialmente pagada.

## Épica 10: Caja y pagos

### Historias

- Como cajero, quiero abrir caja menor con una base.
- Como cajero, quiero registrar cobros, gastos y traslados.
- Como cajero, quiero cerrar caja y declarar el efectivo contado.
- Como propietario, quiero ver caja principal, caja menor y efectivo total.

### Criterios de aceptación

- Solo existe una sesión abierta por caja operativa.
- El saldo esperado se deriva de movimientos confirmados.
- El cierre calcula faltante o sobrante.
- Los traslados entre cajas no afectan ingresos o gastos.
- Una diferencia relevante exige motivo y autorización.

## Épica 11: Finanzas del negocio

### Historias

- Como propietario, quiero registrar ingresos, gastos y obligaciones.
- Como propietario, quiero diferenciar flujo de caja y rentabilidad.
- Como propietario, quiero consultar cuentas por cobrar y pagar.

### Criterios de aceptación

- Toda operación financiera genera un asiento balanceado.
- Los pagos parciales actualizan correctamente saldos.
- Comprar inventario no se muestra como gasto operativo completo del día.
- Los costos reales y aplicados no se duplican.
- Se pueden filtrar movimientos por cuenta, categoría, persona y fecha.

## Épica 12: Hogar, solicitudes y presupuestos

### Historias

- Como habitante, quiero consultar mis movimientos.
- Como habitante, quiero solicitar fondos al hogar o negocio.
- Como propietario, quiero aprobar, rechazar y pagar solicitudes.
- Como hogar, queremos crear y comparar presupuestos mensuales.

### Criterios de aceptación

- Un habitante consulta únicamente la información autorizada.
- Las solicitudes mantienen historial de estado.
- Un pago desde el negocio se clasifica como mano de obra y entrada del habitante.
- Las transferencias internas del hogar no inflan gastos consolidados.
- El presupuesto muestra disponible, ejecutado y porcentaje consumido.

## Épica 13: Deudas, préstamos y ahorro

### Historias

- Como propietario, quiero registrar deudas y préstamos.
- Como usuario, quiero controlar cuotas, intereses y vencimientos.
- Como hogar, queremos definir metas de ahorro.

### Criterios de aceptación

- Capital e intereses se registran por separado.
- Se admiten pagos parciales y anticipados.
- El sistema muestra cuotas vencidas y próximas.
- Una meta refleja valor objetivo, ahorrado y pendiente.

## Épica 14: Tableros y reportes

### Historias

- Como propietario, quiero saber si el día produjo ganancia o pérdida.
- Como productor, quiero comparar rendimiento y merma.
- Como propietario, quiero conocer el estado mensual del negocio y hogar.

### Criterios de aceptación

- El tablero diario separa flujo de efectivo y resultado económico.
- Los meses abiertos muestran rentabilidad provisional.
- Los reportes permiten filtrar por rango de fechas.
- Las cifras de ventas, inventario, cartera y contabilidad son conciliables con sus movimientos origen.
- Se muestran pedidos próximos, inventarios bajos y obligaciones vencidas.

## Épica 15: Adjuntos, auditoría y respaldos

### Historias

- Como usuario, quiero adjuntar comprobantes opcionales.
- Como propietario, quiero revisar operaciones sensibles.
- Como administrador, quiero descargar y verificar respaldos.

### Criterios de aceptación

- Los adjuntos no son públicos.
- La auditoría no expone contraseñas ni secretos.
- Los respaldos incluyen base de datos, adjuntos y manifiesto.
- El sistema verifica la integridad del respaldo.
- Existe un procedimiento documentado y probado de restauración.

## Épica 16: Preparación para Internet, MySQL y Capacitor

### Historias

- Como usuario, quiero acceder al sistema de forma segura desde Internet.
- Como equipo, queremos migrar de SQLite a MySQL sin cambiar el dominio.
- Como usuario futuro, quiero instalar un cliente Capacitor conectado al mismo backend.

### Criterios de aceptación

- Producción usa HTTPS, MySQL y almacenamiento persistente.
- Las migraciones y pruebas críticas pasan contra MySQL.
- Las sesiones pueden revocarse por dispositivo.
- La API móvil está versionada y aplica los mismos permisos.
- El cliente Capacitor no necesita acceso directo a la base de datos.
- El modo offline no se promete ni se simula.

## Orden de entrega recomendado

```text
Fundamentos
-> Personas y permisos
-> Catálogo
-> Inventario
-> Compras
-> Recetas
-> Costos mensuales
-> Producción
-> Pedidos
-> Ventas y precios
-> Caja y finanzas
-> Hogar
-> Deudas y préstamos
-> Reportes
-> Auditoría y respaldos
-> Despliegue y Capacitor
```
