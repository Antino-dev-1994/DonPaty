# Mapa de pantallas

Este documento define la arquitectura de información y las responsabilidades de cada pantalla. Los nombres de rutas son conceptuales y podrán ajustarse sin cambiar el comportamiento descrito.

## Principios de experiencia de usuario

- Diseño responsive con prioridad en tareas rápidas desde teléfono y tableta.
- La navegación y las acciones visibles dependen de permisos del servidor.
- Cada pantalla tiene una acción primaria claramente identificada.
- Los formularios extensos se dividen en pasos o secciones, sin ocultar el estado guardado.
- Los documentos muestran estado, responsable, fecha efectiva y trazabilidad.
- Las acciones irreversibles o excepcionales siempre solicitan confirmación.
- Los importes se muestran en COP y las cantidades en unidades legibles.
- Las alertas distinguen información, advertencia, bloqueo y autorización requerida.
- Los filtros relevantes se conservan al regresar a un listado.
- Las tablas importantes permiten búsqueda, filtros, orden y paginación.

## Estructura de navegación

```text
Inicio
Operación
  |-- Pedidos
  |-- Producción
  |-- Ventas
  `-- Caja
Inventario
  |-- Existencias
  |-- Movimientos
  |-- Ajustes
  `-- Paquetes
Catálogo
  |-- Artículos
  |-- Productos y presentaciones
  |-- Recetas
  |-- Precios
  |-- Clientes
  `-- Proveedores
Compras
  |-- Compras
  |-- Recepciones
  `-- Cuentas por pagar
Finanzas
  |-- Cuentas
  |-- Ingresos y gastos
  |-- Cuentas por cobrar
  |-- Cuentas por pagar
  `-- Movimientos
Hogar
  |-- Resumen
  |-- Movimientos
  |-- Solicitudes
  |-- Presupuestos
  |-- Deudas y préstamos
  `-- Metas de ahorro
Reportes
Administración
  |-- Personas y usuarios
  |-- Roles y permisos
  |-- Periodos de costos
  |-- Auditoría
  |-- Respaldos
  `-- Configuración
