# Riesgos y decisiones aplazadas

## Registro de riesgos

| Riesgo | Impacto | Probabilidad inicial | Mitigación |
|---|---|---|---|
| Inventario inicial incorrecto | Alto | Alta | Conteo físico, responsable y documento de apertura |
| Recetas o rendimientos poco precisos | Alto | Media | Producciones de ensayo y comparación esperado/real |
| Costos de gas, energía o mano de obra duplicados | Alto | Media | Fondos de costo, aplicaciones y variación mensual |
| Uso frecuente de inventario negativo | Alto | Media | Autorización, incidencias visibles y regularización |
| Caja principal y menor no coinciden con efectivo | Alto | Media | Apertura/cierre diario y traslados documentados |
| Mezcla incorrecta entre hogar y negocio | Alto | Media | Ámbitos separados y transferencias balanceadas |
| Pagos a habitantes clasificados dos veces | Alto | Media | Action única de pago desde negocio y asientos definidos |
| Diferencias entre SQLite y MySQL | Alto | Media | Pruebas tempranas de migraciones y flujos críticos en MySQL |
| Pérdida de datos o adjuntos | Alto | Baja/Media | Respaldos diarios, integridad y restauración probada |
| Acceso no autorizado por Internet | Alto | Media | HTTPS, Policies, sesiones revocables y revisión de roles |
| Internet no disponible durante operación | Medio/Alto | Media | Procedimiento temporal manual; no prometer modo offline |
| Pedidos sin receta o peso de masa configurado | Medio | Media | Validación antes de confirmar demanda productiva |
| Usuarios omiten apertura mensual de costos | Medio | Media | Bloqueo de finalización y alerta en tablero |
| Demasiadas excepciones autorizadas | Medio | Media | Reporte de excepciones y revisión periódica |
| Crecimiento excesivo del alcance | Alto | Alta | Respetar entregas y mover mejoras no críticas a versiones futuras |
| Dependencia de un proveedor de hosting | Medio | Baja | Diseño portable, MySQL estándar y respaldos descargables |

## Riesgos contables

El sistema ofrece contabilidad administrativa para decisiones internas. No reemplaza asesoría contable o tributaria colombiana y no se presentará como software de facturación electrónica DIAN en la primera versión.

Antes de utilizar reportes para obligaciones fiscales debe revisarlos una persona competente y definir integraciones futuras.

## Operación temporal sin Internet

Como no existe modo offline en la primera versión, se documentará un procedimiento simple:

1. Registrar temporalmente ventas y gastos en un formato manual.
2. No inventar consecutivos definitivos.
3. Al volver Internet, ingresar operaciones en orden cronológico.
4. Marcar que fueron capturadas posteriormente.
5. Conciliar caja e inventario.

Esto no es sincronización automática y debe utilizarse solo durante interrupciones.

## Decisiones aplazadas hasta iniciar implementación

### Versiones exactas

- Versión estable de Laravel.
- Versiones de PHP, Node y TypeScript.
- Dependencias de Inertia y Vue.

Se elegirán verificando compatibilidad oficial en el momento de inicializar el proyecto.

### Paquetes externos

Se evaluará si conviene usar paquetes mantenidos para:

- Roles y permisos.
- Respaldos.
- Auditoría.
- Manejo de archivos.

Cada paquete deberá justificar su uso, soportar las versiones elegidas y quedar detrás de interfaces cuando afecte el dominio.

### Diseño visual

- Nombre visual definitivo.
- Logotipo.
- Colores y tipografía.
- Componentes visuales base.

Estas decisiones no alteran el modelo funcional y se cerrarán durante la Entrega 0.

### Datos e importación

- Cantidad real de productos, recetas, clientes y proveedores.
- Existencia de hojas de cálculo aprovechables.
- Necesidad de importación CSV específica.

Se decide antes de la carga inicial, no antes de conocer las fuentes.

## Decisiones aplazadas hasta despliegue

- Proveedor de alojamiento.
- Dominio.
- Región del servidor.
- Servicio de almacenamiento persistente.
- Política final de retención de respaldos.
- Correo transaccional para recuperación de contraseña, si se utiliza.
- Herramienta externa de monitoreo, si resulta necesaria.

La arquitectura permanece independiente del proveedor.

## Decisiones configurables durante implementación

- Límite monetario de diferencia de caja que exige autorización.
- Límite o tolerancia de variación en consumos de producción.
- Precio mínimo y permisos de excepción.
- Límite de crédito por cliente.
- Categorías financieras.
- Métodos de pago.
- Anticipación de pedidos, con valor inicial de 24 horas.
- Tamaño máximo de adjuntos, recomendado 10 MB.
- Tiempo de expiración de autorizaciones.

No se codificarán estos valores de forma fija.

## Mejoras candidatas para versiones posteriores

- Facturación electrónica DIAN.
- Varias sedes y almacenes.
- Lotes y vencimientos.
- Nómina formal.
- Notificaciones por WhatsApp, correo o push.
- Código de barras y hardware especializado.
- Importaciones genéricas.
- Aplicación Capacitor publicada.
- Modo offline con sincronización.
- Integración bancaria.
- Contabilidad tributaria avanzada.

## Criterio para cambiar el alcance

Una mejora entra en la primera versión solo si:

- Es necesaria para completar un flujo principal acordado.
- Evita pérdida o inconsistencia de dinero o inventario.
- Es indispensable para operar legal o técnicamente en el entorno elegido.

En cualquier otro caso se registra como mejora posterior para proteger el lanzamiento inicial.

## Estado de decisiones fundamentales

Ya están confirmados:

- Una sede.
- Múltiples usuarios y habitantes.
- Ventas individuales.
- Unidades y paquetes convertibles.
- Crédito y abonos.
- Sin lotes ni vencimientos inicialmente.
- Masa base distribuida entre varios productos.
- Consumo por receta con novedades.
- Costos de mano de obra, gas y electricidad.
- COP y Colombia.
- Presupuestos, deudas y préstamos del hogar.
- Laravel, Inertia, Vue y TypeScript.
- SQLite en desarrollo y MySQL en producción.
- Acceso desde Internet.
- Capacitor como aplicación instalable futura.
- Pruebas básicas y enfocadas.

