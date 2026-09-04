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
- [x] Verificación funcional y commit.
- Evidencia de clientes y precios: commit `769e76b`; perfiles comerciales sin duplicar datos personales, lista predeterminada por cliente, minorista inicial, mayorista, promocional, vigencias, precios mínimos y precios configurables por presentación.
- Evidencia de pedidos: commit `6794c94`; borradores editables, precio original y acordado, autorización bajo mínimo, fechas y prioridad, historial, anticipos con asiento contable, reservas parciales y liberación controlada.
- Evidencia de demanda y producción: commit `f826eaf`; recordatorio de próximas 24 horas en el tablero, faltante convertido a masa y harina, consolidación de varios pedidos por versión de receta y asignaciones entre pedidos y producciones.
- Completar una producción vinculada reserva sus resultados para cada pedido; revertirla libera esas reservas y reabre la demanda de forma atómica.
- Se agregaron dos pruebas funcionales básicas: reserva con anticipo, y dos pedidos consolidados en una producción con cumplimiento y reversión.
- Verificación final informada por el usuario el 2 de septiembre de 2026: las dos pruebas de pedidos aprobaron correctamente.

## Entrega 7: Ventas, cartera y caja

- [x] Punto de venta.
- [x] Venta directa y desde pedido.
- [x] Unidades, paquetes y conversiones en venta.
- [x] Contado, crédito, pagos y abonos.
- [x] Cuentas por cobrar.
- [x] Caja principal y caja menor.
- [x] Apertura, cierre y diferencias.
- [x] Devolución básica.
- [x] Verificación funcional y commit.
- Evidencia de caja: commit `0e024b0`; caja principal y menor, traslados con saldo suficiente, apertura, cierre, saldo esperado, explicación de diferencias y autorización sobre el umbral configurable.
- Evidencia de ventas y cartera: commit `53a36a8`; venta directa o desde pedido, precios trazables, contado, crédito, múltiples pagos, aplicación de anticipos, cuentas por cobrar y abonos parciales.
- La venta consume reservas propias del pedido, descuenta inventario y registra ingreso, costo y utilidad bruta de forma atómica.
- Cuando faltan unidades, el punto de venta puede desarmar automáticamente un paquete compatible del mismo producto antes de evaluar inventario negativo.
- La devolución controla el acumulado por línea, permite decidir si el producto vuelve al inventario, aplica primero el crédito a cartera y reembolsa únicamente el excedente desde una cuenta con saldo.
- Se agregaron dos pruebas funcionales básicas: venta en caja con desarmado, devolución y cierre; venta a crédito con abono parcial.
- La primera verificación confirmó pedidos, finanzas y venta a crédito; el escenario de caja requirió alinear el reloj simulado del cierre con la devolución ya registrada.
- Verificación final informada por el usuario el 2 de septiembre de 2026: el escenario corregido de caja, paquetes, devolución e inventario aprobó con 8 assertions.

## Entrega 8: Finanzas del negocio

- [x] Cuentas y asientos balanceados.
- [x] Ingresos, gastos y obligaciones.
- [x] Pagos parciales.
- [x] Flujo de caja y rentabilidad separados.
- [x] Conciliación de costos.
- [x] Cierre mensual.
- [x] Verificación funcional y commit.
- Evidencia de esquema y catálogos: commit `bf0d322`; categorías con cuenta contable, ingresos y gastos separados, ámbitos y vinculación opcional al fondo de costos.
- Evidencia operativa: commit `d62ef2f`; documentos pagados, parciales o pendientes, cartera y obligaciones con terceros, abonos posteriores y trazabilidad por cuenta, categoría, persona y mes.
- El tablero financiero deriva el flujo únicamente de pagos confirmados y la rentabilidad de cuentas de ingreso, costo y gasto; las transferencias internas no inflan ninguno de los dos valores.
- Evidencia de cierre: commit `88920cb`; variación por electricidad, gas y mano de obra, reclasificación del costo aplicado al inventario, bloqueo por producciones pendientes y reapertura con asiento inverso.
- Evidencia de prueba básica: commit `4b85f87`; un flujo integral cubre ingreso, servicios, mano de obra parcialmente pagada y conciliación mensual.
- Corrección posterior a la primera verificación: commit `c0fd4bb`; precios acepta fechas mutables o inmutables y la expectativa del saldo parcial coincide con el abono registrado.
- Verificación final informada por el usuario el 2 de septiembre de 2026: la prueba integral de movimientos, pago parcial y conciliación mensual aprobó correctamente.

## Entrega 9: Hogar

