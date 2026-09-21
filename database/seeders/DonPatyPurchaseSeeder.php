<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\People\Domain\Models\Person;
use App\Modules\Purchasing\Application\CreatePurchase;
use App\Modules\Purchasing\Application\Data\PurchaseData;
use App\Modules\Purchasing\Application\Data\PurchaseLineData;
use App\Modules\Purchasing\Application\Data\PurchaseReceiptData;
use App\Modules\Purchasing\Application\Data\PurchaseReceiptLineData;
use App\Modules\Purchasing\Application\Data\SupplierData;
use App\Modules\Purchasing\Application\ReceivePurchase;
use App\Modules\Purchasing\Application\RegisterSupplier;
use App\Modules\Purchasing\Domain\Models\Purchase;
use App\Modules\Purchasing\Domain\Models\SupplierProfile;
use App\Modules\Purchasing\Domain\Enums\PurchasePaymentCondition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DonPatyPurchaseSeeder extends Seeder
{
    private const SUPPLIER_DOCUMENT = 'DP-PROV-001';
    private const PURCHASE_DOCUMENT = 'DP-COMPRA-INICIAL-20260919';

    public function run(): void
    {
        if (Purchase::query()->where('supplier_document_number', self::PURCHASE_DOCUMENT)->exists()) {
            return;
        }

        $actor = User::query()->where('email', DonPatyPeopleSeeder::MARIA_EMAIL)->sole();
        $supplier = $this->supplier();
        $today = Carbon::today();
        $purchase = app(CreatePurchase::class)->execute(new PurchaseData(
            supplierPersonId: $supplier->person_id,
            supplierDocumentNumber: self::PURCHASE_DOCUMENT,
            issuedAt: $today,
            dueAt: $today,
            paymentCondition: PurchasePaymentCondition::Credit,
            additionalCosts: 0,
            notes: 'Compra inicial para la producción de pan tajado del 19 de septiembre de 2026.',
            creator: $actor,
            lines: collect([
                ['DP-HARINA-COMPRA', '6.3', 2300], ['DP-AGUA-COMPRA', '2.7', 0], ['DP-AZUCAR-COMPRA', '0.78', 3100],
                ['DP-SAL-COMPRA', '0.02', 2800], ['DP-MANTEQUILLA-PRODIGIO-COMPRA', '0.51', 8000], ['DP-MANTEQUILLA-HIDROGENADA-COMPRA', '0.3', 3700],
                ['DP-HUEVOS-COMPRA', '1', 12500], ['DP-ESENCIA-MANTEQUILLA-COMPRA', '500', 20], ['DP-COLOR-COMPRA', '1000', 10],
                ['DP-LEVADURA-COMPRA', '500', 15], ['DP-ANTIMOHO-COMPRA', '1', 18800], ['DP-MANTEQUILLA-ASTRA-COMPRA', '0.2', 11000],
                ['DP-BOLSA-TAJADO-COMPRA', '100', 130],
            ])->map(fn (array $line) => new PurchaseLineData(
                presentationId: \App\Modules\Catalog\Domain\Models\ProductPresentation::query()->where('sku', $line[0])->sole()->id,
                quantity: $line[1], unitPrice: $line[2],
            ))->all(),
        ));

        $purchase->load('lines');
        app(ReceivePurchase::class)->execute($purchase, new PurchaseReceiptData(
            receivedAt: $today,
            receiver: $actor,
            lines: $purchase->lines->map(fn ($line) => new PurchaseReceiptLineData($line->id, $line->ordered_quantity))->all(),
            notes: 'Recepción completa de insumos para la producción inicial.',
        ));
    }

    private function supplier(): SupplierProfile
    {
        $existing = Person::query()->where('document_number', self::SUPPLIER_DOCUMENT)->first();
        if ($existing) {
            return SupplierProfile::query()->where('person_id', $existing->id)->sole();
        }

        return app(RegisterSupplier::class)->execute(new SupplierData(
            name: 'Proveedor de insumos DonPaty', tradeName: 'Proveedor inicial', documentType: 'NIT',
            documentNumber: self::SUPPLIER_DOCUMENT, taxIdentifier: self::SUPPLIER_DOCUMENT,
            email: 'proveedor@donpaty.local', phone: null, defaultPaymentTermDays: 0,
            notes: 'Proveedor creado para el inventario inicial de pan tajado.', isActive: true,
        ));
    }
}
