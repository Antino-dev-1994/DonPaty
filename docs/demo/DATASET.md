# Escenario demostrativo de DonPaty

Este escenario carga información conectada mediante los mismos casos de uso de la aplicación. No inserta saldos finales artificiales: las compras ingresan materia prima, las producciones la consumen y generan pan, y las ventas descuentan el producto terminado.

## Carga

Solo está habilitado en los ambientes `local`, `lan` y `testing`:

```bash
php artisan db:seed --class=DemoBakerySeeder
```

El comando es idempotente. Si encuentra `admin.demo@donpaty.local`, informa que el escenario ya está instalado y no duplica movimientos. Para regenerarlo desde cero debe usarse una base de datos de demostración limpia; no se debe borrar información real para volver a cargarlo.

## Accesos

Los tres usuarios usan la contraseña `DonPaty2026!`:

| Usuario | Correo | Condición |
| --- | --- | --- |
| Andrea Administradora | `admin.demo@donpaty.local` | Habitante y administradora |
| Patricia Panadera | `panadera.demo@donpaty.local` | Habitante, empleada y responsable de producción |
| Camila Habitante | `habitante.demo@donpaty.local` | Habitante |

La contraseña es exclusivamente demostrativa y debe cambiarse antes de usar estos usuarios con información real.

## Información incluida

- Materias primas: harina, agua para masa, azúcar, margarina, sal y levadura; además, bolsas para pan tajado.
- Una compra recibida y pagada al proveedor La Espiga por `$310.000 COP`.
- Una masa base versionada para pan cascarita y bolita, y una receta independiente para pan tajado.
- Un periodo mensual abierto con mano de obra estándar y recibos del mes anterior: luz `$350.000`, agua `$100.000` y gas `$80.000`, todos separados 70 % negocio y 30 % hogar.
- Dos producciones terminadas: 140 cascaritas, 110 bolitas y 22 panes tajados, con sus consumos, costos y mermas.
- Una venta minorista de contado por `$89.000` y una mayorista por `$97.000`, con abono de `$40.000` y cuenta por cobrar de `$57.000`.
- Inventario final disponible: 60 cascaritas, 60 bolitas y 12 panes tajados.
- Capital inicial del negocio, cuentas del hogar, presupuesto mensual y gastos familiares de servicios y alimentación.

Al cargarse durante el mes actual, las ventas, ingresos, egresos, utilidad, existencias y cuenta por cobrar aparecen inmediatamente en los tableros correspondientes.
