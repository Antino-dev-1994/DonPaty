<?php

namespace App\Modules\Launch\Application;

use App\Modules\Backups\Domain\Models\BackupRun;
use App\Modules\CostAccounting\Domain\Enums\CostPeriodStatus;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\Launch\Domain\Models\LaunchConfiguration;

class LaunchReadinessQuery
{
    public function execute(LaunchConfiguration $configuration): array
    {
        $cutoff = $configuration->cutoff_at;
        $attestations = $configuration->attestations ?? [];

        return [
            $this->item('owner', 'Propietario activo configurado', \App\Models\User::query()->where('status', 'active')->whereHas('roles', fn ($query) => $query->where('name', 'owner'))->exists(), '/users'),
            $this->item('inventory', 'Conteo e inventario inicial revisados', (bool) ($attestations['inventory'] ?? false), '/inventory/adjustments'),
            $this->item('balances', 'Caja, bancos y cuentas del hogar conciliados', (bool) ($attestations['balances'] ?? false), '/finance'),
            $this->item('obligations', 'Cartera, obligaciones y deudas abiertas cargadas', (bool) ($attestations['obligations'] ?? false), '/reports'),
            $this->item('orders', 'Pedidos y anticipos pendientes cargados', (bool) ($attestations['orders'] ?? false), '/orders'),
            $this->item('permissions', 'Usuarios y permisos revisados', (bool) ($attestations['permissions'] ?? false), '/roles'),
            $this->item('cost_period', 'Periodo de costos de la fecha de corte abierto', $cutoff && CostPeriod::query()->where('year', $cutoff->year)->where('month', $cutoff->month)->where('status', CostPeriodStatus::Open)->exists(), '/cost-periods'),
            $this->item('backup', 'Respaldo reciente verificado', BackupRun::query()->where('status', 'completed')->where('verification_status', 'valid')->where('created_at', '>=', now()->subDay())->exists(), '/backups'),
        ];
    }

    private function item(string $key, string $label, bool $complete, string $url): array
    {
        return compact('key', 'label', 'complete', 'url');
    }
}
