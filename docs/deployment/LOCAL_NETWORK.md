# DonPaty en la red local

Esta es la prioridad de despliegue actual. El computador principal conserva la aplicación y la base SQLite; celulares, tabletas y computadores conectados al mismo Wi-Fi entran mediante la IPv4 privada del equipo.

## Condiciones

- El computador servidor debe permanecer encendido, conectado a la misma red y sin suspensión mientras se use DonPaty.
- La red debe ser privada y estar protegida con WPA2 o WPA3.
- El modo inicial usa HTTP y no ofrece cifrado extremo a extremo dentro de la LAN. Solo debe utilizarse en una red doméstica confiable; no ingreses desde redes públicas o de invitados.
- No crear redirección de puertos en el router. Este modo no debe exponerse directamente a Internet.
- Conviene reservar la IPv4 del computador en el DHCP del router para evitar que la dirección cambie.
- Cada persona ingresa con su propio usuario; no compartir la cuenta propietaria.

## Configuración inicial

1. Ejecuta `ipconfig` y localiza la **Dirección IPv4** del adaptador Wi-Fi activo, normalmente `192.168.x.x`.
2. Conserva tu `APP_KEY` y tu base actuales. En el `.env` existente aplica los valores de `.env.lan.example`, reemplazando `192.168.1.100` por la IPv4 real. No copies un `APP_KEY` vacío sobre una instalación existente.
3. Usa `APP_ENV=lan`, `APP_DEBUG=false`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=false`, `DB_CONNECTION=sqlite` y `APP_URL=http://IP_REAL:8000`.
4. Con Node 24.5.0 compila los assets una vez y limpia configuraciones anteriores.

```bash
npm run build
php artisan optimize:clear
php artisan migrate
php artisan app:lan-readiness
```

## Firewall de Windows

En PowerShell **como administrador**, crea una regla limitada al perfil privado:

```powershell
New-NetFirewallRule -DisplayName "DonPaty LAN 8000" -Direction Inbound -Action Allow -Protocol TCP -LocalPort 8000 -Profile Private
```

Si luego dejas de usar este modo, elimínala:

```powershell
Remove-NetFirewallRule -DisplayName "DonPaty LAN 8000"
```

No autorices el puerto para redes públicas.

## Uso diario

En una terminal deja ejecutándose:

```bash
php artisan app:lan
```

En una segunda terminal deja activo el programador, responsable del respaldo diario:

```bash
php artisan schedule:work
```

Los demás dispositivos abren la dirección mostrada, por ejemplo `http://192.168.1.50:8000`. El comando se detiene con `Ctrl+C`.

El servidor escucha únicamente en la IPv4 privada indicada por `APP_URL`, no en todas las interfaces del computador. Si esa dirección deja de pertenecer al equipo, el servidor no podrá enlazarla y deberá corregirse el `.env`.

## Respaldo y recuperación

En el modo inicial, adjuntos, SQLite y ZIP de respaldo permanecen en el computador principal. El sistema crea el respaldo programado, pero al menos una copia verificada debe trasladarse periódicamente a una memoria o almacenamiento externo; guardar original y copia en el mismo disco no protege ante daño o pérdida del equipo.

## Diagnóstico rápido

- Si el servidor abre localmente pero otro dispositivo no: confirma misma red, perfil **Privado** de Windows, regla de firewall y que el router no tenga aislamiento de clientes.
- Si cambió la IPv4: actualiza `APP_URL`, ejecuta `php artisan optimize:clear` y vuelve a iniciar `app:lan`.
- Si la interfaz aparece sin estilos: ejecuta `npm run build` con Node 24.5.0 y verifica de nuevo `app:lan-readiness`.
- Si aparece un aviso de migraciones: detén el servidor, ejecuta `php artisan migrate` y vuelve a iniciar.
