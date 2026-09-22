<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\CostAccounting\Application\Data\OpenCostPeriodData;
use App\Modules\CostAccounting\Application\Data\UtilityCostData;
use App\Modules\CostAccounting\Application\OpenCostPeriod;
use App\Modules\CostAccounting\Domain\Enums\UtilityType;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\Finance\Application\Data\RegisterBusinessOpeningData;
use App\Modules\Finance\Application\Data\SupplierPaymentData;
use App\Modules\Finance\Application\PaySupplierPayable;
use App\Modules\Finance\Application\RegisterBusinessOpening;
use App\Modules\Finance\Domain\Models\BusinessOpening;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\Payable;
use App\Modules\Identity\Application\CreateAuthorizationRequest;
use App\Modules\Identity\Application\DecideAuthorizationRequest;
use App\Modules\Identity\Domain\Enums\AuthorizationStatus;
use App\Modules\Inventory\Application\ConfirmInventoryAdjustment;
use App\Modules\Inventory\Application\ConvertPackage;
use App\Modules\Inventory\Application\CreateInventoryAdjustment;
use App\Modules\Inventory\Application\Data\InventoryAdjustmentData;
use App\Modules\Inventory\Domain\Enums\PackageConversionType;
use App\Modules\Inventory\Domain\Models\InventoryAdjustment;
use App\Modules\People\Domain\Models\Person;
use App\Modules\Production\Application\CompleteProduction;
use App\Modules\Production\Application\Data\ActualConsumptionData;
use App\Modules\Production\Application\Data\ActualOutputData;
use App\Modules\Production\Application\Data\CompleteProductionData;
use App\Modules\Production\Application\Data\PlanProductionData;
use App\Modules\Production\Application\Data\PlannedOutputData;
use App\Modules\Production\Application\PlanProduction;
use App\Modules\Production\Application\StartProduction;
use App\Modules\Production\Domain\Enums\LaborMethod;
use App\Modules\Production\Domain\Models\ProductionOrder;
use App\Modules\Purchasing\Application\CreatePurchase;
use App\Modules\Purchasing\Application\Data\PurchaseData;
use App\Modules\Purchasing\Application\Data\PurchaseLineData;
use App\Modules\Purchasing\Application\Data\PurchaseReceiptData;
use App\Modules\Purchasing\Application\Data\PurchaseReceiptLineData;
use App\Modules\Purchasing\Application\Data\SupplierData;
use App\Modules\Purchasing\Application\ReceivePurchase;
use App\Modules\Purchasing\Application\RegisterSupplier;
use App\Modules\Purchasing\Domain\Enums\PurchasePaymentCondition;
use App\Modules\Purchasing\Domain\Models\Purchase;
use App\Modules\Purchasing\Domain\Models\SupplierProfile;
use App\Modules\Recipes\Domain\Models\RecipeVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Reproduces the confirmed operating facts from 2026-09-21.
 *
 * Raw-material purchases without a supplier or receipt are intentionally loaded as
 * opening inventory. This preserves their physical quantity and known cost without
 * inventing a supplier, payable, or cash payment.
 */
class DonPatyRealOperationSeeder extends Seeder
{
    private const DATE = '2026-09-21';
    private const INITIAL_INVENTORY_REASON = 'Inventario físico previo a la producción real del 21 de septiembre de 2026.';
    private const GAS_DOCUMENT = 'GAS-20260921';
    private const COCA_COLA_DOCUMENT = '00001';

    public function run(): void
    {
        $kevin = User::query()->where('email', DonPatyPeopleSeeder::KEVIN_EMAIL)->sole();
        $maria = User::query()->where('email', DonPatyPeopleSeeder::MARIA_EMAIL)->sole();
        $day = Carbon::parse(self::DATE)->startOfDay();

        $this->opening($kevin, $day);
        $this->initialInventory($kevin, $day);
        $this->openCostPeriod($kevin, $day);
        $this->receiveGasBomb($maria, $day);
        $this->receiveCocaCola($maria, $day);
        $this->completeTajadoProduction($kevin, $maria, $day);
    }