- [x] Cuentas y movimientos del hogar.
- [x] Visibilidad propia por habitante.
- [x] Solicitudes al hogar o negocio.
- [x] Pago desde negocio como mano de obra.
- [x] Presupuestos.
- [x] Deudas, préstamos y cuotas.
- [x] Metas de ahorro.
- [x] Verificación funcional y commit.
- Evidencia inicial de E9: commit `f1306f5`; cuentas compartidas o personales, saldo inicial contable, ingresos, gastos y transferencias internas sin inflar el flujo.
- La consulta mensual y los saldos aplican una política de visibilidad reutilizable: el propietario autorizado consolida el hogar y cada habitante solo accede a sus cuentas y movimientos relacionados.
- La pantalla del hogar permite administrar cuentas y movimientos únicamente con `household.manage`; el rol habitante conserva una vista propia de solo lectura y podrá crear solicitudes de fondos en el siguiente bloque.
- Evidencia de solicitudes: commit `c2d64d2`; creación, historial de estados, aprobación o rechazo, pago y confirmación de recepción con permisos separados.
- El desembolso desde el hogar es una transferencia interna; desde el negocio genera un gasto pagado de mano de obra, una entrada en la cuenta personal del habitante y un aporte al fondo de costos del periodo abierto, sin duplicar conceptos.
- Evidencia de presupuestos: commit `ccf0422`; presupuesto mensual borrador o confirmado, líneas por categoría y habitante opcional, ejecutado, disponible y porcentaje de consumo.
- Solo los gastos marcados consumen presupuesto; las transferencias internas quedan excluidas y la visibilidad del ejecutado respeta el alcance autorizado del usuario.
- Evidencia de deudas y ahorro: commit `4e7aac6`; obligaciones por pagar, préstamos por cobrar, cronograma mensual, capital e interés separados, abonos parciales o anticipados y cierre automático de cuotas.
- Las metas muestran objetivo, ahorrado, pendiente y porcentaje; cada aporte es un traslado contable entre cuentas con validación de saldo, sin crear ingresos o gastos ficticios.
- Pruebas básicas preparadas en el commit `1988ebb`: un escenario de privacidad, solicitud, mano de obra y presupuesto; otro de deuda, interés y ahorro.
- Verificación final informada por el usuario el 2 de septiembre de 2026: las 2 pruebas de hogar aprobaron con 14 aserciones y la compilación de producción terminó correctamente.

## Entrega 10: Tableros y reportes

- [x] Tableros por rol.
- [x] Resultado diario y mensual.
- [x] Flujo de caja y rentabilidad.
- [x] Márgenes, rendimiento y merma.
- [x] Inventario valorizado.
- [x] Cartera, obligaciones y presupuesto del hogar.
- [x] Verificación funcional y commit.
- Evidencia inicial de E10: commit `7e0a203`; reportes con rango de hasta 366 días, permisos financieros, operativos o del hogar y filtros por producto o persona.
- El flujo se deriva de pagos confirmados y el resultado de asientos del ámbito negocio; se muestran por separado, por día y acumulados, indicando como provisional cualquier rango con meses sin cerrar.
- Los márgenes descuentan devoluciones, la producción muestra rendimiento, merma y costo, y el inventario se valoriza con existencia física por costo promedio.
- Cartera, obligaciones, ejecución presupuestal, solicitudes, deudas y ahorro conservan los filtros de ámbito y privacidad definidos en sus módulos de origen.
- Evidencia del tablero: commit `38724d3`; propietario y administrador ven decisiones financieras, producción y ventas ven indicadores operativos sin costos, y cada habitante recibe únicamente el resumen privado del hogar.
- El tablero incorpora acciones rápidas por permiso, pedidos próximos o atrasados, demanda pendiente de producción, periodo de costos faltante, inventario crítico, cartera vencida y solicitudes por atender.
- Pruebas básicas preparadas en el commit `b6d55c6`: separación entre flujo y resultado con obligación vencida, y bloqueo de costos/valorización para un rol exclusivamente operativo.
- Verificación final informada por el usuario el 2 de septiembre de 2026: las 2 pruebas aprobaron con 12 aserciones y la compilación de producción terminó correctamente.
- Protección complementaria en el commit `5976912`: los costos de una producción también se omiten en el servidor y en la interfaz cuando el usuario no posee `reports.view-financial`.

## Entrega 11: Adjuntos, auditoría y respaldos

- [x] Adjuntos privados.
- [x] Auditoría consultable.
- [x] Respaldos de base de datos y archivos.
- [x] Manifiesto e integridad.
- [x] Restauración controlada.
- [x] Verificación funcional y commit.
- Evidencia de adjuntos: commit `995263e`; almacenamiento privado con nombres físicos aleatorios, PDF/JPEG/PNG hasta 10 MB configurable, hash SHA-256 y autorización heredada del documento.
- La carga, descarga y eliminación quedan auditadas; cada descarga comprueba la integridad antes de entregar el archivo. El panel reutilizable ya está integrado en ventas, compras y producciones.
- Evidencia de auditoría: commit `7685353`; filtros por usuario, acción, documento, rango de fechas y presencia de autorización, detalle enlazado de la excepción y paginación conservando filtros.
- Los datos antes/después pasan por un sanitizador recursivo central que reemplaza contraseñas, secretos, tokens, credenciales, cookies y llaves privadas por `[REDACTADO]`.
- Evidencia de respaldos: commit `5dc5493`; genera un ZIP privado con copia consistente SQLite o `mysqldump`, todos los adjuntos registrados y un manifiesto versionado con tamaño y SHA-256 por entrada.
- La verificación comprueba el hash del archivo completo, identidad del manifiesto, base de datos y cada adjunto. La restauración no se expone como botón web: exige el comando `backups:restore`, frase exacta por ULID, una segunda verificación, respaldo previo y modo mantenimiento.
- Pruebas básicas: sanitización recursiva y creación/verificación real de un respaldo SQLite.
- Primera verificación del 3 de septiembre de 2026: las migraciones nuevas aprobaron, pero la prueba detectó el orden incorrecto al revertir un índice único antiguo y el build detectó un import de iconos inconsistente. Ambos defectos quedaron corregidos en `be47670`; se requiere repetir solo prueba y build.
- Segunda verificación: el build aprobó y la sanitización obtuvo 4 aserciones correctas; el escenario SQLite quedó aislado en `15c6978` para evitar que una base `:memory:` nueva heredara estado estático entre dos pruebas.
- Verificación final informada por el usuario el 4 de septiembre de 2026: el respaldo SQLite y su manifiesto aprobaron con 4 aserciones; junto con la compilación previa, E11 queda cerrada.

