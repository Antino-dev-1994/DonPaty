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
- [ ] Verificación funcional y commit.
- Evidencia de proveedores y compras base: commit `37abfc8`; perfiles enlazados a personas, condiciones comerciales, compras confirmadas, líneas y distribución proporcional de costos adicionales.
- Evidencia de recepciones: commit `6280623`; recepciones parciales confirmadas, control de cantidades, autorización para excedentes, entradas de inventario y costo promedio con transporte distribuido.
- Evidencia financiera y devoluciones: commit `d21abec`; cuentas, obligaciones, pagos, aplicaciones y asientos de partida doble. Cada compra genera una cuenta por pagar y admite abonos parciales sin duplicar su costo.
- El mismo bloque incluye devoluciones básicas con documento independiente, salida de inventario, trazabilidad por línea y nota crédito aplicada al saldo sin alterar el valor original de la compra.

## Entrega 4: Recetas y periodos de costos

- [x] Recetas de masa base.
- [x] Versiones e ingredientes.
- [x] Productos compatibles y rendimientos.
- [x] Periodos mensuales.
- [x] Gas, electricidad y distribución hogar/negocio.
- [x] Tarifas sugeridas y manuales.
- [ ] Verificación funcional y commit.
- Evidencia de recetas: commit `0d68bdb`; borradores completos, ingredientes y porcentajes panaderos, productos compatibles, rendimientos, clonación histórica, rangos de vigencia y previsualización de escalado decimal.
- Evidencia de costos: commit `34324a2`; apertura mensual, facturas previas de gas y electricidad, reparto exacto hogar/negocio, fondos productivos, tarifa sugerida por harina histórica, tarifa manual justificada y alerta de periodo faltante en el tablero.
- Se agregaron dos pruebas funcionales básicas para cubrir historial/escalado de recetas y el paso de tarifa inicial manual a tarifa sugerida.

## Entrega 5: Producción

- [ ] Planeación y escalado por harina.
- [ ] Disponibilidad y consumos.
- [ ] Mano de obra configurable.
- [ ] Novedades, masa y merma.
- [ ] Distribución entre productos.
- [ ] Costos y movimientos atómicos.
- [ ] Reversión controlada.
- [ ] Verificación funcional y commit.

## Entrega 6: Clientes, precios y pedidos

- [ ] Clientes.
- [ ] Listas y reglas de precios.
- [ ] Pedidos, anticipos y reservas.
- [ ] Recordatorio de 24 horas.
- [ ] Demanda pendiente y producción sugerida.
- [ ] Vinculación pedido-producción.
- [ ] Verificación funcional y commit.

## Entrega 7: Ventas, cartera y caja

- [ ] Punto de venta.
- [ ] Venta directa y desde pedido.
- [ ] Unidades, paquetes y conversiones en venta.
- [ ] Contado, crédito, pagos y abonos.
- [ ] Cuentas por cobrar.
- [ ] Caja principal y caja menor.
- [ ] Apertura, cierre y diferencias.
- [ ] Devolución básica.
- [ ] Verificación funcional y commit.

## Entrega 8: Finanzas del negocio

- [ ] Cuentas y asientos balanceados.
- [ ] Ingresos, gastos y obligaciones.
- [ ] Pagos parciales.
- [ ] Flujo de caja y rentabilidad separados.
- [ ] Conciliación de costos.
- [ ] Cierre mensual.
- [ ] Verificación funcional y commit.

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
