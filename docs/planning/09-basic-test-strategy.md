# Estrategia básica de pruebas

## Objetivo

Mantener una cantidad pequeña de pruebas que proteja las operaciones con mayor riesgo para dinero, inventario y costos. No se busca probar cada pantalla o cada operación CRUD.

## Decisiones

- No habrá una meta obligatoria de porcentaje de cobertura.
- No se crearán pruebas unitarias para getters, validaciones triviales o código propio del framework.
- No habrá inicialmente una suite extensa de navegador extremo a extremo.
- Las pantallas y formularios comunes se validarán mediante una lista manual corta durante cada entrega.
- Se automatizarán únicamente cálculos y flujos cuya falla pueda producir saldos incorrectos.
- Un error de producción que pueda repetirse deberá agregar una prueba de regresión específica.

## Nivel 1: comprobaciones automáticas rápidas

Se ejecutarán antes de integrar cambios relevantes:

- Formato de PHP.
- Análisis de TypeScript.
- Lint básico del frontend.
- Arranque de la aplicación y conexión de base de datos.
- Migraciones desde una base vacía.

Estas comprobaciones detectan errores mecánicos sin ampliar innecesariamente la suite funcional.

## Nivel 2: pruebas unitarias esenciales

Solo se consideran obligatorios inicialmente estos cálculos:

### 1. Escalado de receta

Verifica que ingredientes y rendimiento se escalen correctamente desde los kilogramos de harina.

Casos mínimos:

- Factor entero.
- Factor decimal.
- Redondeo controlado.

### 2. Costo promedio de inventario

Verifica una entrada valorizada sobre inventario existente y el costo resultante.

### 3. Tarifa mensual sugerida

Verifica:

- Distribución negocio/hogar.
- División por kilogramos procesados.
- Caso sin historial que exige tarifa manual.

### 4. Conversión de paquetes

Verifica que armar y desarmar conserve cantidad equivalente y costo total.

No se añadirán más pruebas unitarias por anticipación; se agregarán cuando aparezca una regla de cálculo realmente independiente.

## Nivel 3: pruebas funcionales críticas

La suite funcional inicial se limita a los siguientes recorridos:

### 1. Autorización básica

- Un usuario sin permiso no ejecuta una acción protegida.
- Una excepción autorizada conserva solicitante, autorizador y motivo.

### 2. Compra recibida

- Confirmar recepción aumenta inventario.
- Actualiza costo promedio.
- Una compra a crédito conserva su cuenta por pagar.

### 3. Producción completada

- Descuenta ingredientes.
- Ingresa varios productos.
- Registra merma y costos.
- No permite completar sin tarifa mensual efectiva.

### 4. Venta y cartera

- Una venta de contado reduce inventario y aumenta caja.
- Una venta a crédito crea saldo por cobrar.
- Un abono reduce cartera sin crear otra venta.

### 5. Caja menor

- Apertura, movimientos y cierre calculan correctamente el saldo esperado y la diferencia.

### 6. Solicitud pagada desde negocio

- Registra salida del negocio.
- Clasifica el pago como mano de obra.
- Registra entrada para el habitante.
- No duplica el gasto.

### 7. Paquete durante una venta

- Si faltan unidades y existen paquetes, desarmar y vender ocurre de forma consistente.
- Si la venta falla, no queda una conversión parcial.

### 8. Inventario negativo autorizado

- Sin autorización no continúa.
- Con autorización registra el saldo negativo y la incidencia.

Este conjunto es deliberadamente pequeño. Las variantes triviales se comprobarán manualmente.

## Nivel 4: lista manual por entrega

Antes de considerar estable una fase se revisará:

1. Inicio y cierre de sesión.
2. Menú y permisos visibles según rol.
3. Creación y edición de un borrador del módulo entregado.
4. Confirmación del flujo principal.
5. Mensajes de validación comprensibles.
6. Vista desde computador y teléfono.
7. Enlace correcto entre documento y movimientos generados.
8. Ausencia de errores visibles en logs durante el recorrido.

La lista se adapta al módulo; no obliga a repetir todos los flujos de la aplicación en cada cambio.

## Verificación antes del primer despliegue

- Ejecutar todas las pruebas esenciales.
- Ejecutar migraciones desde cero contra MySQL.
- Probar inicio de sesión por HTTPS.
- Completar manualmente una compra, producción, pedido, venta y cierre de caja.
- Crear y descargar un respaldo.
- Verificar acceso privado a adjuntos.
- Confirmar que un usuario habitante no pueda consultar datos de otro habitante.

## Pruebas excluidas inicialmente

- Cobertura exhaustiva de navegadores.
- Pruebas de carga a gran escala.
- Automatización visual de todas las pantallas.
- Pruebas automáticas de cada combinación de permisos.
- Pruebas de Capacitor antes de iniciar esa fase.
- Pruebas de integración con DIAN o servicios no incluidos.

## Cuándo agregar una prueba

Se agrega una prueba nueva únicamente cuando:

- Protege dinero, inventario, costos o permisos.
- Una regla posee cálculo no trivial.
- Corrige un defecto que podría reaparecer.
- Una integración externa requiere asegurar su contrato.
- Una migración de datos implica riesgo real.

