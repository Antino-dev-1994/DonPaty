<?php

namespace App\Modules\CostAccounting\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\CostAccounting\Application\Data\OpenCostPeriodData;
use App\Modules\CostAccounting\Domain\Enums\CostPeriodStatus;
use App\Modules\CostAccounting\Domain\Enums\UtilityType;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\CostAccounting\Domain\Services\OverheadRateCalculator;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OpenCostPeriod
{
    public function __construct(private readonly OverheadRateCalculator $rateCalculator, private readonly RecordAuditEvent $audit) {}

    public function execute(OpenCostPeriodData $data): CostPeriod
    {
        $types = array_values(array_unique(array_map(fn ($utility) => $utility->type->value, $data->utilities)));
        sort($types);
        $requiredTypes = [UtilityType::Electricity->value, UtilityType::Gas->value];
        sort($requiredTypes);
        if (count($data->utilities) !== 2 || $types !== $requiredTypes) {
            throw new DomainException('El periodo debe registrar una factura de electricidad y una de gas.');
        }

        return DB::transaction(function()use($data):CostPeriod{
            if(CostPeriod::query()->where('year',$data->year)->where('month',$data->month)->exists()) throw new DomainException('Ya existe un periodo para este mes.');
            $periodStart=Carbon::create($data->year,$data->month,1)->startOfDay(); $previousDate=$periodStart->copy()->subMonth();
            $previous=CostPeriod::query()->where('year',$previousDate->year)->where('month',$previousDate->month)->first(); $base=$previous?->processed_flour_quantity??'0';
            $period=CostPeriod::create(['year'=>$data->year,'month'=>$data->month,'status'=>CostPeriodStatus::Open,'processed_flour_quantity'=>0,'standard_labor_rate_per_kg'=>$data->standardLaborRatePerKg,'labor_rate_reason'=>$data->laborRateReason,'opened_at'=>now(),'opened_by'=>$data->opener->id]);
            foreach($data->utilities as $utility){
                if($utility->billedFrom->gt($utility->billedTo)||$utility->billedTo->gte($periodStart)) throw new DomainException('La factura debe corresponder a un intervalo anterior al periodo que se abre.');
                if(bccomp(bcadd($utility->businessPercentage,$utility->householdPercentage,4),'100',4)!==0) throw new DomainException('Los porcentajes de negocio y hogar deben sumar 100 %.');
                $businessAmount=(int)round($utility->totalAmount*((float)$utility->businessPercentage/100)); $householdAmount=$utility->totalAmount-$businessAmount;
                $record=$period->utilities()->create(['utility_type'=>$utility->type,'billed_from'=>$utility->billedFrom,'billed_to'=>$utility->billedTo,'paid_at'=>$utility->paidAt,'total_amount'=>$utility->totalAmount,'business_percentage'=>$utility->businessPercentage,'household_percentage'=>$utility->householdPercentage,'business_amount'=>$businessAmount,'household_amount'=>$householdAmount,'physical_consumption'=>$utility->physicalConsumption,'physical_consumption_unit'=>$utility->type->consumptionUnit(),'reference'=>$utility->reference]);
                $rate=$this->rateCalculator->calculate($businessAmount,$base,$utility->manualRate,$utility->overrideReason);
                $period->rates()->create(['cost_type'=>$utility->type,'suggested_rate'=>$rate['suggested'],'manual_rate'=>$rate['manual'],'effective_rate'=>$rate['effective'],'calculation_base_quantity'=>$base,'method'=>$rate['method'],'override_reason'=>$utility->overrideReason,'set_by'=>$data->opener->id]);
                $period->poolEntries()->create(['cost_type'=>$utility->type,'amount'=>$businessAmount,'source_type'=>$record->getMorphClass(),'source_id'=>$record->id,'effective_at'=>$utility->paidAt]);
            }
            $this->audit->execute('costs.period_opened',$period,$data->opener,after:$period->load(['utilities','rates'])->toArray());
            return $period->fresh(['utilities','rates','poolEntries']);
        });
    }
}
