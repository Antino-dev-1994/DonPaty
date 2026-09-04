<?php

namespace App\Console\Commands;

use App\Support\Deployment\LanReadinessReport;
use Illuminate\Console\Command;

final class CheckLanReadinessCommand extends Command
{
    protected $signature = 'app:lan-readiness';
    protected $description = 'Valida DonPaty antes de permitir acceso desde la red local';

    public function handle(LanReadinessReport $report): int
    {
        $checks = $report->execute();
        $this->table(['Estado', 'Comprobación', 'Detalle'], array_map(
            fn (array $check) => [strtoupper($check['level']), $check['check'], $check['message']],
            $checks,
        ));

        return collect($checks)->contains('level', 'error') ? self::FAILURE : self::SUCCESS;
    }
}
