# Plan de implementación

## Enfoque de entrega

La implementación se realizará por entregas verticales. Cada entrega debe dejar un flujo utilizable y verificable, aunque todavía no exista el resto del sistema.

No se iniciará un módulo dependiente hasta que sus conceptos base estén estables. Sin embargo, no se construirá toda la infraestructura por anticipado: cada abstracción se agregará cuando una entrega real la necesite.

## Reglas de ejecución

- Una sola entrega activa a la vez salvo tareas totalmente independientes.
- Cambios pequeños y revisables.
- Migraciones hacia adelante; no editar migraciones ya utilizadas con datos reales.
- Actualizar documentación cuando cambie una regla acordada.
- Validación manual con el usuario al cerrar cada entrega funcional.
- Ejecutar solo las pruebas básicas definidas para el riesgo de la entrega.
- No mezclar refactorizaciones no relacionadas con una historia funcional.

## Entrega 0: Inicialización técnica

### Objetivo

Crear la base ejecutable sin implementar todavía reglas operativas.

### Incluye

- Proyecto Laravel.
- Inertia, Vue 3 y TypeScript.
- Configuración SQLite de desarrollo.
- Estructura de módulos.
- Layout principal y navegación vacía según permisos.
- Configuración de COP, UTC y `America/Bogota`.
- Manejo de errores e identificador de correlación.
- Convenciones de código, formato y tipos.
- Base para pruebas esenciales.

### Termina cuando

- La aplicación instala y arranca desde cero.
- Se pueden ejecutar migraciones vacías.
- La página inicial carga mediante Inertia.
- PHP y TypeScript pasan sus comprobaciones básicas.

## Entrega 1: Identidad, personas y permisos

### Objetivo

Permitir acceso seguro y preparar los actores del negocio y hogar.

### Incluye

- Personas y clasificaciones.
- Usuarios.
- Roles y permisos iniciales.
- Inicio y cierre de sesión.
- Bloqueo de usuarios.
- Sesiones revocables.
- Policies y permisos atómicos.
- Solicitudes de autorización reutilizables.
- Auditoría mínima de accesos y cambios sensibles.

### Demostración de cierre

- Crear propietario, administrador, productor, cajero y habitante.
- Verificar que cada uno solo vea y ejecute lo permitido.
- Bloquear un usuario y revocar su sesión.

## Entrega 2: Catálogo e inventario base

### Objetivo

Registrar artículos, productos, presentaciones y existencias auditables.

### Incluye

- Unidades y conversiones.
- Artículos y tipos.
- Presentaciones por unidad y paquete.
- Composición de paquetes.
- Libro de inventario.
- Saldos físicos, reservados y disponibles.
- Ajustes con motivo.
- Inventario negativo autorizado.
- Armado y desarmado manual de paquetes.
- Carga de inventario inicial.

### Demostración de cierre

- Crear harina, empaque y dos productos.
- Registrar inventario inicial.
- Armar y desarmar un paquete conservando costo.
- Ejecutar un ajuste e identificarlo en el kardex.

## Entrega 3: Proveedores y compras

### Objetivo

Ingresar inventario real mediante compras y controlar obligaciones.

### Incluye

- Proveedores.
- Compras pagadas o pendientes.
- Recepciones parciales.
- Costos adicionales.
- Entradas y costo promedio.
- Cuentas por pagar y pagos parciales.
- Devolución básica al proveedor.
- Comprobante opcional preparado, aunque el gestor general de archivos puede completarse después.

### Demostración de cierre

- Registrar una compra a crédito.
- Recibirla parcialmente.
- Comprobar inventario y costo promedio.
- Abonar la obligación y consultar su saldo.

## Entrega 4: Recetas y periodos de costos

### Objetivo

Definir fórmulas versionadas y las tarifas necesarias para producir.

### Incluye

- Recetas de masa base.
- Versiones, ingredientes y porcentajes panaderos.
- Productos compatibles y peso de masa.
- Rendimiento y merma esperada.
- Previsualización de escalado.
- Periodos mensuales.
- Gas y electricidad.
- Distribución manual hogar/negocio.
- Tarifa sugerida y manual.
- Fondos de costo básicos.

### Demostración de cierre

- Crear y activar una receta.
- Escalarla para una cantidad decimal de harina.
- Abrir un periodo con tarifa calculada y otro caso manual.
- Confirmar que una versión anterior permanece intacta.

## Entrega 5: Producción completa

### Objetivo

Convertir ingredientes en varios productos con costos y trazabilidad.

### Incluye

- Planeación de producción.
- Disponibilidad de ingredientes.
- Método de mano de obra por kilo, horas o manual autorizado.
- Inicio y registro de novedades.
- Consumos calculados y reales.
- Masa esperada y obtenida.
- Distribución entre productos.
- Merma.
- Costos aplicados.
- Movimientos de inventario atómicos.
- Reversión controlada.

### Demostración de cierre

- Producir una masa con dos productos resultantes.
- Ver salidas, entradas, costos y merma.
- Mostrar advertencia y autorización ante un ingrediente insuficiente.

## Entrega 6: Clientes, precios y pedidos

