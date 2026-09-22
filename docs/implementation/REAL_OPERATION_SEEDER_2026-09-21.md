# Carga real inicial — 21 de septiembre de 2026

`DonPatyRealOperationSeeder` deja lista una instalación nueva para comenzar a operar al día siguiente. Se ejecuta desde `DonPatyOperationalSeeder`, después de usuarios y catálogo.

## Datos cargados

- Kevin Andres Patiño Grimaldos como administrador y Maria Paola Peñaloza como responsable operativa.
- Apertura: $257.300 en efectivo y $42.300 en Nequi.
- Materias primas, insumos, empaque, pan tajado, bebidas, masa reservada y los dos tipos de gas.
- Inventario físico previo al lote, incluidas 105 bolsas para que, tras empacar 35 tajados, queden 70.
- Compra y recepción de 44 horneadas de bombona a $5.000. Se abonan $120.000 y quedan $100.000 a crédito.
- Compra de contado Coca-Cola, factura/remisión `00001`: 9 Coca-Cola 2 L por $41.598 y media caja de 6 Schweppes por $12.550. La media caja se convierte a unidades para venta.
- Producción real: 35 tajados y 0,509020 kg de masa reservada. Incluye los consumos adicionales de 265 g de harina, 26 g de Prodigio y un huevo.

## Costos

Las tarifas manuales de septiembre se calculan con los 250 kg de harina procesados en el período anterior:

- Agua: $120.000 × 50 % / 250 kg = $240/kg de harina.
- Luz: $350.000 × 50 % / 250 kg = $700/kg de harina.
- Gas de bombona: inventario físico, $5.000 por horneada.
- Gas de tubería: artículo inventariable de $5.000 por fritada. La receta de churros descontará una unidad por fritada; la factura de hogar queda al 100 % fuera del prorrateo del negocio.

El rango `2026-08-01` a `2026-08-31` se usa como período técnico de referencia para las tarifas manuales. Cuando estén los recibos, se debe corregir con sus fechas y consumos exactos.

## Materias primas sin comprobante

Las cantidades y costos conocidos de harina, azúcar, sal, grasas, huevos, esencias, color, levadura, antimoho, Astra, Gourmet y bolsas se cargan como inventario inicial. No se crea una compra ficticia porque no se informó proveedor, número de factura ni forma de pago.

## Ejecución

Primero valida el seeder en la base de pruebas:

```bash
php artisan test tests/Feature/DonPatyRealOperationSeederTest.php
```

Cuando la prueba pase, la carga definitiva requiere una base vacía porque elimina los registros de prueba existentes:

```bash
php artisan migrate:fresh --seed
```

Los seeders demostrativos y anteriores continúan disponibles por clase explícita, pero no son llamados por `DatabaseSeeder`.
