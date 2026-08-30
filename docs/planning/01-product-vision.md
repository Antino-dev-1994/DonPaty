# Visión, alcance y decisiones

## Objetivo

Construir un sistema completo para administrar una panadería y las finanzas del hogar desde una sola aplicación, manteniendo ambos ámbitos separados contablemente pero permitiendo movimientos controlados entre ellos.

El sistema debe permitir conocer inventarios, costos, producción, ventas, obligaciones, flujo de caja, rentabilidad, presupuestos y situación financiera con datos auditables.

## Usuarios

- Propietario.
- Pareja del propietario.
- Habitantes del hogar.
- Empleados de la panadería.
- Usuarios de consulta.

Una persona puede cumplir simultáneamente varios roles, por ejemplo habitante y empleado.

## Decisiones confirmadas

### Organización

- Existirá una sola sede.
- Habrá múltiples usuarios con permisos configurables.
- Una persona puede existir sin tener credenciales de acceso.
- Los habitantes podrán consultar sus movimientos y solicitar fondos.
- Los propietarios podrán consultar el consolidado del negocio y del hogar.

### Inventario

- Se manejarán materias primas, insumos, empaques, productos intermedios, productos terminados y artículos de reventa.
- No se requieren lotes ni fechas de vencimiento en la primera versión.
- Se permitirán inventarios negativos con advertencia, autorización y motivo auditado.
- Los productos podrán venderse por unidad o paquete.
- Los paquetes se arman a partir de unidades y podrán desarmarse cuando falten unidades del mismo producto.

### Recetas y producción

- Una receta representa una masa base versionada.
- La producción se escala a partir de la cantidad requerida de harina en kilogramos.
- Una misma masa se distribuye entre varios productos terminados.
- Se utilizará el consumo calculado por receta y se registrarán las novedades o diferencias reales.
- La producción afectará inventario mediante salidas de ingredientes y entradas de productos.
- Se medirán masa esperada, masa obtenida, productos esperados, productos obtenidos y merma.

### Costos

- El costo productivo incluirá materias primas, empaques, mano de obra, electricidad, gas y otros costos directos.
- La mano de obra podrá calcularse por tarifa estándar por kilogramo de harina o mediante horas reales, elegible en cada producción.
- El primer día de cada mes se registrarán los servicios pagados correspondientes al periodo anterior.
- El porcentaje asignado al negocio y al hogar se indicará manualmente.
- La tarifa productiva sugerida se calculará con el valor del periodo anterior y los kilogramos procesados en ese periodo.
- La tarifa podrá reemplazarse manualmente, dejando motivo y responsable.
- Debe existir una tarifa mensual efectiva antes de completar producciones del nuevo mes.

### Ventas y pedidos

- Las ventas se registrarán individualmente.
- Se permitirán ventas a crédito y abonos parciales.
- Habrá precios minoristas, mayoristas, promocionales y personalizados.
- Los pedidos anticipados generarán demanda pendiente de producción.
- El tablero recordará los pedidos con una anticipación predeterminada de 24 horas.
- No habrá notificaciones externas en la primera versión.

### Caja y finanzas

- Existirá una caja principal y una caja menor operativa.
- El efectivo total será la suma de ambas cajas.
- La caja menor tendrá apertura, movimientos, conteo y cierre.
- Se registrarán compras y gastos pagados, parcialmente pagados y pendientes.
- Se diferenciará flujo de efectivo de rentabilidad económica.
- Las transferencias entre cuentas no se tratarán como ingresos o gastos ficticios.

### Hogar

- Se manejarán ingresos, gastos, presupuestos, cuentas, deudas, préstamos, cuotas, intereses, vencimientos y metas de ahorro.
- Los habitantes podrán solicitar fondos al hogar o al negocio.
- Si una solicitud es pagada por el negocio, se registrará como gasto de mano de obra y como pago al habitante.
- Si una transferencia permanece dentro de cuentas controladas por el hogar, será un movimiento interno y no un gasto consolidado.
- Los comprobantes serán opcionales.

### Tecnología e infraestructura

- Monolito modular Laravel.
- Inertia, Vue 3 y TypeScript.
- Arquitectura limpia, SOLID y componentes con responsabilidad única.
- SQLite para desarrollo inicial.
- MySQL para el primer despliegue real multiusuario.
- Acceso desde Internet mediante HTTPS.
- Dominio propio y almacenamiento persistente de adjuntos.
- Interfaz responsive para computador, tableta y teléfono.
- Empaquetado instalable futuro con Capacitor.
- Capacitor consumirá el mismo backend Laravel mediante una API versionada.
- El funcionamiento offline no forma parte de la primera versión.

## Indicadores principales

### Diarios

- Ventas.
- Dinero recibido.
- Abonos.
- Gastos y pagos.
- Costo de productos vendidos.
- Utilidad bruta.
- Resultado operativo estimado.
- Flujo neto de efectivo.
- Ganancia, pérdida o equilibrio.
- Caja menor y efectivo total.
- Pedidos próximos o atrasados.
- Producciones pendientes.
- Inventario insuficiente.
- Cartera vencida.

### Mensuales

- Ventas y margen por producto.
- Rentabilidad por receta y producción.
- Consumo de harina.
- Rendimiento y merma.
- Costos reales frente a costos aplicados.
- Gastos del negocio y del hogar.
- Presupuesto frente a ejecución.
- Cuentas por cobrar y pagar.
- Deudas y préstamos.
- Evolución patrimonial.

## Fuera del alcance de la primera versión

- Facturación electrónica DIAN.
- Varias sedes.
- Lotes y vencimientos.
- Nómina formal.
- Contabilidad tributaria certificada.
- Aplicación móvil nativa independiente.
- Sincronización offline.
- WhatsApp, correo y notificaciones push.

La arquitectura deberá permitir agregar facturación electrónica, integraciones externas y un cliente Capacitor sin reescribir el dominio.