    private function opening(User $kevin, Carbon $day): void
    {
        if (BusinessOpening::query()->whereDate('opened_on', $day)->exists()) {
            return;
        }

        app(RegisterBusinessOpening::class)->execute(new RegisterBusinessOpeningData(
            openedOn: $day,
            cashAmount: 257300,
            nequiAmount: 42300,
            notes: 'Saldo inicial confirmado: efectivo incluye $53.000 de alcancía; Nequi se mantiene separado.',
            opener: $kevin,
        ));
    }

    private function initialInventory(User $kevin, Carbon $day): void
    {
        if (InventoryAdjustment::query()->where('reason', self::INITIAL_INVENTORY_REASON)->exists()) {
            return;
        }

        $quantities = [
            'DP-HARINA-COMPRA' => ['30', 2287],
            'DP-AGUA-COMPRA' => ['4.2', 0],
            'DP-AZUCAR-COMPRA' => ['3', 3233],
            'DP-SAL-COMPRA' => ['0.35', 2800],
            'DP-MANTEQUILLA-PRODIGIO-COMPRA' => ['2.5', 8000],
            'DP-MANTEQUILLA-HIDROGENADA-COMPRA' => ['1', 3700],
            'DP-HUEVOS-COMPRA' => ['1', 10000],
            'DP-ESENCIA-MANTEQUILLA-COMPRA' => ['100', 20],
            'DP-ESENCIA-VAINILLA-COMPRA' => ['300', 20],
            'DP-COLOR-COMPRA' => ['200', 10],
            'DP-LEVADURA-COMPRA' => ['200', 15],
            'DP-ANTIMOHO-COMPRA' => ['0.5', 18800],
            'DP-MANTEQUILLA-ASTRA-COMPRA' => ['0.45', 11000],
            'DP-EMPASTE-GOURMET-COMPRA' => ['0.35', 14000],
            'DP-BOLSA-TAJADO-COMPRA' => ['105', 130],
        ];

        $adjustment = app(CreateInventoryAdjustment::class)->execute(new InventoryAdjustmentData(
            type: 'initial',
            effectiveAt: $day->copy()->setTime(6, 0),
            reason: self::INITIAL_INVENTORY_REASON,
            creator: $kevin,
            lines: collect($quantities)->map(fn (array $line, string $sku) => [
                'presentation_id' => $this->presentation($sku)->id,
                'counted_quantity' => $line[0],
                'unit_cost' => $line[1],
            ])->values()->all(),
        ));
        app(ConfirmInventoryAdjustment::class)->execute($adjustment, $kevin);
    }

    private function openCostPeriod(User $kevin, Carbon $day): void
    {
        if (CostPeriod::query()->where('year', 2026)->where('month', 9)->exists()) {
            return;
        }

        $from = Carbon::parse('2026-08-01');
        $to = Carbon::parse('2026-08-31');
        app(OpenCostPeriod::class)->execute(new OpenCostPeriodData(
            year: 2026,
            month: 9,
            opener: $kevin,
            utilities: [
                new UtilityCostData(UtilityType::Electricity, $from, $to, Carbon::parse('2026-09-10'), 350000, '50', '50', null, 'RECIBO-LUZ-PENDIENTE', 700, 'Tarifa manual: $175.000 del negocio / 250 kg de harina del período anterior.'),
                new UtilityCostData(UtilityType::Gas, $from, $to, $day, 0, '0', '100', null, 'GAS-TUBERIA-HOGAR', 0, 'La factura de gas de tubería pertenece al hogar; sus fritadas se controlan como insumo virtual por receta.'),
                new UtilityCostData(UtilityType::Water, $from, $to, Carbon::parse('2026-09-16'), 120000, '50', '50', null, 'RECIBO-AGUA-PENDIENTE', 240, 'Tarifa manual: $60.000 del negocio / 250 kg de harina del período anterior.'),
            ],
            standardLaborRatePerKg: 0,
            laborRateReason: 'La mano de obra se registra por producción mediante autorización manual.',
        ));
    }

