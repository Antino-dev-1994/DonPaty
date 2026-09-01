<?php
namespace App\Modules\Sales;
use Illuminate\Support\ServiceProvider;
class SalesServiceProvider extends ServiceProvider {public function boot():void{$this->loadRoutesFrom(__DIR__.'/Presentation/routes.php');}}
