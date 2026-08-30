# Configuración y datos iniciales

## Objetivo

Iniciar el uso del sistema con saldos y existencias correctos, evitando que información histórica incompleta distorsione costos, inventario o resultados.

## Principio de fecha de corte

Se elegirá una fecha y hora de inicio oficial. Todo lo anterior se resume en saldos de apertura; todo lo posterior se registra mediante operaciones normales.

```text
Antes de la fecha de corte
-> saldos iniciales documentados

Desde la fecha de corte
-> compras, producciones, ventas y gastos normales
```

No se recomienda reconstruir años de movimientos si no existen datos confiables.

## Asistente de configuración inicial

### Paso 1: Organización

- Nombre de la panadería.
- Datos básicos del hogar.
- Moneda COP.
- Zona horaria `America/Bogota`.
- Fecha de inicio operativo.
- Preferencias de consecutivos.

### Paso 2: Propietarios y usuarios

- Crear persona propietaria inicial.
- Crear usuario seguro.
- Registrar pareja y habitantes.
- Asignar clasificaciones y permisos.
- Crear empleados con o sin acceso.

### Paso 3: Cuentas financieras

- Caja principal.
- Caja menor.
- Bancos.
- Billeteras digitales.
- Cuentas personales controladas de habitantes.
- Cuentas de obligaciones relevantes.

### Paso 4: Saldos financieros de apertura

- Efectivo real contado.
- Saldos bancarios.
- Dinero en billeteras.
- Cuentas por cobrar existentes.
- Cuentas por pagar existentes.
- Deudas y préstamos.

Cada saldo genera un asiento de apertura con fecha, responsable y observación.

### Paso 5: Catálogo

- Materias primas.
- Insumos y empaques.
- Productos terminados.
- Presentaciones y paquetes.
- Unidades y conversiones.
- Existencias mínimas.

### Paso 6: Conteo inicial de inventario

Para cada presentación:

- Cantidad física contada.
- Costo unitario estimado o última compra confiable.
- Valor total.
- Responsable del conteo.
- Observaciones.

El conteo genera un documento de inventario inicial, no una edición directa del saldo.

### Paso 7: Clientes, proveedores y precios

- Clientes con cartera vigente.
- Proveedores con obligaciones vigentes.
- Lista minorista.
- Lista mayorista.
- Precios mínimos.
- Condiciones de crédito.

No es obligatorio cargar clientes ocasionales sin saldo o pedido pendiente.

### Paso 8: Recetas

- Masas base activas.
- Ingredientes y cantidades.
- Harina de referencia.
- Rendimiento esperado.
- Productos relacionados.
- Peso de masa por unidad.
- Merma esperada.

Cada receta se valida inicialmente con una producción de ensayo antes de confiar en sus costos.

### Paso 9: Periodo mensual

- Registrar servicios del periodo anterior.
- Distribuir porcentajes hogar/negocio.
- Calcular o ingresar tarifas iniciales.
- Abrir el periodo productivo actual.

### Paso 10: Verificación

- Efectivo total coincide con el conteo.
- Saldos bancarios coinciden con las fuentes disponibles.
- Inventario valorizado fue revisado.
- Cartera y obligaciones tienen tercero y vencimiento.
- Recetas activas escalan correctamente.
- El periodo de costos permite completar producción.

## Inventario inicial

El valor de apertura debe ser razonable y documentado. Si no se conoce un costo exacto:

1. Usar última compra confiable.
2. Si no existe, usar una estimación manual marcada como tal.
3. Actualizar el costo promedio con compras reales posteriores.

No se inventarán compras históricas para justificar el saldo.

## Cartera y obligaciones iniciales

Solo se cargan saldos abiertos:

- Cliente o proveedor.
- Documento de referencia opcional.
- Fecha original conocida.
- Vencimiento.
- Saldo pendiente.
- Observación de apertura.

Los importes ya pagados no se reconstruyen salvo necesidad explícita.

## Deudas y préstamos iniciales

- Tercero.
- Capital pendiente.
- Interés pendiente conocido.
- Próxima cuota.
- Calendario futuro.
- Cuenta financiera relacionada.

Debe evitarse registrar nuevamente como ingreso el dinero recibido antes de la fecha de corte.

## Producciones y pedidos abiertos

- Una producción iniciada antes de la fecha de corte se termina fuera del sistema o se carga como inventario inicial; no se migra parcialmente en la primera puesta en marcha.
- Los pedidos aún no entregados sí se cargan como pedidos abiertos.
- Los anticipos existentes se registran como saldos asociados al pedido.

## Método de carga

La primera versión permite carga manual controlada. Si el volumen real de artículos, clientes o saldos es alto, se diseñará una importación CSV específica antes del lanzamiento.

No se construirá un importador genérico sin conocer los datos fuente.

## Datos de demostración y datos reales

- Desarrollo puede usar seeders y factories claramente ficticios.
- Producción solo usa catálogos mínimos y datos ingresados durante la configuración.
- Nunca se copiarán datos ficticios al ambiente productivo.
- Los seeders de producción deben ser idempotentes para roles, permisos, unidades y cuentas base.

## Lista de información que deberá proporcionar el usuario

- Nombre e identidad visual básica.
- Personas y roles iniciales.
- Cuentas y saldos de apertura.
- Conteo de inventario y costos estimados.
- Proveedores y obligaciones vigentes.
- Clientes con cartera o pedidos abiertos.
- Productos, presentaciones y precios.
- Recetas activas.
- Facturas de gas y electricidad necesarias para abrir el periodo.
- Categorías y presupuestos iniciales del hogar.
- Deudas, préstamos y metas vigentes.

## Corrección de la carga inicial

- Antes del lanzamiento, los documentos de apertura pueden corregirse mientras estén en borrador.
- Después del inicio oficial, cualquier corrección usa ajuste o reversión documentada.
- No se editarán saldos directamente en base de datos.
