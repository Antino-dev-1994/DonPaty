<?php
namespace App\Modules\Orders;
use App\Modules\Orders\Domain\Models\SalesOrder;use App\Modules\Orders\Domain\Models\SalesOrderLine;use App\Modules\Orders\Infrastructure\Observers\CancelDemandWhenOrderCancelled;use App\Modules\Orders\Infrastructure\Observers\RecalculateDemandWhenReservationChanges;use Illuminate\Support\ServiceProvider;
class OrdersServiceProvider extends ServiceProvider {public function boot():void{SalesOrder::observe(CancelDemandWhenOrderCancelled::class);SalesOrderLine::observe(RecalculateDemandWhenReservationChanges::class);$this->loadRoutesFrom(__DIR__.'/Presentation/routes.php');}}
