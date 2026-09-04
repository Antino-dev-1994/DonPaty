<?php

namespace App\Console\Commands;

use App\Support\Deployment\LanAccessUrl;
use App\Support\Deployment\LanReadinessReport;
use Illuminate\Console\Command;

final class ServeLanCommand extends Command
{
    protected $signature = 'app:lan';
    protected $description = 'Inicia DonPaty para los dispositivos de la misma red local';

    public function handle(LanReadinessReport $report): int
    {
        $checks = $report->execute();

        if (collect($checks)->contains('level', 'error')) {
            $this->components->error('La configuración LAN todavía no está lista.');
            $this->call('app:lan-readiness');

            return self::FAILURE;
        }

        $accessUrl = LanAccessUrl::from((string) config('app.url'));

        if ($accessUrl === null) {
            return self::FAILURE;
        }

        $this->components->info("DonPaty estará disponible en {$accessUrl->value}");
        $this->components->warn('El modo inicial usa HTTP: úsalo únicamente en tu Wi-Fi privado y confiable.');
        $this->components->warn('No abras ni redirijas este puerto desde el router hacia Internet.');

        return $this->call('serve', [
            '--host' => $accessUrl->host,
            '--port' => $accessUrl->port,
            '--no-reload' => true,
        ]);
    }
}
