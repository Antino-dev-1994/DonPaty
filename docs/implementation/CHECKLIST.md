# Checklist maestro de implementación

Este documento registra el avance real de DonPaty. Un elemento solo se marca como completado cuando existe evidencia en el repositorio y, cuando aplica, fue verificado.

## Convenciones

- `[x]`: terminado y confirmado en Git.
- `[ ]`: pendiente.
- Cada entrega funcional debe actualizar este archivo antes de su commit.
- Los hashes se registran después de confirmar cada bloque; el historial de Git es la evidencia autoritativa.

## Planeación

- [x] Visión, alcance y decisiones de producto.
- [x] Reglas de negocio.
- [x] Modelo de dominio.
- [x] Backlog funcional.
- [x] Mapa de pantallas y flujos operativos.
- [x] Matriz de permisos.
- [x] Requisitos no funcionales y estrategia básica de pruebas.
- [x] Arquitectura, dependencias, eventos y esquema físico.
- [x] Plan de implementación, carga inicial, lanzamiento y riesgos.
- Evidencia: commit `5d74ff6` (`init`).

## Entrega 0: Inicialización técnica

- [x] Confirmar versiones de PHP, Laravel, Node y dependencias frontend.
- [x] Inicializar Laravel en la raíz conservando la documentación.
- [x] Configurar Inertia, Vue 3 y TypeScript.
- [x] Configurar SQLite para desarrollo.
- [x] Configurar COP, UTC técnico y `America/Bogota` para presentación.
- [x] Crear estructura base del monolito modular.
- [x] Crear layout y navegación inicial.
- [x] Configurar formato, lint y comprobación de tipos.
- [x] Preparar las pruebas básicas.
- [x] Verificar instalación, migraciones, build y pruebas esenciales.
- [x] Commit de Entrega 0.
- Evidencia: commit `4af5094`; migraciones aplicadas, build frontend correcto y 39 pruebas básicas aprobadas (144 aserciones).

## Entrega 1: Identidad, personas y permisos

- [x] Personas y clasificaciones.
- [x] Usuarios, autenticación y sesiones.
- [x] Roles y permisos.
- [x] Policies y ámbito propio/global.
- [x] Autorizaciones excepcionales.
- [x] Auditoría mínima.
- [x] Verificación funcional y commit.
- Evidencia: commit `d9b7ddb`; catálogo de acceso sembrado, 3 pruebas funcionales aprobadas con 8 aserciones y build frontend completado correctamente por el usuario.
- Datos locales: seeder idempotente de propietario de desarrollo, aislado de producción y sin sobrescribir contraseñas modificadas.

## Entrega 2: Catálogo e inventario

- [x] Unidades y conversiones.
- [x] Artículos y presentaciones.
- [x] Paquetes y componentes.
- [x] Libro de movimientos y saldos.
- [x] Ajustes e inventario negativo autorizado.
- [x] Armado y desarmado de paquetes.
- [x] Inventario inicial.
- [x] Verificación funcional y commit.
- Evidencia del catálogo: commit `4f4b21d`; referencias iniciales idempotentes, reglas de dimensión, artículos, presentaciones, composición de paquetes, pantallas y dos pruebas funcionales básicas.
- Evidencia de inventario: commit `9480622`; kardex, saldos físicos/reservados/disponibles, costo promedio, ajustes, incidentes negativos, saldos iniciales, conversiones de paquetes, reversión y tres pruebas funcionales básicas.
- Correcciones verificadas: commits `863b8cd` y `18ee65b`; migración SQLite sin índice duplicado, compatibilidad con fechas Carbon inmutables y cachés de PHPUnit aisladas.
- Verificación final informada por el usuario el 30 de agosto de 2026: pruebas básicas de catálogo e inventario aprobadas.

## Entrega 3: Proveedores y compras

