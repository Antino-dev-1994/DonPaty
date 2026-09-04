<?php

namespace Database\Seeders\Demo;

use App\Models\User;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Customers\Application\Data\CustomerData;
use App\Modules\Customers\Application\RegisterCustomer;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Pricing\Domain\Models\PriceList;
use App\Modules\Sales\Application\ConfirmSale;
use App\Modules\Sales\Application\CreateDraftSale;
use App\Modules\Sales\Application\Data\CreateSaleData;
use App\Modules\Sales\Application\Data\SaleLineData;
use App\Modules\Sales\Application\Data\SalePaymentPlanData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoSalesSeeder extends Seeder
{
    public const CUSTOMER_DOCUMENT = 'DEMO-CLI-001';

    public function run(): void
    {
        $actor = User::query()->where('email', DemoPeopleSeeder::ADMIN_EMAIL)->sole();
        $cash = FinancialAccount::query()->where('code', '1105-CAJA')->sole();
        $retail = PriceList::query()->where('name', 'Minorista')->sole();
        $wholesale = PriceList::query()->where('name', 'Mayorista')->sole();
        $customer = app(RegisterCustomer::class)->execute(new CustomerData(
            name: 'Tienda La Esquina Demo',
            documentType: 'NIT',
            documentNumber: self::CUSTOMER_DOCUMENT,
            email: 'cliente.demo@donpaty.local',
            phone: '3005550202',
            defaultPriceListId: $wholesale->id,
            creditLimit: 500_000,
            defaultPaymentTermDays: 8,
            deliveryNotes: 'Entregar por la mañana.',
            notes: 'Cliente mayorista demostrativo.',
            isActive: true,
        ));

        $cashSale = app(CreateDraftSale::class)->execute(new CreateSaleData(
            customerProfileId: null,
            salesOrderId: null,
            priceListId: $retail->id,
            soldAt: Carbon::now(),
            dueAt: null,
            discount: 0,
            actor: $actor,
            lines: $this->lines([
                ['DEMO-PAN-CAS-UND', '30'],
                ['DEMO-PAN-BOL-UND', '20'],
                ['DEMO-PAN-TAJ-UND', '5'],
            ]),
            payments: [new SalePaymentPlanData($cash->id, 89_000, 'VENTA-DEMO-CONTADO')],
        ));
        app(ConfirmSale::class)->execute($cashSale, $actor);

        $creditSale = app(CreateDraftSale::class)->execute(new CreateSaleData(
            customerProfileId: $customer->id,
            salesOrderId: null,
            priceListId: $wholesale->id,
            soldAt: Carbon::now(),
            dueAt: Carbon::now()->addDays(8),
            discount: 0,
            actor: $actor,
            lines: $this->lines([
                ['DEMO-PAN-CAS-UND', '50'],
                ['DEMO-PAN-BOL-UND', '30'],
                ['DEMO-PAN-TAJ-UND', '5'],
            ]),
            payments: [new SalePaymentPlanData($cash->id, 40_000, 'ABONO-DEMO-MAYORISTA')],
        ));
        app(ConfirmSale::class)->execute($creditSale, $actor);
    }

    /** @param list<array{string,string}> $lines @return list<SaleLineData> */
    private function lines(array $lines): array
    {
        return collect($lines)->map(fn (array $line) => new SaleLineData(
            presentationId: ProductPresentation::query()->where('sku', $line[0])->sole()->id,
            quantity: $line[1],
            unitPrice: null,
            discount: 0,
            priceReason: null,
        ))->all();
    }
}
