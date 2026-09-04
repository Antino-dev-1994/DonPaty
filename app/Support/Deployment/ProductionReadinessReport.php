<?php

namespace App\Support\Deployment;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProductionReadinessReport
{
    public function __construct(private readonly Migrator $migrator) {}

    /** @return list<array{level:string,check:string,message:string}> */
    public function execute(bool $probeStorage = false): array
    {
        $checks = [
            $this->expect('environment', app()->environment('production', 'staging'), 'APP_ENV debe ser production o staging.'),
            $this->expect('debug', ! config('app.debug'), 'APP_DEBUG debe ser false.'),
            $this->expect('https', str_starts_with((string) config('app.url'), 'https://'), 'APP_URL debe usar HTTPS.'),
            $this->expect('forced_https', (bool) config('app.force_https'), 'APP_FORCE_HTTPS debe ser true.'),
            $this->expect('database', in_array(config('database.default'), ['mysql', 'mariadb'], true), 'Producción debe usar MySQL o MariaDB.'),
            $this->expect('session_cookie', (bool) config('session.secure'), 'SESSION_SECURE_COOKIE debe ser true.'),
            $this->expect('session_encryption', (bool) config('session.encrypt'), 'SESSION_ENCRYPT debe ser true.'),
            $this->expect('app_key', filled(config('app.key')), 'APP_KEY no puede estar vacío.'),
            $this->expect('mail_transport', ! in_array(config('mail.default'), ['log', 'array'], true), 'Configura un correo real para recuperación de acceso.'),
            $this->expect('mail_sender', filled(config('mail.from.address')), 'MAIL_FROM_ADDRESS no puede estar vacío.'),
            $this->expect('zip_extension', class_exists(\ZipArchive::class), 'La extensión PHP Zip es obligatoria para respaldos.'),
            $this->expect('backups_external', config('backups.disk') !== 'local', 'Se recomienda guardar respaldos fuera de la instancia principal.', 'warning'),
        ];

        try {
            DB::select('select 1');
            $checks[] = $this->expect('database_connection', true, 'Conexión a base de datos disponible.');
            $files = $this->migrator->getMigrationFiles(database_path('migrations'));
            $pending = array_diff(array_keys($files), $this->migrator->getRepository()->getRan());
            $checks[] = $this->expect('migrations', $pending === [], count($pending).' migración(es) pendientes.');
        } catch (Throwable $exception) {
            $checks[] = $this->expect('database_connection', false, $exception->getMessage());
        }

        if ($probeStorage) {
            foreach (array_unique([(string) config('attachments.disk'), (string) config('backups.disk')]) as $disk) {
                $path = 'health/'.Str::uuid().'.txt';
                try {
                    $written = Storage::disk($disk)->put($path, 'DonPaty storage probe');
                    $read = $written && Storage::disk($disk)->get($path) === 'DonPaty storage probe';
                    Storage::disk($disk)->delete($path);
                    $checks[] = $this->expect("storage_{$disk}", $read, "Lectura y escritura en {$disk}.");
                } catch (Throwable $exception) {
                    $checks[] = $this->expect("storage_{$disk}", false, $exception->getMessage());
                }
            }
        }

        return $checks;
    }

    private function expect(string $check, bool $passes, string $message, string $failureLevel = 'error'): array
    {
        return ['level' => $passes ? 'ok' : $failureLevel, 'check' => $check, 'message' => $message];
    }
}
