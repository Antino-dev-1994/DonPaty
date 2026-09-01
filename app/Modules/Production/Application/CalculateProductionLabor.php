<?php

namespace App\Modules\Production\Application;

use App\Modules\Identity\Application\UseAuthorization;
use App\Modules\Production\Application\Data\CompleteProductionData;
use App\Modules\Production\Domain\Enums\LaborMethod;
use App\Modules\Production\Domain\Models\ProductionOrder;
use DomainException;

class CalculateProductionLabor
{
    public function __construct(
        private readonly UseAuthorization $useAuthorization,
        private readonly ProductionAuthorizationGuard $authorizationGuard,
    ) {}

    public function execute(ProductionOrder $order, CompleteProductionData $data): int
    {
        return match ($order->labor_method) {
            LaborMethod::StandardPerKilogram => $this->standard($order),
            LaborMethod::ActualHours => $this->hours($order, $data),
            LaborMethod::AuthorizedManual => $this->manual($order, $data),
        };
    }

    private function standard(ProductionOrder $order): int
    {
        $rate = $order->costPeriod->standard_labor_rate_per_kg;
        if ($rate <= 0) throw new DomainException('El periodo no tiene tarifa estándar de mano de obra.');
        $total = (int) round((float) $order->flour_quantity * $rate);
        $order->laborEntries()->create(['person_id' => $order->responsible_person_id, 'method' => LaborMethod::StandardPerKilogram, 'flour_rate' => $rate, 'total_amount' => $total]);
        return $total;
    }

    private function hours(ProductionOrder $order, CompleteProductionData $data): int
    {
        if ($data->laborHours === []) throw new DomainException('Registra al menos una persona y sus horas reales.');
        $total = 0;
        foreach ($data->laborHours as $line) {
            if (bccomp($line->hours, '0', 4) <= 0 || $line->hourlyRate < 0) throw new DomainException('Las horas deben ser positivas y la tarifa válida.');
            $amount = (int) round((float) $line->hours * $line->hourlyRate); $total += $amount;
            $order->laborEntries()->create(['person_id' => $line->personId, 'method' => LaborMethod::ActualHours, 'hours' => $line->hours, 'hourly_rate' => $line->hourlyRate, 'total_amount' => $amount]);
        }
        return $total;
    }

    private function manual(ProductionOrder $order, CompleteProductionData $data): int
    {
        if ($data->manualLaborAmount === null || $data->manualLaborAmount < 0 || trim((string) $data->manualLaborReason) === '') throw new DomainException('El valor manual requiere monto y motivo.');
        $authorization = $this->authorizationGuard->ensureUsableFor(
            $data->manualLaborAuthorization,
            $order,
            'production.authorize-manual-labor',
        );
        $this->useAuthorization->execute($authorization);
        $order->update(['manual_labor_authorization_id' => $authorization->id]);
        $order->laborEntries()->create(['person_id' => $order->responsible_person_id, 'method' => LaborMethod::AuthorizedManual, 'manual_amount' => $data->manualLaborAmount, 'total_amount' => $data->manualLaborAmount, 'reason' => $data->manualLaborReason]);
        return $data->manualLaborAmount;
    }
}
