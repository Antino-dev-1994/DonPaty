<?php
namespace App\Modules\CostAccounting;
use Illuminate\Support\ServiceProvider;
class CostAccountingServiceProvider extends ServiceProvider { public function boot():void{$this->loadRoutesFrom(__DIR__.'/Presentation/routes.php');} }
