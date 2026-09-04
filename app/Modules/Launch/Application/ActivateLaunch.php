<?php

namespace App\Modules\Launch\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Launch\Domain\Models\LaunchConfiguration;
use DomainException;

class ActivateLaunch
{
    public function __construct(
        private readonly LaunchReadinessQuery $readiness,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(LaunchConfiguration $configuration, User $actor): LaunchConfiguration
    {
        if ($configuration->status === 'active') {
            throw new DomainException('El inicio oficial ya está activo.');
        }
        if (! $configuration->cutoff_at) {
            throw new DomainException('Define primero la fecha de corte.');
        }
        $pending = collect($this->readiness->execute($configuration))->where('complete', false)->pluck('label');
        if ($pending->isNotEmpty()) {
            throw new DomainException('Faltan requisitos: '.$pending->join(', ').'.');
        }

        $before = $configuration->toArray();
        $configuration->update(['status' => 'active', 'activated_at' => now(), 'activated_by' => $actor->id]);
        $this->audit->execute('launch.activated', $configuration, $actor, $before, $configuration->fresh()->toArray());

        return $configuration->fresh();
    }
}