- [x] Proveedores.
- [x] Compras y recepciones parciales.
- [x] Entradas y costo promedio.
- [x] Cuentas por pagar y pagos parciales.
- [x] Devoluciones básicas.
- [x] Verificación funcional y commit.
- Evidencia de proveedores y compras base: commit `37abfc8`; perfiles enlazados a personas, condiciones comerciales, compras confirmadas, líneas y distribución proporcional de costos adicionales.
- Evidencia de recepciones: commit `6280623`; recepciones parciales confirmadas, control de cantidades, autorización para excedentes, entradas de inventario y costo promedio con transporte distribuido.
- Evidencia financiera y devoluciones: commit `d21abec`; cuentas, obligaciones, pagos, aplicaciones y asientos de partida doble. Cada compra genera una cuenta por pagar y admite abonos parciales sin duplicar su costo.
- El mismo bloque incluye devoluciones básicas con documento independiente, salida de inventario, trazabilidad por línea y nota crédito aplicada al saldo sin alterar el valor original de la compra.
- Verificación final informada por el usuario el 1 de septiembre de 2026: migraciones, seeder y las dos pruebas básicas de compras aprobadas.

## Entrega 4: Recetas y periodos de costos

- [x] Recetas de masa base.
- [x] Versiones e ingredientes.
- [x] Productos compatibles y rendimientos.
- [x] Periodos mensuales.
- [x] Gas, electricidad y distribución hogar/negocio.
- [x] Tarifas sugeridas y manuales.
- [x] Verificación funcional y commit.
- Evidencia de recetas: commit `0d68bdb`; borradores completos, ingredientes y porcentajes panaderos, productos compatibles, rendimientos, clonación histórica, rangos de vigencia y previsualización de escalado decimal.
- Evidencia de costos: commit `34324a2`; apertura mensual, facturas previas de gas y electricidad, reparto exacto hogar/negocio, fondos productivos, tarifa sugerida por harina histórica, tarifa manual justificada y alerta de periodo faltante en el tablero.
- Se agregaron dos pruebas funcionales básicas para cubrir historial/escalado de recetas y el paso de tarifa inicial manual a tarifa sugerida.
- Verificación final informada por el usuario el 1 de septiembre de 2026: las dos pruebas básicas de recetas y periodos de costos aprobadas.

## Entrega 5: Producción

- [x] Planeación y escalado por harina.
- [x] Disponibilidad y consumos.
- [x] Mano de obra configurable.
- [x] Novedades, masa y merma.
- [x] Distribución entre productos.
- [x] Costos y movimientos atómicos.
- [x] Reversión controlada.
- [x] Verificación funcional y commit.
- Evidencia de producción: commit `053aacf`; planeación por versión exacta y kilogramos de harina, disponibilidad, inicio autorizado ante faltantes, consumos reales, novedades, masa, merma y múltiples productos compatibles.
- El mismo bloque calcula mano de obra por tarifa estándar, horas reales o valor manual autorizado; distribuye costos enteros, publica entradas y salidas de inventario de forma atómica y permite reversión controlada.
- Se agregaron dos pruebas funcionales básicas: ciclo completo con dos productos y reversión, y bloqueo de inicio cuando existen faltantes sin autorización.
- Verificación final informada por el usuario el 1 de septiembre de 2026: las dos pruebas básicas de producción y la compilación del frontend con Node 24.5.0 aprobaron correctamente.

## Entrega 6: Clientes, precios y pedidos

- [x] Clientes.
- [x] Listas y reglas de precios.
- [x] Pedidos, anticipos y reservas.
- [x] Recordatorio de 24 horas.
- [x] Demanda pendiente y producción sugerida.
- [x] Vinculación pedido-producción.
- [ ] Verificación funcional y commit.
- Evidencia de clientes y precios: commit `769e76b`; perfiles comerciales sin duplicar datos personales, lista predeterminada por cliente, minorista inicial, mayorista, promocional, vigencias, precios mínimos y precios configurables por presentación.
- Evidencia de pedidos: commit `6794c94`; borradores editables, precio original y acordado, autorización bajo mínimo, fechas y prioridad, historial, anticipos con asiento contable, reservas parciales y liberación controlada.
- Evidencia de demanda y producción: commit `f826eaf`; recordatorio de próximas 24 horas en el tablero, faltante convertido a masa y harina, consolidación de varios pedidos por versión de receta y asignaciones entre pedidos y producciones.
- Completar una producción vinculada reserva sus resultados para cada pedido; revertirla libera esas reservas y reabre la demanda de forma atómica.
- Se agregaron dos pruebas funcionales básicas: reserva con anticipo, y dos pedidos consolidados en una producción con cumplimiento y reversión.

## Entrega 7: Ventas, cartera y caja

