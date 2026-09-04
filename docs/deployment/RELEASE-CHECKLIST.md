# Checklist del primer lanzamiento

## Infraestructura

- [ ] Proveedor, región y ambiente de ensayo definidos.
- [ ] MySQL creado con usuario de privilegios mínimos.
- [ ] Migraciones y escenarios críticos aprobados contra MySQL.
- [ ] Dominio, DNS, HTTPS y proxy confiable verificados.
- [ ] Persistencia de adjuntos confirmada después de reiniciar o redesplegar.
- [ ] Respaldos almacenados fuera de la instancia principal.
- [ ] Cron de `schedule:run` activo y último respaldo automático válido.
- [ ] `php artisan app:readiness --probe-storage` sin errores.

## Acceso y datos

- [ ] Contraseñas temporales cambiadas y usuarios de prueba bloqueados.
- [ ] Roles de propietario, pareja, empleados y habitantes revisados.
- [ ] Fecha y hora de corte acordadas.
- [ ] Inventario inicial contado y valorizado.
- [ ] Caja, bancos, cartera, obligaciones y cuentas del hogar conciliadas.
- [ ] Periodo de costos del mes abierto con servicios y tarifas.
- [ ] Pedidos pendientes cargados.
- [ ] Respaldo verificado creado inmediatamente antes de iniciar.

## Humo posterior

- [ ] Inicio de sesión y recuperación de acceso.
- [ ] Tablero y reportes según cada rol.
- [ ] Consulta de inventario y creación/eliminación de un borrador no crítico.
- [ ] Compra, recepción y pago de ensayo antes de la fecha de corte.
- [ ] Producción de ensayo con consumos, productos y costos.
- [ ] Pedido, anticipo, venta, devolución y cierre de caja de ensayo.
- [ ] Subida, descarga y eliminación de un adjunto de prueba.
- [ ] Auditoría, programador, respaldo y logs sin errores inesperados.

Las operaciones de ensayo se realizan antes de la fecha de corte o en el ambiente de ensayo. No se generan transacciones ficticias en producción después del inicio oficial.
