# Definición de terminado y lanzamiento

## Terminado para una historia

Una historia se considera terminada cuando:

- Cumple sus criterios de aceptación acordados.
- La regla reside en el módulo y capa correctos.
- Los permisos se validan en servidor.
- Los errores presentan mensajes comprensibles.
- Las operaciones críticas son transaccionales e idempotentes cuando aplica.
- El diseño funciona en escritorio y teléfono para el flujo principal.
- Se añadió únicamente la prueba básica necesaria según la estrategia acordada.
- No quedan errores conocidos que alteren dinero, inventario, costos o seguridad.
- La documentación se actualizó si cambió una decisión.

## Terminado para una entrega

Además de las historias:

- Las migraciones funcionan desde una base vacía.
- Los datos anteriores siguen siendo compatibles.
- El flujo demostrable de la entrega funciona de extremo a extremo.
- Se ejecutaron las comprobaciones automáticas rápidas.
- Se realizó la lista manual corta.
- Los permisos de al menos propietario y rol operativo fueron revisados.
- Los documentos creados enlazan correctamente con inventario, finanzas o auditoría cuando corresponde.
- El usuario validó el comportamiento funcional principal.

No se exige porcentaje de cobertura ni automatización completa de navegador.

## Terminado para la primera versión productiva

- Todas las entregas necesarias para operar fueron aceptadas.
- MySQL fue utilizado en un ambiente de ensayo.
- La carga inicial fue revisada.
- HTTPS y dominio funcionan.
- Los adjuntos son privados.
- Los respaldos automáticos funcionan y uno fue restaurado en ensayo.
- Los permisos fueron revisados con usuarios reales.
- Compra, producción, pedido, venta, abono, caja y gasto se probaron manualmente.
- El resultado diario puede rastrearse hasta sus documentos.
- No existen errores abiertos de severidad crítica o alta.
- Existe un procedimiento de soporte y recuperación.

## Ambientes

### Desarrollo

- Código local.
- SQLite.
- Datos ficticios.
- Herramientas de depuración habilitadas únicamente localmente.

### Ensayo

- Configuración semejante a producción.
- MySQL.
- HTTPS.
- Datos ficticios o copia anonimizada cuando resulte necesario.
- Validación de migraciones y despliegues.

### Producción

- MySQL.
- HTTPS.
- Datos reales.
- Depuración deshabilitada.
- Almacenamiento persistente.
- Respaldos automáticos.
- Logs protegidos.

## Preparación del lanzamiento

### Infraestructura

- Elegir proveedor y región apropiada.
- Configurar dominio y DNS.
- Configurar certificado HTTPS.
- Crear base MySQL y usuario con privilegios mínimos.
- Configurar almacenamiento de adjuntos.
- Configurar programador de tareas.
- Configurar proceso de trabajos diferidos si es necesario.
- Configurar respaldos fuera de la misma instancia principal.

### Seguridad

- Cambiar credenciales temporales.
- Confirmar secretos fuera del repositorio.
- Revisar propietarios y administradores.
- Revocar usuarios de prueba.
- Revisar permisos de almacenamiento.
- Verificar que depuración esté deshabilitada.
- Probar recuperación de acceso.

### Datos

- Definir fecha de corte.
- Completar conteo de inventario.
- Validar saldos de caja, bancos, cartera y obligaciones.
- Abrir periodo mensual de costos.
- Cargar pedidos pendientes.
- Realizar respaldo antes de iniciar operación.

## Procedimiento de despliegue

```text
Crear respaldo previo
-> poner la aplicación en modo de mantenimiento cuando sea necesario
-> desplegar código
-> instalar dependencias bloqueadas
-> ejecutar migraciones
-> compilar frontend
-> limpiar y reconstruir cachés apropiadas
-> reiniciar procesos necesarios
-> salir de mantenimiento
-> ejecutar comprobación de humo
```

El comando concreto dependerá del proveedor elegido y se documentará antes del primer despliegue.

## Comprobación de humo posterior

1. Abrir dominio por HTTPS.
2. Iniciar sesión como usuario de prueba autorizado.
3. Cargar tablero.
4. Consultar inventario.
5. Crear y eliminar un borrador no crítico.
6. Verificar conexión MySQL.
7. Verificar escritura y lectura de un adjunto de prueba.
8. Verificar estado del programador y respaldos.
9. Revisar logs por errores inesperados.

No se ejecutan transacciones financieras ficticias en producción después de la fecha de corte.

## Estrategia de adopción

Se recomienda:

1. Capacitar primero al propietario y pareja.
2. Cargar inventario y cuentas.
3. Realizar producciones y ventas de ensayo antes de la fecha de corte.
4. Operar un periodo corto de validación paralela, recomendado de 3 a 7 días.
5. Comparar caja, inventario y ventas diariamente.
6. Corregir configuración antes de depender exclusivamente del sistema.
7. Declarar el inicio oficial.

La operación paralela no debe prolongarse demasiado, porque duplicar registros aumenta errores.

## Plan de reversión de despliegue

Si una versión nueva falla:

- Detener nuevas confirmaciones cuando exista riesgo de inconsistencia.
- Conservar logs y evidencia.
- Restaurar código compatible.
- Revertir datos únicamente mediante respaldo o migración correctiva evaluada.
- No ejecutar `migrate:rollback` ciegamente sobre datos reales.
- Validar inventario, caja y asientos antes de reabrir.

## Soporte inicial

Durante los primeros días se revisará diariamente:

- Diferencias de caja.
- Inventarios negativos.
- Producciones con novedades altas.
- Pedidos atrasados.
- Pagos no aplicados.
- Fallos de respaldo.
- Errores del servidor.

## Criterios de aplazamiento del lanzamiento

El lanzamiento se aplaza si:

- No existe respaldo verificable.
- Los saldos iniciales no fueron revisados.
- Un usuario puede acceder a información no autorizada.
- Compra, producción o venta dejan movimientos parciales.
- No puede cerrarse caja correctamente.
- Los cálculos de costos acordados no coinciden con ejemplos reales.
- HTTPS, MySQL o almacenamiento persistente no son confiables.

## Capacitor

El cliente Capacitor se evalúa después de estabilizar la aplicación web. Tendrá su propia lista de publicación, firma, permisos de dispositivo y pruebas mínimas, sin alterar la definición de terminado de la primera versión web.