- [x] Punto de venta.
- [x] Venta directa y desde pedido.
- [x] Unidades, paquetes y conversiones en venta.
- [x] Contado, crédito, pagos y abonos.
- [x] Cuentas por cobrar.
- [x] Caja principal y caja menor.
- [x] Apertura, cierre y diferencias.
- [x] Devolución básica.
- [ ] Verificación funcional y commit.
- Evidencia de caja: commit `0e024b0`; caja principal y menor, traslados con saldo suficiente, apertura, cierre, saldo esperado, explicación de diferencias y autorización sobre el umbral configurable.
- Evidencia de ventas y cartera: commit `53a36a8`; venta directa o desde pedido, precios trazables, contado, crédito, múltiples pagos, aplicación de anticipos, cuentas por cobrar y abonos parciales.
- La venta consume reservas propias del pedido, descuenta inventario y registra ingreso, costo y utilidad bruta de forma atómica.
- Cuando faltan unidades, el punto de venta puede desarmar automáticamente un paquete compatible del mismo producto antes de evaluar inventario negativo.
- La devolución controla el acumulado por línea, permite decidir si el producto vuelve al inventario, aplica primero el crédito a cartera y reembolsa únicamente el excedente desde una cuenta con saldo.
- Se agregaron dos pruebas funcionales básicas: venta en caja con desarmado, devolución y cierre; venta a crédito con abono parcial.

## Entrega 8: Finanzas del negocio

- [x] Cuentas y asientos balanceados.
- [x] Ingresos, gastos y obligaciones.
- [x] Pagos parciales.
- [x] Flujo de caja y rentabilidad separados.
- [x] Conciliación de costos.
- [x] Cierre mensual.
- [ ] Verificación funcional y commit.
- Evidencia de esquema y catálogos: commit `bf0d322`; categorías con cuenta contable, ingresos y gastos separados, ámbitos y vinculación opcional al fondo de costos.
- Evidencia operativa: commit `d62ef2f`; documentos pagados, parciales o pendientes, cartera y obligaciones con terceros, abonos posteriores y trazabilidad por cuenta, categoría, persona y mes.
- El tablero financiero deriva el flujo únicamente de pagos confirmados y la rentabilidad de cuentas de ingreso, costo y gasto; las transferencias internas no inflan ninguno de los dos valores.
- Evidencia de cierre: commit `88920cb`; variación por electricidad, gas y mano de obra, reclasificación del costo aplicado al inventario, bloqueo por producciones pendientes y reapertura con asiento inverso.
- Evidencia de prueba básica: commit `4b85f87`; un flujo integral cubre ingreso, servicios, mano de obra parcialmente pagada y conciliación mensual.

## Entrega 9: Hogar

- [ ] Cuentas y movimientos del hogar.
- [ ] Visibilidad propia por habitante.
- [ ] Solicitudes al hogar o negocio.
- [ ] Pago desde negocio como mano de obra.
- [ ] Presupuestos.
- [ ] Deudas, préstamos y cuotas.
- [ ] Metas de ahorro.
- [ ] Verificación funcional y commit.

## Entrega 10: Tableros y reportes

- [ ] Tableros por rol.
- [ ] Resultado diario y mensual.
- [ ] Flujo de caja y rentabilidad.
- [ ] Márgenes, rendimiento y merma.
- [ ] Inventario valorizado.
- [ ] Cartera, obligaciones y presupuesto del hogar.
- [ ] Verificación funcional y commit.

## Entrega 11: Adjuntos, auditoría y respaldos

- [ ] Adjuntos privados.
- [ ] Auditoría consultable.
- [ ] Respaldos de base de datos y archivos.
- [ ] Manifiesto e integridad.
- [ ] Restauración controlada.
- [ ] Verificación funcional y commit.

## Entrega 12: Lanzamiento por Internet

- [ ] Migraciones verificadas en MySQL.
- [ ] Ambiente de ensayo.
- [ ] Dominio y HTTPS.
- [ ] Almacenamiento persistente.
- [ ] Tareas programadas y procesos diferidos necesarios.
- [ ] Respaldos automáticos.
- [ ] Carga inicial y fecha de corte.
- [ ] Revisión de permisos y comprobación de humo.
- [ ] Commit de preparación de lanzamiento.

## Futuro: Capacitor

- [ ] API móvil versionada.
- [ ] Autenticación revocable por dispositivo.
- [ ] Integración de cámara y archivos.
- [ ] Empaquetado Capacitor.
- [ ] Pruebas mínimas de publicación.
