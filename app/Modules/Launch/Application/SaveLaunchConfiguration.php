<?php

namespace App\Modules\Launch\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Launch\Domain\Models\LaunchConfiguration;
use Carbon\CarbonImmutable;
use DomainException;

class SaveLaunchConfiguration
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(LaunchConfiguration $configuration, array $data, User $actor): LaunchConfiguration
    {
        if ($configuration->status === 'active') {
            throw new DomainException('La fecha de corte oficial ya fue activada y no puede editarse.');
        }
        $before = $configuration->toArray();
        $configuration->update([
            'cutoff_at' => CarbonImmutable::parse($data['cutoff_at'], config('regional.display_timezone'))->utc(),
            'notes' => $data['notes'] ?? null,
            'attestations' => collect($data['attestations'])->map(fn ($value): bool => (bool) $value)->all(),
        ]);
        $this->audit->execute('launch.configuration_saved', $configuration, $actor, $before, $configuration->fresh()->toArray());

        return $configuration->fresh();
    }
}
