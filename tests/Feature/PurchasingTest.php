<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Application\EnsureCatalogReferenceData;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Finance\Application\Data\SupplierPaymentData;
use App\Modules\Finance\Application\PaySupplierPayable;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Identity\Application\EnsureAccessControlCatalog;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Inventory\Domain\Models\InventoryBalance;
use App\Modules\Purchasing\Application\CreatePurchase;
use App\Modules\Purchasing\Application\Data\PurchaseData;
use App\Modules\Purchasing\Application\Data\PurchaseLineData;
use App\Modules\Purchasing\Application\Data\PurchaseReceiptData;
use App\Modules\Purchasing\Application\Data\PurchaseReceiptLineData;
use App\Modules\Purchasing\Application\Data\PurchaseReturnData;
use App\Modules\Purchasing\Application\Data\PurchaseReturnLineData;
use App\Modules\Purchasing\Application\Data\SupplierData;
use App\Modules\Purchasing\Application\ReceivePurchase;
use App\Modules\Purchasing\Application\RegisterSupplier;
use App\Modules\Purchasing\Application\ReturnPurchaseItems;
use App\Modules\Purchasing\Domain\Enums\PurchasePaymentCondition;
use App\Modules\Purchasing\Domain\Models\Purchase;
use Database\Seeders\FinanceReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchasingTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_purchase_creates_lines_and_payable_with_distributed_costs(): void
    {
        [$owner, $presentation, $supplierPersonId] = $this->context();
        $purchase = $this->purchase($owner, $presentation, $supplierPersonId);

        $this->assertSame(10000, $purchase->subtotal);
        $this->assertSame(11000, $purchase->total);
        $this->assertDatabaseHas('purchase_lines', ['purchase_id' => $purchase->id, 'allocated_additional_cost' => 1000]);
        $this->assertDatabaseHas('payables', ['source_id' => $purchase->id, 'original_amount' => 11000, 'balance_amount' => 11000]);
    }

    public function test_partial_receipt_payment_and_return_update_inventory_and_obligation(): void
    {
        [$owner, $presentation, $supplierPersonId] = $this->context();
        $purchase = $this->purchase($owner, $presentation, $supplierPersonId);
        $line = $purchase->lines()->sole();

        app(ReceivePurchase::class)->execute($purchase, new PurchaseReceiptData(
            receivedAt: now(), receiver: $owner,
            lines: [new PurchaseReceiptLineData($line->id, '4')], notes: 'Recepción parcial de prueba.',
        ));
        app(PaySupplierPayable::class)->execute($purchase->payable()->sole(), new SupplierPaymentData(
            amount: 3000, financialAccountId: FinancialAccount::query()->where('code', '1105-CAJA')->sole()->id,
            paidAt: now(), creator: $owner, reference: 'Transferencia de prueba',
        ));
        app(ReturnPurchaseItems::class)->execute($purchase, new PurchaseReturnData(
            returnedAt: now(), reason: 'Unidad defectuosa.', creator: $owner,
            lines: [new PurchaseReturnLineData($line->id, '1')],
        ));

        $this->assertSame('3.000000', InventoryBalance::query()->where('presentation_id', $presentation->id)->sole()->physical_quantity);
        $this->assertDatabaseHas('payables', ['source_id' => $purchase->id, 'original_amount' => 11000, 'credited_amount' => 1100, 'paid_amount' => 3000, 'balance_amount' => 6900, 'status' => 'partial']);
        $this->assertDatabaseHas('journal_entries', ['source_type' => 'App\\Modules\\Finance\\Domain\\Models\\Payment', 'status' => 'confirmed']);
    }

    /** @return array{User, ProductPresentation, string} */
    private function context(): array
    {
        app(EnsureAccessControlCatalog::class)->execute(); app(EnsureCatalogReferenceData::class)->execute(); $this->seed(FinanceReferenceSeeder::class);
        $owner = User::factory()->create(); $owner->roles()->attach(Role::query()->where('name', 'owner')->sole());
        $unit = Unit::query()->where('code', 'kg')->sole();
        $item = Item::create(['code' => fake()->unique()->bothify('HAR-###'), 'name' => 'Harina prueba', 'type' => 'raw_material', 'base_unit_id' => $unit->id, 'minimum_stock' => 0, 'allow_negative_stock' => false, 'is_active' => true]);
        $presentation = $item->presentations()->create(['sku' => fake()->unique()->bothify('HAR-###'), 'name' => 'Bulto fraccionable', 'stock_unit_id' => $unit->id, 'conversion_to_item_base' => 1, 'is_purchasable' => true, 'is_sellable' => false, 'is_stockable' => true, 'is_active' => true]);
        $supplier = app(RegisterSupplier::class)->execute(new SupplierData('Molino prueba', 'Molino prueba', 'NIT', fake()->unique()->numerify('#########'), null, null, null, 30, null, true));

        return [$owner, $presentation, $supplier->person_id];
    }

    private function purchase(User $owner, ProductPresentation $presentation, string $supplierPersonId): Purchase
    {
        return app(CreatePurchase::class)->execute(new PurchaseData(
            supplierPersonId: $supplierPersonId, supplierDocumentNumber: 'FAC-100', issuedAt: now(), dueAt: now()->addDays(30),
            paymentCondition: PurchasePaymentCondition::Credit, additionalCosts: 1000, notes: null, creator: $owner,
            lines: [new PurchaseLineData($presentation->id, '10', 1000)],
        ));
    }
}