```

El menú se reduce según el rol. Un habitante no verá módulos operativos del negocio si no posee permisos laborales.

## Inicio y tablero

### Tablero del propietario

Muestra:

- Ventas, cobros, gastos y flujo neto del día.
- Resultado económico estimado del día.
- Indicador de ganancia, pérdida o equilibrio.
- Caja principal, caja menor y efectivo total.
- Cuentas por cobrar y pagar vencidas.
- Pedidos próximos, vencidos y pendientes de producción.
- Inventarios bajos o negativos.
- Producciones recientes, rendimiento y merma.
- Ejecución presupuestal del hogar.
- Solicitudes pendientes de aprobación.
- Alertas de configuración mensual y respaldos.

Acciones rápidas:

- Nueva venta.
- Nuevo pedido.
- Planificar producción.
- Registrar compra.
- Registrar gasto.
- Abrir o cerrar caja.
- Atender solicitud.

### Tablero de producción

- Producciones del día.
- Pedidos que deben producirse en las próximas 24 horas.
- Harina y masa requeridas.
- Faltantes de ingredientes.
- Producciones en curso.
- Acceso rápido a registrar resultados y novedades.

### Tablero de ventas y caja

- Estado de caja menor.
- Ventas del turno.
- Pedidos listos para entregar.
- Clientes con saldo pendiente.
- Acceso a venta, abono y cierre de caja.

### Tablero de habitante

- Saldo de cuentas personales autorizadas.
- Movimientos recientes.
- Solicitudes y su estado.
- Presupuesto personal o compartido permitido.
- Cuotas próximas y metas de ahorro autorizadas.

## Personas, usuarios y permisos

### Listado de personas

- Buscar por nombre, documento, teléfono o clasificación.
- Filtrar propietarios, habitantes, empleados, clientes y proveedores.
- Crear, editar datos de contacto, activar o desactivar.
- Consultar relaciones financieras permitidas.

### Detalle de persona

Pestañas:

- Información general.
- Clasificaciones.
- Usuario y accesos.
- Movimientos autorizados.
- Solicitudes.
- Ventas o pagos relacionados según el rol.
- Historial de cambios.

### Usuarios

- Crear acceso para una persona.
- Asignar roles.
- Activar o bloquear.
- Revocar sesiones.
- Restablecer acceso mediante flujo seguro.

### Roles y permisos

- Crear roles reutilizables.
- Seleccionar permisos por módulo y acción.
- Ver usuarios afectados antes de modificar un rol.

## Catálogo e inventario

### Artículos

- Listado con tipo, unidad base, existencia, costo promedio y estado.
- Formulario con datos generales, límites y configuración de inventario.
- Historial de movimientos y costos desde el detalle.

### Productos y presentaciones

- Producto principal.
- Presentaciones por unidad o paquete.
- SKU y código de barras opcional.
- Composición de paquetes.
- Existencias y precios por presentación.
- Recetas compatibles.

### Existencias

Columnas principales:

- Artículo o presentación.
- Existencia física.
- Reservada.
- Disponible.
- Existencia mínima.
- Costo promedio.
- Valor del inventario.

Filtros:

- Tipo.
- Bajo mínimo.
- Negativo.
- Activo o inactivo.

### Movimientos de inventario

- Línea de tiempo por artículo.
- Tipo, documento origen, entrada, salida, costo y saldo.
- Enlace al documento que originó el movimiento.
- Exportación del rango consultado.

### Ajuste de inventario

- Conteo físico.
- Existencia esperada.
- Diferencia.
- Motivo.
- Adjunto opcional.
- Autorización cuando aplique.

### Armado y desarmado de paquetes

- Seleccionar producto y presentación.
- Mostrar equivalencia y existencias antes/después.
- Proponer cantidad mínima para atender una venta.
- Confirmar conversión con trazabilidad.

## Recetas

### Listado de recetas

- Masa base.
- Versión activa.
- Fecha de vigencia.
- Costo estimado de referencia.
- Rendimiento y estado.

### Editor de receta

Secciones:

1. Información de la masa.
2. Harina de referencia.
3. Ingredientes y porcentajes panaderos.
4. Rendimiento y merma.
5. Productos compatibles.
6. Componentes de acabado y empaques.
7. Instrucciones.
8. Vista previa de costos.

### Versiones

- Comparar versiones.
- Duplicar una versión para editarla como borrador.
- Activar con fecha de vigencia.
- Marcar una versión como obsoleta sin perder historial.

## Periodos de costos

### Listado mensual

- Mes.
- Estado.
- Kilogramos procesados.
- Tarifas de gas y electricidad.
- Costos aplicados.
- Variaciones.

### Apertura mensual

- Registrar factura del periodo anterior.
- Indicar valor total.
- Distribuir porcentajes entre negocio y hogar.
- Ver kilogramos procesados del periodo anterior.
- Revisar tarifa sugerida.
- Ingresar tarifa manual y motivo cuando corresponda.
- Abrir el periodo para producción.

### Cierre mensual

- Comparar costos reales y aplicados.
- Mostrar variaciones.
- Validar documentos pendientes.
- Confirmar cierre o solicitar correcciones.

## Producción

### Demanda pendiente

- Pedidos próximos y atrasados.
- Productos faltantes.
- Inventario utilizable.
- Masa y harina necesarias.
- Agrupación por receta base.
- Acción para crear propuesta de producción.

### Nueva producción

Pasos:

1. Receta, versión y fecha.
2. Pedidos que se atenderán.
3. Kilogramos de harina.
4. Ingredientes calculados y disponibilidad.
5. Productos planeados.
6. Método de mano de obra.
7. Costos estimados.
8. Confirmación de planeación.

### Producción en curso

- Resumen de fórmula.
- Consumo esperado.
- Registro de novedades.
- Pausa o reanudación informativa.
- Registro de horas reales.
- Productos obtenidos progresivamente como borrador.

### Finalizar producción

- Consumos calculados y reales.
- Masa esperada y obtenida.
- Distribución final por productos.
- Merma.
- Costos estimados y reales.
- Pedidos atendidos.
- Advertencias y autorizaciones.
- Confirmación final.

### Detalle e historial

- Documento completo no editable.
- Movimientos de inventario.
- Distribución de costos.
- Novedades.
- Pedidos relacionados.
- Línea de auditoría.
- Acción de reversión según permiso.

## Pedidos

### Listado

- Cliente.
- Fecha de entrega.
- Estado.
- Prioridad.
- Total, anticipo y saldo.
- Estado de producción.

Vistas rápidas:

- Próximas 24 horas.
- Atrasados.
- Pendientes de producción.
- Listos para entregar.
- Entregados.

### Crear o editar pedido

- Cliente.
- Productos y cantidades.
- Lista y precios acordados.
- Fecha y hora de entrega.
- Reserva de inventario.
- Anticipo.
- Notas y prioridad.

### Detalle del pedido

- Progreso por línea.
- Producciones relacionadas.
- Inventario reservado.
- Anticipos y saldo.
- Entregas parciales.
- Conversión a venta.
- Historial de estados.

## Ventas y cartera

### Punto de venta

- Búsqueda rápida por producto, SKU o código.
- Carrito con unidades y paquetes.
- Cliente opcional.
- Lista de precios y precio personalizado.
- Advertencias por precio mínimo e inventario.
- Propuesta de armar o desarmar paquetes.
- Formas de pago múltiples.
- Venta a crédito.
- Confirmación y comprobante interno.

### Detalle de venta

- Líneas, precios y descuentos.
- Costo de venta y margen según permiso.
- Pagos y saldo.
- Movimientos de inventario.
- Pedido relacionado.
- Devoluciones y reversión.

### Cuentas por cobrar

- Cliente.
- Documento.
- Emisión y vencimiento.
- Saldo.
- Estado.
- Registro de abonos y estado de cuenta.

### Registrar abono

- Cliente y obligaciones abiertas.
- Valor recibido.
- Cuenta de destino.
- Distribución automática o manual.
- Comprobante opcional.

## Compras y cuentas por pagar

### Compras

- Listado por proveedor, fecha, estado y saldo.
- Creación de compra con líneas, impuestos informativos opcionales, transporte y vencimiento.
- Adjuntos opcionales.

### Recepción

- Seleccionar compra.
- Registrar cantidades recibidas.
- Identificar diferencias.
- Confirmar entrada de inventario.

### Cuentas por pagar

- Proveedor, documento, vencimiento y saldo.
- Registrar pagos parciales.
- Ver estado de cuenta.

## Caja y finanzas

### Caja menor

- Estado actual.
- Apertura con base inicial.
- Movimientos del turno.
- Ingresos y egresos.
- Saldo esperado.
- Acción de cierre.

### Cierre de caja

- Resumen por medio de pago.
- Saldo esperado.
- Conteo declarado.
- Diferencia.
- Motivo y autorización si aplica.
- Traslado opcional a caja principal.

### Caja principal y cuentas

- Saldos por cuenta.
- Efectivo total.
- Transferencias entre cuentas.
- Historial y conciliación.

### Ingresos y gastos

- Registro pagado o pendiente.
- Ámbito negocio u hogar.
- Categoría.
- Cuenta y tercero.
- Fecha efectiva y vencimiento.
- Pago parcial.
- Comprobante opcional.

## Hogar

### Resumen

- Ingresos y gastos del mes.
- Presupuesto consumido.
- Deudas y cuotas próximas.
- Solicitudes pendientes.
- Metas de ahorro.

### Movimientos

- Vista personal para habitantes.
- Vista consolidada para propietarios autorizados.
- Filtros por persona, cuenta, categoría y fecha.

### Solicitudes

- Crear solicitud indicando origen hogar o negocio, valor, motivo y fecha requerida.
- Consultar estado y comentarios.
- Aprobar, rechazar o pagar según permiso.
- Ver el movimiento financiero generado.

### Presupuestos

- Presupuesto por mes, categoría y habitante opcional.
- Comparación planeado, ejecutado y disponible.
- Alertas por porcentaje consumido.

### Deudas y préstamos

- Registro de capital, interés, cuotas y vencimientos.
- Calendario de pagos.
- Abonos parciales o anticipados.
- Estado de deuda o préstamo.

### Metas de ahorro

- Valor objetivo.
- Fecha objetivo.
- Cuenta relacionada.
- Aportes y progreso.

## Reportes

### Reportes operativos

- Existencias y valorización.
- Kardex por artículo.
- Consumo de ingredientes.
- Producción, rendimiento y merma.
- Pedidos y cumplimiento.
- Ventas por producto, cliente y vendedor.

### Reportes financieros

- Flujo de caja.
- Resultado diario y mensual.
- Margen por producto.
- Costos por receta y producción.
- Costos reales frente a aplicados.
- Cuentas por cobrar y pagar.
- Gastos por categoría y ámbito.
- Presupuesto del hogar.

Todos los reportes permiten rango de fechas, filtros relevantes y exportación posterior a formatos comunes.

## Administración

### Auditoría

- Filtros por usuario, acción, documento y fecha.
- Visualización de excepciones y autorizaciones.
- Enlace al documento origen.

### Respaldos

- Crear respaldo.
- Consultar estado e integridad.
- Descargar.
- Ver instrucciones de restauración.
- Restaurar únicamente mediante flujo administrativo protegido.

### Configuración

- Datos de la panadería y hogar.
- Moneda y zona horaria.
- Consecutivos.
- Límites de autorización.
- Anticipación de pedidos, inicialmente 24 horas.
- Categorías financieras.
- Métodos de pago.
- Configuración de adjuntos.

## Estados visuales compartidos

- `Borrador`: gris.
- `Pendiente`: amarillo.
- `En proceso`: azul.
- `Completado/Pagado`: verde.
- `Vencido/Bloqueado`: rojo.
- `Revertido/Cancelado`: neutro con referencia al documento correctivo.

El color nunca será el único indicador; todos los estados incluirán texto e iconografía accesible.
