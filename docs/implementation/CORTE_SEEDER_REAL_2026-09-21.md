# Corte para seeder real — 21 de septiembre de 2026

## Propósito

Este documento separa los datos confirmados en la base local de los datos que aún requieren validación antes de crear un seeder operativo reproducible. No se deben convertir movimientos de prueba en inventario, ventas o cartera reales.

## Datos confirmados en la base

| Grupo | Estado actual |
| --- | --- |
| Usuarios | Kevin Andres Patiño Grimaldos y Maria Paola Peñaloza |
| Apertura del negocio | Efectivo $257.300 y Nequi $42.300, fecha 2026-09-21 |
| Catálogo base | 16 materias primas/insumos/empaques y Pan tajado 410 g |
| Receta base | Pan tajado de 6 kg de harina, rendimiento estándar 21,6 unidades |
| Proveedor | `gas pais`, documento `1`, plazo de 1 día |
| Compra de gas | $220.000 a crédito; abono $120.000 y saldo $100.000 |
| Recepción de gas | Pendiente; no hay inventario de gas de bombona recibido |

## Inventario inicial registrado

| Insumo | Cantidad | Costo unitario registrado |
| --- | ---: | ---: |
| Harina de trigo | 5 kg | $2.320/kg |
| Azúcar | 2 kg | $3.100/kg |
| Sal | 0,2 kg | $1.900/kg |
| Mantequilla hidrogenada | 1 kg | $3.700/kg |
| Esencia de mantequilla | 100 ml | $20/ml |
| Esencia de vainilla | 300 ml | $20/ml |
| Levadura | 200 g | $15/g |
| Mantequilla Astra | 0,5 kg | $11.000/kg |
| Empaste Gourmet | 0,35 kg | $14.000/kg |

> Nota: el costo de sal registrado es $1.900/kg, distinto al valor informado anteriormente de $2.800/kg. El conteo y costo físico confirmado deben definir el seeder final.

## Movimientos que requieren confirmación

- Ajuste manual: entrada de 20 pan tajado a $2.000 por unidad, con nota `aaasas`.
- Cuatro ventas confirmadas: salida acumulada de 15 pan tajado.
- Saldo actual calculado: 5 pan tajado a $2.000 por unidad.

Estos movimientos no se incluirán en el seeder real hasta confirmar que no fueron pruebas.

## Información pendiente

### Bebidas recibidas

| Campo | Coca-Cola 2 L | Soda 400 ml |
| --- | --- | --- |
| Proveedor | Pendiente | Pendiente |
| Factura/remisión | Pendiente | Pendiente |
| Cantidad recibida | Pendiente | Pendiente |
| Costo por unidad o total | Pendiente | Pendiente |
| Precio de venta | Pendiente | Pendiente |
| Forma de pago | Pendiente | Pendiente |

### Decisiones pendientes

1. Confirmar si el ajuste de 20 tajados y las ventas de 15 tajados son operaciones reales o pruebas.
2. Confirmar el costo físico correcto de la sal.
3. Confirmar si la bombona ya fue recibida; si sí, registrar su recepción antes de producir.
4. Entregar datos de Coca-Cola, Soda y cualquier producto adicional existente hoy.

## Seeder final propuesto

El seeder real se ejecutará una sola vez y será idempotente. Incluirá únicamente:

1. Usuarios operativos.
2. Catálogo de materias primas, empaques, productos terminados y bebidas realmente existentes.
3. Proveedores reales.
4. Apertura financiera e inventario inicial confirmado.
5. Compras y recepciones reales.
6. Producción y ventas reales, solo después de validar los movimientos pendientes.

Los seeders `Demo*`, `DevelopmentOwnerSeeder` y los seeders operativos de ejemplo permanecerán fuera de la ejecución automática.
