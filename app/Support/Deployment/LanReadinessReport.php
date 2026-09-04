<?php

namespace App\Support\Deployment;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class LanReadinessReport
{
    public function __construct(
        private Migrator $migrator,
        private ReadinessCheckFactory $checks,
    ) {}

    /** @return list<array{level:string,check:string,message:string}> */
    public function execute(): array
    {
        $accessUrl = LanAccessUrl::from((string) config('app.url'));
        $checks = [
            $this->checks->make('environment', app()->environment('lan'), 'APP_ENV debe ser lan.'),
            $this->checks->make('debug', ! config('app.debug'), 'APP_DEBUG debe ser false.'),
            $this->checks->make('lan_url', $accessUrl !== null, 'APP_URL debe ser HTTP y usar la IPv4 privada del equipo, por ejemplo http://192.168.1.50:8000.'),
            $this->checks->make('forced_https', ! config('app.force_https'), 'APP_FORCE_HTTPS debe ser false en el servidor HTTP de la red local.'),
            $this->checks->make('app_key', filled(config('app.key')), 'APP_KEY no puede estar vacío.'),
            $this->checks->make('database', config('database.default') === 'sqlite', 'El despliegue LAN inicial debe usar SQLite.'),
            $this->checks->make('session_driver', config('session.driver') === 'database', 'SESSION_DRIVER debe ser database.'),
            $this->checks->make('session_encryption', (bool) config('session.encrypt'), 'SESSION_ENCRYPT debe ser true.'),
            $this->checks->make('session_cookie', ! config('session.secure'), 'SESSION_SECURE_COOKIE debe ser false mientras la red local use HTTP.'),
            $this->checks->make('assets', is_file(public_path('build/manifest.json')), 'Falta public/build/manifest.json; ejecuta npm run build.'),
            $this->checks->make('vite_server', ! is_file(public_path('hot')), 'El archivo public/hot debe eliminarse; la LAN usa assets compilados, no Vite dev server.'),
            $this->checks->make('private_storage', is_writable(storage_path('app/private')), 'storage/app/private debe existir y permitir escritura.'),
            $this->checks->make('backups_local', config('backups.disk') === 'local', 'Los respaldos LAN se guardarán en este equipo; copia periódicamente uno fuera del computador.', 'warning'),
        ];

        try {
            DB::select('select 1');
            $checks[] = $this->checks->make('database_connection', true, 'SQLite está disponible.');
            $files = $this->migrator->getMigrationFiles(database_path('migrations'));
            $pending = array_diff(array_keys($files), $this->migrator->getRepository()->getRan());
            $checks[] = $this->checks->make('migrations', $pending === [], count($pending).' migración(es) pendientes.');
        } catch (Throwable $exception) {
            $checks[] = $this->checks->make('database_connection', false, $exception->getMessage());
        }

        return $checks;
    }
}
