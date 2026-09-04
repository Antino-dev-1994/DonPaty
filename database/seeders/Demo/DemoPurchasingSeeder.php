<?php

namespace Database\Seeders\Demo;

use App\Models\User;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Finance\Application\Data\SupplierPaymentData;
use App\Modules\Finance\Application\PaySupplierPayable;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Purchasing\Application\CreatePurchase;
use App\Modules\Purchasing\Application\Data\PurchaseData;
use App\Modules\Purchasing\Application\Data\PurchaseLineData;
use App\Modules\Purchasing\Application\Data\PurchaseReceiptData;
use App\Modules\Purchasing\Application\Data\PurchaseReceiptLineData;
use App\Modules\Purchasing\Application\Data\SupplierData;
use App\Modules\Purchasing\Application\ReceivePurchase;
use App\Modules\Purchasing\Application\RegisterSupplier;
use App\Modules\Purchasing\Domain\Enums\PurchasePaymentCondition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoPurchasingSeeder extends Seeder
{
    public const SUPPLIER_DOCUMENT = 'DEMO-PROV-001';

    public function run(): void
    {
        $actor = User::query()->where('email', DemoPeopleSeeder::ADMIN_EMAIL)->sole();
        $supplier = app(RegisterSupplier::class)->execute(new SupplierData(
            name: 'Distribuciones La Espiga Demo',
            tradeName: 'La Espiga',
            documentType: 'NIT',
            documentNumber: self::SUPPLIER_DOCUMENT,
            taxIdentifier: '900999001-1',
            email: 'proveedor.demo@donpaty.local',
            phone: '3005550101',
            defaultPaymentTermDays: 0,
            notes: 'Proveedor demostrativo de ingredientes y empaques.',
            isActive: true,
        ));

        $issuedAt = Carbon::now()->subDays(4);
        $purchase = app(CreatePurchase::class)->execute(new PurchaseData(
            supplierPersonId: $supplier->person_id,
            supplierDocumentNumber: 'FACT-DEMO-001',
            issuedAt: $issuedAt,
            dueAt: $issuedAt,
            paymentCondition: PurchasePaymentCondition::Cash,
            additionalCosts: 0,
            notes: 'Compra inicial de materia prima para el escenario demostrativo.',
            creator: $actor,
            lines: collect([
                ['DEMO-HARINA-KG', '50', 2500],
                ['DEMO-AGUA-KG', '50', 100],
                ['DEMO-AZUCAR-KG', '10', 4500],
                ['DEMO-MARGARINA-KG', '10', 8000],
                ['DEMO-SAL-KG', '5', 2000],
                ['DEMO-LEVADURA-KG', '2', 18000],
                ['DEMO-BOLSA-TAJ-UND', '30', 300],
            ])->map(fn (array $line) => new PurchaseLineData(
                presentationId: ProductPresentation::query()->where('sku', $line[0])->sole()->id,
                quantity: $line[1],
                unitPrice: $line[2],
            ))->all(),
        ));

        $purchase->load('lines');
        app(ReceivePurchase::class)->execute($purchase, new PurchaseReceiptData(
            receivedAt: $issuedAt->copy()->addDay(),
            receiver: $actor,
            lines: $purchase->lines->map(fn ($line) => new PurchaseReceiptLineData(
                purchaseLineId: $line->id,
                quantity: $line->ordered_quantity,
            ))->all(),
            notes: 'Recepción completa de la compra demostrativa.',
        ));

        app(PaySupplierPayable::class)->execute($purchase->payable()->sole(), new SupplierPaymentData(
            amount: $purchase->total,
            financialAccountId: FinancialAccount::query()->where('code', '1105-CAJA')->sole()->id,
            paidAt: $issuedAt->copy()->addDay(),
            creator: $actor,
            reference: 'PAGO-DEMO-COMPRA-001',
        ));
    }
}
