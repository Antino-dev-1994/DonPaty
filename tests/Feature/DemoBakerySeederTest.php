<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\CostAccounting\Domain\Enums\UtilityType;
use App\Modules\CostAccounting\Domain\Models\UtilityCostRecord;
use App\Modules\Inventory\Domain\Models\InventoryBalance;
use App\Modules\People\Domain\Enums\PersonClassificationType;
use App\Modules\People\Domain\Models\PersonClassification;
use App\Modules\Production\Domain\Enums\ProductionStatus;
use App\Modules\Production\Domain\Models\ProductionOrder;
use App\Modules\Purchasing\Domain\Enums\PurchasePaymentStatus;
use App\Modules\Purchasing\Domain\Enums\PurchaseReceiptStatus;
use App\Modules\Purchasing\Domain\Models\Purchase;
use App\Modules\Recipes\Domain\Models\Recipe;
use App\Modules\Sales\Domain\Enums\SaleStatus;
use App\Modules\Sales\Domain\Models\Sale;
use Database\Seeders\DemoBakerySeeder;
use Database\Seeders\Demo\DemoPeopleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoBakerySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_a_complete_and_idempotent_bakery_scenario(): void
    {
        $this->seed(DemoBakerySeeder::class);

        $this->assertSame(3, PersonClassification::query()
            ->where('classification', PersonClassificationType::Resident)
            ->whereHas('person', fn ($query) => $query->where('document_number', 'like', 'DEMO-100%'))
            ->count());
        $this->assertSame(1, PersonClassification::query()
            ->where('classification', PersonClassificationType::Employee)
            ->whereHas('person', fn ($query) => $query->where('document_number', 'DEMO-1002'))
            ->count());
        $this->assertSame(3, User::query()->whereIn('email', [
            DemoPeopleSeeder::ADMIN_EMAIL,
            DemoPeopleSeeder::WORKER_EMAIL,
            DemoPeopleSeeder::RESIDENT_EMAIL,
        ])->count());

        $this->assertSame(2, Recipe::query()->where('code', 'like', 'DEMO-MASA-%')->count());
        $this->assertSame(2, Recipe::query()->where('code', 'DEMO-MASA-TRAD')->sole()->versions()->count());
        $this->assertSame(350_000, UtilityCostRecord::query()->where('utility_type', UtilityType::Electricity)->sole()->total_amount);
        $this->assertSame(100_000, UtilityCostRecord::query()->where('utility_type', UtilityType::Water)->sole()->total_amount);

        $purchase = Purchase::query()->sole();
        $this->assertSame(PurchaseReceiptStatus::Received, $purchase->receipt_status);
        $this->assertSame(PurchasePaymentStatus::Paid, $purchase->payment_status);
        $this->assertSame(2, ProductionOrder::query()->where('status', ProductionStatus::Completed)->count());
        $this->assertSame(1, Sale::query()->where('status', SaleStatus::Paid)->count());
        $this->assertSame(1, Sale::query()->where('status', SaleStatus::PartiallyPaid)->count());
        $this->assertInventory('DEMO-PAN-CAS-UND', '60.000000');
        $this->assertInventory('DEMO-PAN-BOL-UND', '60.000000');
        $this->assertInventory('DEMO-PAN-TAJ-UND', '12.000000');

        $counts = [User::count(), Recipe::count(), Purchase::count(), ProductionOrder::count(), Sale::count()];
        $this->seed(DemoBakerySeeder::class);
        $this->assertSame($counts, [User::count(), Recipe::count(), Purchase::count(), ProductionOrder::count(), Sale::count()]);
    }

    private function assertInventory(string $sku, string $expected): void
    {
        $presentation = ProductPresentation::query()->where('sku', $sku)->sole();
        $this->assertSame($expected, InventoryBalance::query()->where('presentation_id', $presentation->id)->sole()->physical_quantity);
    }
}