## Entrega 12A: Operación local en la red Wi-Fi (prioridad actual)

- [x] Ambiente `lan` separado del desarrollo y de producción.
- [x] URL validada con IPv4 privada y servidor accesible en `0.0.0.0`.
- [x] SQLite, sesiones cifradas y assets compilados verificados por diagnóstico.
- [x] Contraseñas fuertes y comandos destructivos protegidos en ambiente LAN.
- [x] Guía de firewall privado, IP estable, uso diario y programador.
- [x] Plantilla de configuración sin secretos.
- [ ] Acceso comprobado desde un segundo dispositivo conectado al mismo Wi-Fi.
- [ ] Respaldo automático creado y copia externa comprobada.
- [ ] Revisión de permisos y comprobación de humo con usuarios reales.
- [x] Commit de operación LAN.

El comando `app:lan-readiness` detecta configuración insegura o incompleta y `app:lan` solo inicia cuando no hay errores. Este modo nunca requiere ni autoriza abrir el puerto 8000 hacia Internet.

- Evidencia de implementación LAN: commit `ed8dd65`; incluye plantilla sin secretos, diagnóstico, servidor vinculado a todas las interfaces, protección del ambiente desplegado y guía de operación en Windows/Wi-Fi.
- La verificación desde otro dispositivo, la regla real del firewall y la copia externa del primer respaldo requieren ejecutarse en la red del usuario antes de cerrar esta entrega.

## Entrega 12B: Lanzamiento por Internet (posterior)

- [ ] Migraciones verificadas en MySQL.
- [ ] Ambiente de ensayo.
- [ ] Dominio gratuito registrado, DNS delegado y HTTPS verificado.
- [x] Estrategia de almacenamiento persistente y externo definida.
- [ ] Tareas programadas y procesos diferidos necesarios.
- [ ] Respaldos automáticos.
- [ ] Carga inicial y fecha de corte.
- [ ] Revisión de permisos y comprobación de humo.
- [x] Commit de preparación de lanzamiento.
- Preparación portable en `dea2089`: plantilla de producción sin secretos, HTTPS y proxies configurables, diagnóstico `app:readiness`, respaldo diario con retención, escenario CI MySQL y runbook con checklist de lanzamiento.
- El proveedor, ambiente de ensayo, dominio, persistencia externa, cron real, MySQL real y fecha de corte siguen deliberadamente pendientes de evidencia operativa.
- Flujo de puesta en marcha en `6a4b8a1`: registra fecha de corte y declaraciones de conciliación, exige propietario, periodo mensual abierto y respaldo reciente válido, requiere contraseña y frase exacta, y bloquea cambios después de la activación auditada.
- La fecha de corte se interpreta en `America/Bogota`, se persiste en UTC y vuelve a mostrarse en hora local; corrección registrada en `49c16b0`.
- Verificación local informada por el usuario el 4 de septiembre de 2026: la migración `launch_configurations` y la compilación con Node 24.5.0 aprobaron. La carga real y activación siguen pendientes del lanzamiento.
- Diagnóstico de producción reforzado en `5985b82`: exige HTTPS forzado, cookie y sesión cifradas, correo real de recuperación, remitente configurado y extensión Zip; la plantilla usa `MAIL_SCHEME` compatible con Laravel actual.
- Infraestructura preseleccionada: VPS Hostinger o Teramont, dominio inicial DigitalPlat FreeDomain con DNS autoritativo externo, adjuntos privados persistentes y respaldos en un bucket S3 compatible fuera del VPS. La contratación, el nombre exacto y la evidencia operativa siguen pendientes.
- Los discos `attachments_s3` y `backups_s3` tienen credenciales independientes y acceso privado; producción usará `backups_s3` aunque los adjuntos permanezcan inicialmente en el volumen del VPS.

## Futuro: Capacitor

- [ ] API móvil versionada.
- [ ] Autenticación revocable por dispositivo.
- [ ] Integración de cámara y archivos.
- [ ] Empaquetado Capacitor.
- [ ] Pruebas mínimas de publicación.