    private function receiveGasBomb(User $maria, Carbon $day): void
    {
        if (Purchase::query()->where('supplier_document_number', self::GAS_DOCUMENT)->exists()) {
            return;
        }

        $supplier = $this->supplier('Gas Pais', 'CC', '1', 1, 'Proveedor de bombona para horno.');
        $purchase = app(CreatePurchase::class)->execute(new PurchaseData(
            supplierPersonId: $supplier->person_id,
            supplierDocumentNumber: self::GAS_DOCUMENT,
            issuedAt: $day,
            dueAt: Carbon::parse('2026-09-22'),
            paymentCondition: PurchasePaymentCondition::Credit,
            additionalCosts: 0,
            notes: 'Bombona de $220.000 equivalente a 44 horneadas de $5.000 para horno.',
            creator: $maria,
            lines: [new PurchaseLineData($this->presentation('DP-GAS-BOMBONA-HORNEADA-COMPRA')->id, '44', 5000)],
        ));
        $this->receive($purchase, $maria, $day, 'Recepción completa de la bombona de gas para horno.');
        $this->pay($purchase, 120000, $maria, $day, 'Abono inicial de bombona; saldo a crédito $100.000.');
    }

    private function receiveCocaCola(User $maria, Carbon $day): void
    {
        if (Purchase::query()->where('supplier_document_number', self::COCA_COLA_DOCUMENT)->exists()) {
            return;
        }

        $supplier = $this->supplier('Coca-Cola', null, null, 0, 'NIT pendiente de confirmar; factura/remisión 00001.');
        $purchase = app(CreatePurchase::class)->execute(new PurchaseData(
            supplierPersonId: $supplier->person_id,
            supplierDocumentNumber: self::COCA_COLA_DOCUMENT,
            issuedAt: $day,
            dueAt: $day,
            paymentCondition: PurchasePaymentCondition::Cash,
            additionalCosts: 0,
            notes: 'Compra de contado. Coca-Cola 2 L y media caja de Schweppes soda 400 ml.',
            creator: $maria,
            lines: [
                new PurchaseLineData($this->presentation('DP-COCACOLA-2L-UND')->id, '9', 4622),
                new PurchaseLineData($this->presentation('DP-SCHWEPPES-400ML-MCAJ6')->id, '1', 12550),
            ],
        ));
        $this->receive($purchase, $maria, $day, 'Recepción completa según factura/remisión 00001.');
        $this->pay($purchase, 54148, $maria, $day, 'Pago de contado de factura/remisión 00001.');
        app(ConvertPackage::class)->execute($this->presentation('DP-SCHWEPPES-400ML-MCAJ6'), PackageConversionType::Disassembly, '1', $maria, null, $day->copy()->setTime(12, 0));
    }

