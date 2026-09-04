# Decisión de infraestructura para DonPaty

Estado: preselección aprobada, contratación y dominio exacto pendientes. Información contrastada el 4 de septiembre de 2026.

## Arquitectura inicial

- Un VPS Linux en Miami o la región disponible con menor latencia hacia Colombia.
- Ubuntu LTS, Nginx, PHP 8.4, MySQL 8, cron y certificados TLS automáticos.
- Aplicación, MySQL y adjuntos privados en el VPS durante la primera etapa.
- Un bucket S3 compatible privado, fuera del VPS, para los respaldos diarios de DonPaty.
- Ambiente de ensayo con código y base independientes en `staging.<dominio>`; nunca comparte datos reales ni secretos con producción.
- DNS autoritativo externo, porque DigitalPlat FreeDomain solo delega nameservers.

La base mínima recomendada es 2 vCPU, 4 GB de RAM y 50 GB NVMe. Un VPS de 1 vCPU y 4 GB puede ejecutar la primera versión, pero las compilaciones, MySQL y un despliegue simultáneo competirán por CPU.

## Proveedores preseleccionados

| Alternativa | Recursos de referencia | Respaldo del proveedor | Precio publicado | Decisión |
| --- | --- | --- | --- | --- |
| Teramont EVPS 4GB | 2 vCore, 4 GB RAM, 60 GB NVMe, Miami | Diario, retención 14 días | USD 8/mes | Mejor relación costo/recursos; recomendado si sus términos, soporte y método de pago son aceptables al contratar. |
| Hostinger KVM 1 | 1 vCPU, 4 GB RAM, 50 GB NVMe | Semanal | COP 19.900/mes promocional; renovación publicada COP 38.900/mes | Proveedor alternativo con menor CPU y precio de renovación mayor. |
| Hostinger KVM 2 | 2 vCPU, 8 GB RAM, 100 GB NVMe | Semanal | COP 28.900/mes promocional; renovación publicada COP 50.900/mes | Alternativa con mayor holgura si se prioriza Hostinger. |

Los precios promocionales y renovaciones deben confirmarse en la pantalla de pago. Los snapshots del proveedor no sustituyen `backups:create`: una falla de cuenta, proveedor o VPS podría afectar servidor y snapshots a la vez.

Fuentes: [planes oficiales de Teramont](https://teramont.net/es/vps-hosting#pricing) y [planes VPS oficiales de Hostinger Colombia](https://www.hostinger.com/co/vps-servidor-web).

## Dominio gratuito inicial

Se permite iniciar con un nombre de [DigitalPlat FreeDomain](https://github.com/DigitalPlatDev/FreeDomain), preferiblemente corto y reconocible, por ejemplo `donpaty.<extensión-disponible>`. Las extensiones disponibles cambian y se eligen únicamente en el panel oficial.

Restricciones que forman parte del plan:

- El límite publicado es un dominio por cuenta; los subdominios sí pueden crearse desde el proveedor DNS.
- DigitalPlat no administra registros `A`, `AAAA`, `CNAME` o `TXT`: el dominio debe delegarse a nameservers autoritativos externos.
- No se asumirá que cualquier proveedor DNS acepta todas las extensiones gratuitas. La zona debe crearse y sus nameservers deben obtenerse antes de confirmar la delegación.
- La fecha y condiciones de renovación pueden cambiar. Se registrarán recordatorios a 90, 60, 30 y 7 días según la fecha que muestre el panel.
- El correo de recuperación de usuarios no debe depender de una dirección bajo este dominio gratuito.
- Se conservará un procedimiento para migrar a un dominio comercial sin cambiar la arquitectura de la aplicación.

Fuentes: [FAQ oficial](https://github.com/DigitalPlatDev/FreeDomain/blob/main/documents/domains/faq.md), [delegación de nameservers](https://github.com/DigitalPlatDev/FreeDomain/blob/main/documents/tutorial/platform/1.3-connect-nameservers.md) y [renovación](https://github.com/DigitalPlatDev/FreeDomain/blob/main/documents/tutorial/platform/1.4-status-and-renewal.md).

## Registros previstos

Una vez existan el dominio, el DNS autoritativo y la IP fija del VPS:

| Nombre | Tipo | Destino |
| --- | --- | --- |
| `@` | `A` | IPv4 pública del VPS |
| `www` | `CNAME` | dominio principal |
| `staging` | `A` | IPv4 del VPS mientras el ensayo comparta servidor |

El ambiente de ensayo se protegerá con autenticación adicional y no será indexable. HTTPS debe estar válido en los tres nombres antes de ingresar credenciales.

## Evidencia pendiente para cerrar E12

1. Elegir y contratar uno de los VPS preseleccionados.
2. Registrar el nombre exacto en DigitalPlat y anotar su fecha de expiración.
3. Elegir un DNS autoritativo que acepte esa extensión y verificar la delegación.
4. Crear el bucket privado externo y credenciales exclusivas para `backups_s3`.
5. Aprovisionar ensayo, validar MySQL y ejecutar `app:readiness --probe-storage`.
6. Realizar la carga inicial, comprobación de humo y activación controlada desde `/launch`.
