# Carga inicial operativa: pan tajado

## Propósito

`php artisan db:seed` carga únicamente los datos maestros necesarios para iniciar DonPaty. No carga movimientos de compra, costos, producción, ventas, datos demostrativos anteriores ni el propietario técnico de desarrollo.

## Datos creados

- Kevin Andres Patiño Grimaldos (`kevin.patino@donpaty.local`): único usuario con rol **Administrador**.
- Maria Paola Peñaloza (`maria.penaloza@donpaty.local`): rol **Operación comercial y producción**. Puede ejecutar compras, ventas, inventario, recetas y producción, pero no administrar usuarios ni aprobar sus propias autorizaciones.
- Contraseña inicial para ambas cuentas: `DonPaty2026!`. Debe cambiarse al primer ingreso.
- Catálogo de materias primas, insumos y empaque del pan tajado.
- Presentación de pan tajado de 410 g y precios: mayorista $2.600, normal $3.500 y público $4.000.
- Receta `DP-PAN-TAJADO-6KG`, con rendimiento estándar de 21,6 unidades; el consumo real de 22 bolsas queda documentado en la producción inicial.
- Las compras, costos, producciones, ventas y pagos se registran desde el sitio con información real y comprobantes del día de operación. No se generan mediante seeders.

## Receta base

La masa estándar pesa 10,729 kg antes de hornear. Incluye 6 kg de harina, 2,7 kg de agua, 6 huevos, azúcar, sal, Prodigio, mantequilla hidrogenada, esencia, color, levadura y antimoho. El rendimiento de 21,6 panes corresponde a 8,856 kg de producto de 410 g; la diferencia por horneado es evaporación esperada, no merma.

El consumo de Astra (200 g), Prodigio de picado (10 g, incorporado al total de 510 g de Prodigio) y una bolsa por pan hacen parte del costo. La harina extra del cilindrado debe registrarse siempre como consumo real con explicación.

## Ejecución

Para una base limpia:

```powershell
php artisan migrate:fresh --seed
```

Para cargar solo usuarios, catálogo, receta y precios en una base ya preparada:

```powershell
php artisan db:seed --class=DonPatyOperationalSeeder
```

La carga es segura para repetirse: evita duplicar usuarios, artículos, receta y precios.

## Seeders conservados, pero no automáticos

Los seeders anteriores se conservan para pruebas o demostraciones y no son llamados por `DatabaseSeeder`:

```powershell
php artisan db:seed --class=DemoBakerySeeder
php artisan db:seed --class=DevelopmentOwnerSeeder
```

Ejecutarlos en una base de operación real introduciría datos de prueba; úsalos solamente en una base aislada.