    private function completeTajadoProduction(User $kevin, User $maria, Carbon $day): void
    {
        $version = RecipeVersion::query()->whereHas('recipe', fn ($query) => $query->where('code', DonPatyCatalogSeeder::RECIPE_CODE))->applicableOn($day->toDateString())->sole();
        if (ProductionOrder::query()->where('recipe_version_id', $version->id)->whereDate('planned_for', $day)->exists()) {
            return;
        }

        $tajado = $version->compatibleProducts()->whereHas('presentation', fn ($query) => $query->where('sku', DonPatyCatalogSeeder::TAJADO_SKU))->sole();
        $leftover = $version->compatibleProducts()->whereHas('presentation', fn ($query) => $query->where('sku', 'DP-SOBRANTE-MASA-KG'))->sole();
        $order = app(PlanProduction::class)->execute(new PlanProductionData(
            recipeVersionId: $version->id,
            plannedFor: $day->copy()->setTime(14, 0),
            flourQuantityKg: '10',
            laborMethod: LaborMethod::AuthorizedManual,
            responsiblePersonId: $maria->person_id,
            creator: $maria,
            outputs: [
                new PlannedOutputData($tajado->id, '35'),
                new PlannedOutputData($leftover->id, '0.509020'),
            ],
        ));
        app(StartProduction::class)->execute($order, $maria, null, $day->copy()->setTime(14, 0));
        $authorization = app(CreateAuthorizationRequest::class)->execute('production.manual_labor', 'production.authorize-manual-labor', $order, $maria, 'Mano de obra del lote de tajado: $5.000.', 120);
        app(DecideAuthorizationRequest::class)->execute($authorization, $kevin, AuthorizationStatus::Approved, 'Autorizado para la producción real del 21 de septiembre.');

        $order->refresh()->load('consumptions.item');
        app(CompleteProduction::class)->execute($order, new CompleteProductionData(
            completedAt: $day->copy()->setTime(18, 30),
            actualDoughQuantityKg: '17.534000',
            wasteQuantityKg: '0',
            actor: $maria,
            consumptions: $order->consumptions->map(fn ($line) => new ActualConsumptionData(
                consumptionId: $line->id,
                actualQuantity: $this->actualConsumption($line->item->code, $line->calculated_quantity),
                differenceReason: $this->consumptionReason($line->item->code),
            ))->all(),
            outputs: [
                new ActualOutputData($tajado->id, '35', '17.024980'),
                new ActualOutputData($leftover->id, '0.509020', '0.509020'),
            ],
            manualLaborAmount: 5000,
            manualLaborReason: 'Pago de panadería para el lote de tajado del 21 de septiembre.',
            manualLaborAuthorization: $authorization->fresh(),
        ));
    }

    private function receive(Purchase $purchase, User $receiver, Carbon $day, string $notes): void
    {
        $purchase->load('lines');
        app(ReceivePurchase::class)->execute($purchase, new PurchaseReceiptData(
            receivedAt: $day,
            receiver: $receiver,
            lines: $purchase->lines->map(fn ($line) => new PurchaseReceiptLineData($line->id, $line->ordered_quantity))->all(),
            notes: $notes,
        ));
    }

    private function pay(Purchase $purchase, int $amount, User $actor, Carbon $day, string $reference): void
    {
        $payable = Payable::query()->where('source_type', $purchase->getMorphClass())->where('source_id', $purchase->id)->sole();
        $cash = FinancialAccount::query()->where('code', '1105-CAJA')->where('is_active', true)->sole();
        app(PaySupplierPayable::class)->execute($payable, new SupplierPaymentData($amount, $cash->id, $day, $actor, $reference));
    }

    private function supplier(string $name, ?string $documentType, ?string $documentNumber, int $termDays, string $notes): SupplierProfile
    {
        $person = $documentNumber === null
            ? Person::query()->where('name', $name)->first()
            : Person::query()->where('document_number', $documentNumber)->first();
        if ($person) {
            return SupplierProfile::query()->where('person_id', $person->id)->sole();
        }

        return app(RegisterSupplier::class)->execute(new SupplierData($name, $name, $documentType, $documentNumber, $documentNumber, null, null, $termDays, $notes, true));
    }

    private function presentation(string $sku): ProductPresentation
    {
        return ProductPresentation::query()->where('sku', $sku)->sole();
    }

    private function actualConsumption(string $itemCode, string $calculated): string
    {
        return match ($itemCode) {
            'DP-HARINA' => '10.265',
            'DP-MANTEQUILLA-PRODIGIO' => '0.926',
            'DP-HUEVOS' => '11',
            default => $calculated,
        };
    }

    private function consumptionReason(string $itemCode): ?string
    {
        return match ($itemCode) {
            'DP-HARINA' => 'Se usaron 265 g adicionales durante los cuatro cilindrados: 45 g, 60 g, 60 g y 100 g.',
            'DP-MANTEQUILLA-PRODIGIO' => 'Se usaron 26 g adicionales de picado y humectado: 3 g, 3 g, 15 g y 5 g.',
            'DP-HUEVOS' => 'La producción utilizó 11 huevos, uno más que los 10 de la fórmula base.',
            default => null,
        };
    }
}
