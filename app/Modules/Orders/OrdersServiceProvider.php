<?php
namespace App\Modules\Orders;
use Illuminate\Support\ServiceProvider;
class OrdersServiceProvider extends ServiceProvider {public function boot():void{$this->loadRoutesFrom(__DIR__.'/Presentation/routes.php');}}
