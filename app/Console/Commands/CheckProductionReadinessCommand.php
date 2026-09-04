<?php

namespace App\Console\Commands;

use App\Support\Deployment\ProductionReadinessReport;
use Illuminate\Console\Command;

class CheckProductionReadinessCommand extends Command
{
    protected $signature = 'app:readiness {--probe-storage : Comprueba escritura y lectura eliminando el archivo temporal}';
    protected $description = 'Valida la configuración mínima antes de publicar DonPaty';

    public function handle(ProductionReadinessReport $report): int
    {
        $checks = $report->execute((bool) $this->option('probe-storage'));
        $this->table(['Estado', 'Comprobación', 'Detalle'], array_map(
            fn (array $check) => [strtoupper($check['level']), $check['check'], $check['message']],
            $checks,
        ));

        return collect($checks)->contains('level', 'error') ? self::FAILURE : self::SUCCESS;
    }
}
