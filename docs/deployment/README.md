# Despliegue de DonPaty

Este documento prepara un despliegue portable. La preselección de VPS y el uso inicial de un dominio gratuito están documentados en [INFRASTRUCTURE.md](INFRASTRUCTURE.md).

## Requisitos del servidor

- PHP 8.4 con BCMath, cURL, Intl, Mbstring, OpenSSL, PDO MySQL y Zip.
- MySQL 8.0 o 8.4 con zona horaria UTC y juego de caracteres `utf8mb4`.
- Servidor web con HTTPS y redirección permanente de HTTP a HTTPS.
- Directorio persistente para `storage/app/private`, salvo que adjuntos y respaldos usen S3.
- Cliente `mysqldump` y `mysql` de la misma familia del servidor.
- Cron capaz de ejecutar el programador de Laravel cada minuto.
- Node 24.5.0 únicamente durante la compilación; no es necesario en ejecución si se despliegan los assets compilados.

## Variables

Partir de `.env.production.example`. Los secretos se configuran en el proveedor y nunca se guardan en Git.

Decisiones obligatorias antes del lanzamiento:

- `APP_URL`, `SESSION_DOMAIN` y DNS deben usar el dominio real.
- `TRUSTED_PROXIES` debe contener solo las redes del balanceador o `*` únicamente cuando el proveedor aísle el servidor de acceso directo.
- `DB_USERNAME` debe poseer privilegios solo sobre la base DonPaty.
- `ATTACHMENTS_DISK=local` exige conservar `storage/app/private` entre despliegues. Puede cambiarse a `attachments_s3` sin modificar código.
- `BACKUPS_DISK=backups_s3` debe usar un bucket privado externo al VPS y credenciales exclusivas con acceso únicamente a ese bucket.

## Procesos

El programador ejecuta `backups:create` diariamente a las 02:30, verifica el manifiesto y conserva 30 respaldos por defecto. Instalar una sola entrada cron:

```cron
* * * * * cd /ruta/donpaty && php artisan schedule:run >> /dev/null 2>&1
```

Actualmente los casos de uso no despachan trabajos diferidos obligatorios. No se requiere un worker permanente en esta versión; deberá agregarse antes de introducir trabajos `ShouldQueue`.

## Secuencia de publicación

```bash
php artisan down --retry=60
composer install --no-dev --classmap-authoritative --no-interaction
npm ci
npm run build
php artisan migrate --force --no-interaction
php artisan optimize:clear
php artisan optimize
php artisan app:readiness --probe-storage
php artisan up
```

Si los assets se compilan en CI, se omiten `npm ci` y `npm run build` en el servidor. Nunca ejecutar `db:seed` completo automáticamente en producción.

## Restauración

Solo se restaura desde terminal y con el ULID mostrado en la pantalla de respaldos:

```bash
php artisan backups:restore ULID --confirm=RESTAURAR-ULID
```

El comando verifica el archivo, crea un respaldo previo, entra en mantenimiento y conserva un registro JSON externo de la restauración.
