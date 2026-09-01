<?php
namespace App\Modules\CashManagement;
use Illuminate\Support\ServiceProvider;
class CashManagementServiceProvider extends ServiceProvider {public function boot():void{$this->loadRoutesFrom(__DIR__.'/Presentation/routes.php');}}
