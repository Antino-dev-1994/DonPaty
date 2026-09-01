<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Application\EnsureCatalogReferenceData;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\CostAccounting\Application\Data\OpenCostPeriodData;
use App\Modules\CostAccounting\Application\Data\UtilityCostData;
use App\Modules\CostAccounting\Application\OpenCostPeriod;
use App\Modules\CostAccounting\Domain\Enums\UtilityType;
use App\Modules\Customers\Domain\Models\CustomerProfile;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Identity\Application\EnsureAccessControlCatalog;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Inventory\Application\ConfirmInventoryAdjustment;
use App\Modules\Inventory\Application\CreateInventoryAdjustment;
use App\Modules\Inventory\Application\Data\InventoryAdjustmentData;
use App\Modules\Inventory\Domain\Models\InventoryBalance;
use App\Modules\Orders\Application\AddOrderAdvance;
use App\Modules\Orders\Application\ConfirmSalesOrder;
use App\Modules\Orders\Application\CreateProductionFromDemand;
use App\Modules\Orders\Application\Data\CreateProductionFromDemandData;
use App\Modules\Orders\Application\Data\OrderLineData;
use App\Modules\Orders\Application\Data\SalesOrderData;
use App\Modules\Orders\Application\SaveDraftOrder;
use App\Modules\Orders\Domain\Enums\OrderPriority;
use App\Modules\Orders\Domain\Models\ProductionDemand;
use App\Modules\Orders\Domain\Models\SalesOrder;
use App\Modules\People\Domain\Models\Person;
use App\Modules\Pricing\Application\EnsureDefaultPriceLists;
use App\Modules\Pricing\Domain\Models\PriceList;
use App\Modules\Production\Application\CompleteProduction;
use App\Modules\Production\Application\Data\ActualConsumptionData;
use App\Modules\Production\Application\Data\ActualOutputData;
use App\Modules\Production\Application\Data\CompleteProductionData;
use App\Modules\Production\Application\ReverseProduction;
use App\Modules\Production\Application\StartProduction;
use App\Modules\Production\Domain\Enums\LaborMethod;
use App\Modules\Recipes\Application\CreateRecipe;
use App\Modules\Recipes\Application\Data\CompatibleProductData;
use App\Modules\Recipes\Application\Data\RecipeData;
use App\Modules\Recipes\Application\Data\RecipeIngredientData;
use App\Modules\Recipes\Application\Data\RecipeVersionData;
use App\Modules\Recipes\Application\PublishRecipeVersion;
use App\Modules\Recipes\Domain\Enums\IngredientRole;
use Database\Seeders\FinanceReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_order_reserves_available_stock_and_records_a_financial_advance(): void
    {
        $context = $this->context();
        $this->stock($context['owner'], $context['product'], '5', 600);
        $order = $this->order($context, '3');

        app(ConfirmSalesOrder::class)->execute($order, $context['owner']);
        app(AddOrderAdvance::class)->execute($order->fresh(), 1000, FinancialAccount::query()->where('code', '1105-CAJA')->sole()->id, now(), $context['owner'], 'Transferencia de prueba');

        $this->assertSame('ready', $order->fresh()->status->value);
        $this->assertSame('3.000000', InventoryBalance::query()->where('presentation_id', $context['product']->id)->sole()->reserved_quantity);
        $this->assertSame('5.000000', InventoryBalance::query()->where('presentation_id', $context['product']->id)->sole()->physical_quantity);
        $this->assertSame(1000, $order->fresh()->advance_amount);
        $this->assertDatabaseHas('journal_entries', ['description' => "Anticipo {$order->document_number}", 'status' => 'confirmed']);
    }

    public function test_two_orders_are_consolidated_into_one_production_and_reopen_after_reversal(): void
    {
        $context = $this->context();
        $this->stock($context['owner'], $context['flour'], '10', 2000);
        $this->stock($context['owner'], $context['yeast'], '1', 10000);
        $first = $this->order($context, '10'); $second = $this->order($context, '10', '2026-09-16 12:00');
        app(ConfirmSalesOrder::class)->execute($first, $context['owner']); app(ConfirmSalesOrder::class)->execute($second, $context['owner']);
        $demands = ProductionDemand::query()->orderBy('id')->get();

        $production = app(CreateProductionFromDemand::class)->execute(new CreateProductionFromDemandData($demands->pluck('id')->all(), now(), null, LaborMethod::StandardPerKilogram, $context['worker']->id, $context['owner']));
        app(StartProduction::class)->execute($production, $context['owner']);
        $production->refresh()->load('consumptions');
        $completed = app(CompleteProduction::class)->execute($production, new CompleteProductionData(now()->addHour(), '1.6', '0', $context['owner'], $production->consumptions->map(fn ($line) => new ActualConsumptionData($line->id, $line->calculated_quantity, null))->all(), [new ActualOutputData($context['compatible']->id, '20', '1.6')]));

        $this->assertCount(2, $completed->orderAllocations);
        $this->assertSame('ready', $first->fresh()->status->value);
        $this->assertSame('ready', $second->fresh()->status->value);
        $this->assertSame('20.000000', InventoryBalance::query()->where('presentation_id', $context['product']->id)->sole()->reserved_quantity);

        app(ReverseProduction::class)->execute($completed, $context['owner'], 'Reversión funcional de prueba.');
        $this->assertSame('pending_production', $first->fresh()->status->value);
        $this->assertSame('pending_production', $second->fresh()->status->value);
        $this->assertSame('0.000000', InventoryBalance::query()->where('presentation_id', $context['product']->id)->sole()->reserved_quantity);
        $this->assertSame(2, ProductionDemand::query()->where('status', 'pending')->count());
    }

    /** @return array<string,mixed> */
    private function context(): array
    {
        Carbon::setTestNow('2026-09-15 08:00'); app(EnsureCatalogReferenceData::class)->execute(); app(EnsureAccessControlCatalog::class)->execute(); app(EnsureDefaultPriceLists::class)->execute(); $this->seed(FinanceReferenceSeeder::class);
        $owner=User::factory()->create();$owner->roles()->attach(Role::query()->where('name','owner')->sole());$worker=Person::factory()->create();$customerPerson=Person::factory()->create();$list=PriceList::query()->where('is_default',true)->sole();$customer=CustomerProfile::create(['person_id'=>$customerPerson->id,'default_price_list_id'=>$list->id,'credit_limit'=>100000,'default_payment_term_days'=>0,'is_active'=>true]);
        $kg=Unit::query()->where('code','kg')->sole();$unit=Unit::query()->where('code','und')->sole();$flourItem=$this->item('ORD-HARINA','Harina pedidos','raw_material',$kg);$yeastItem=$this->item('ORD-LEV','Levadura pedidos','raw_material',$kg);$productItem=$this->item('ORD-PAN','Pan pedido','finished_product',$unit);$flour=$this->presentation($flourItem,$kg,'ORD-HARINA-KG',true,false);$yeast=$this->presentation($yeastItem,$kg,'ORD-LEV-KG',true,false);$product=$this->presentation($productItem,$unit,'ORD-PAN-UND',false,true);$list->items()->create(['presentation_id'=>$product->id,'price'=>1000,'minimum_price'=>800]);
        $recipe=app(CreateRecipe::class)->execute(new RecipeData('ORD-MASA','Masa pedidos',null,true,new RecipeVersionData('1',$kg->id,'1.6',$kg->id,'0','Prueba',[new RecipeIngredientData($flourItem->id,$flour->id,IngredientRole::Flour,'1',$kg->id,'100',false,0),new RecipeIngredientData($yeastItem->id,$yeast->id,IngredientRole::Leavening,'0.02',$kg->id,'2',false,1)],[new CompatibleProductData($product->id,'0.08',$kg->id,'0','1',[])])),$owner);$version=$recipe->versions()->sole();app(PublishRecipeVersion::class)->execute($version,Carbon::parse('2026-09-01'),$owner);app(OpenCostPeriod::class)->execute(new OpenCostPeriodData(2026,9,$owner,$this->utilities(),5000,'Tarifa de prueba.'));
        return compact('owner','worker','customer','list','flour','yeast','product')+['version'=>$version->fresh(),'compatible'=>$version->compatibleProducts()->sole()];
    }

    private function order(array $context,string $quantity,string $due='2026-09-16 10:00'):SalesOrder{return app(SaveDraftOrder::class)->execute(new SalesOrderData($context['customer']->id,$context['list']->id,now(),Carbon::parse($due),OrderPriority::Normal,0,null,$context['owner'],[new OrderLineData($context['product']->id,$quantity,null,0,null,null)]));}
    private function item(string $code,string $name,string $type,Unit $unit):Item{return Item::create(['code'=>$code,'name'=>$name,'type'=>$type,'base_unit_id'=>$unit->id,'minimum_stock'=>0,'allow_negative_stock'=>false,'is_active'=>true]);}
    private function presentation(Item $item,Unit $unit,string $sku,bool $buy,bool $sell):ProductPresentation{return $item->presentations()->create(['sku'=>$sku,'name'=>'Unidad','stock_unit_id'=>$unit->id,'conversion_to_item_base'=>1,'is_purchasable'=>$buy,'is_sellable'=>$sell,'is_stockable'=>true,'is_active'=>true]);}
    private function stock(User $owner,ProductPresentation $presentation,string $quantity,int $cost):void{$adjustment=app(CreateInventoryAdjustment::class)->execute(new InventoryAdjustmentData('initial',now(),'Saldo de prueba.',$owner,[['presentation_id'=>$presentation->id,'counted_quantity'=>$quantity,'unit_cost'=>$cost]]));app(ConfirmInventoryAdjustment::class)->execute($adjustment,$owner);}
    private function utilities():array{return [new UtilityCostData(UtilityType::Electricity,Carbon::parse('2026-08-01'),Carbon::parse('2026-08-31'),Carbon::parse('2026-08-31'),100,'100','0',null,null,100,'Inicial.'),new UtilityCostData(UtilityType::Gas,Carbon::parse('2026-08-01'),Carbon::parse('2026-08-31'),Carbon::parse('2026-08-31'),200,'100','0',null,null,200,'Inicial.')];}
}