### Objetivo

Capturar demanda futura y convertirla en producción sugerida.

### Incluye

- Clientes.
- Listas minorista, mayorista y promocional.
- Precios mínimos y personalizados.
- Pedidos, anticipos y fechas de entrega.
- Reservas de inventario.
- Vista de próximas 24 horas.
- Demanda pendiente agrupada por masa.
- Cálculo de masa y harina necesarias.
- Vinculación entre pedido y producción.

### Demostración de cierre

- Registrar dos pedidos para la misma masa.
- Mostrar inventario disponible y faltante.
- Generar una propuesta consolidada de producción.

## Entrega 7: Ventas, cartera y caja menor

### Objetivo

Completar el ciclo comercial y controlar el dinero diario.

### Incluye

- Punto de venta.
- Venta directa y desde pedido.
- Unidad o paquete.
- Conversión de paquetes dentro de venta.
- Precio personalizado y autorización bajo mínimo.
- Venta de contado o crédito.
- Pagos múltiples y abonos.
- Cuentas por cobrar.
- Apertura y cierre de caja menor.
- Traslados con caja principal.
- Devolución básica.

### Demostración de cierre

- Abrir caja.
- Entregar un pedido y aplicar su anticipo.
- Registrar una venta de contado y otra a crédito.
- Registrar un abono.
- Cerrar caja mostrando saldo esperado y contado.

## Entrega 8: Finanzas del negocio

### Objetivo

Consolidar ingresos, gastos, cuentas y resultado administrativo.

### Incluye

- Plan de cuentas interno.
- Asientos balanceados.
- Ingresos y gastos pagados o pendientes.
- Cuentas financieras.
- Pagos parciales.
- Flujo de caja.
- Conciliación de costos reales y aplicados.
- Cierre mensual de costos.
- Cuentas por cobrar y pagar consolidadas.

### Demostración de cierre

- Consultar trazabilidad financiera de compra, producción y venta.
- Registrar un gasto pendiente y pagarlo parcialmente.
- Cerrar un periodo y mostrar su variación.

## Entrega 9: Hogar

### Objetivo

Controlar finanzas familiares sin mezclarlas incorrectamente con el negocio.

### Incluye

- Cuentas y movimientos del hogar.
- Vista propia por habitante.
- Solicitudes al hogar o negocio.
- Pago desde negocio como mano de obra.
- Presupuestos mensuales.
- Deudas, préstamos y cuotas.
- Metas de ahorro.

### Demostración de cierre

- Un habitante crea una solicitud.
- El propietario la aprueba y paga desde el negocio.
- Se observa el gasto laboral y la entrada del habitante sin duplicación.
- Se compara un presupuesto con gastos reales.

## Entrega 10: Tableros y reportes

### Objetivo

Convertir los movimientos en información útil para decidir.

### Incluye

- Tableros por rol.
- Resultado diario y mensual.
- Flujo de caja separado de rentabilidad.
- Margen por producto.
- Rendimiento, merma y costo por producción.
- Inventario valorizado.
- Cartera y obligaciones.
- Presupuesto del hogar.
- Periodos abiertos marcados como provisionales.

### Demostración de cierre

- Rastrear cada cifra principal hasta sus documentos origen.
- Comparar un día con ganancia y otro con pérdida.
- Consultar pedidos, alertas y obligaciones desde el tablero.

## Entrega 11: Adjuntos, auditoría y respaldos

### Objetivo

Completar trazabilidad y recuperación operativa.

### Incluye

- Archivos privados.
- Comprobantes opcionales.
- Auditoría consultable.
- Respaldos de base y adjuntos.
- Manifiesto e integridad.
- Procedimiento de restauración.

### Demostración de cierre

- Adjuntar y consultar un comprobante con permisos.
- Revisar una autorización en auditoría.
- Crear, descargar y verificar un respaldo.

## Entrega 12: Preparación y lanzamiento por Internet

### Objetivo

Desplegar de forma segura con MySQL y datos reales.

### Incluye

- Ambiente de ensayo.
- Validación completa de migraciones en MySQL.
- Dominio y HTTPS.
- Almacenamiento persistente.
- Programador de tareas y procesos diferidos necesarios.
- Backups automáticos.
- Carga inicial controlada.
- Revisión de permisos.
- Lista de humo posterior al despliegue.

## Entrega futura: Capacitor

No bloquea el lanzamiento web.

Incluye cuando se autorice:

- API móvil `/api/mobile/v1`.
- Autenticación por dispositivo.
- Reutilización de componentes Vue viable.
- Cámara y selector de archivos.
- Empaquetado Capacitor.
- Sin modo offline inicialmente.

## Dependencias resumidas

```text
Identidad
-> Catálogo e inventario
-> Compras
-> Recetas y costos
-> Producción
-> Pedidos
-> Ventas y caja
-> Finanzas
-> Hogar
-> Reportes
-> Auditoría y respaldos
-> Lanzamiento
```

## Estimaciones

No se fijan duraciones antes de inicializar el proyecto y validar el entorno real. Las estimaciones se realizarán por entrega después de revisar dependencias, versiones y disponibilidad del usuario para validar.
