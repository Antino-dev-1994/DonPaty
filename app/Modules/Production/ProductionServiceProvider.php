<?php
namespace App\Modules\Production;
use Illuminate\Support\ServiceProvider;
class ProductionServiceProvider extends ServiceProvider { public function boot():void{$this->loadRoutesFrom(__DIR__.'/Presentation/routes.php');} }
