<?php
namespace App\Modules\CostAccounting\Domain\Enums;
enum UtilityType:string { case Electricity='electricity'; case Gas='gas'; public function label():string{return match($this){self::Electricity=>'Electricidad',self::Gas=>'Gas'};} public function consumptionUnit():string{return match($this){self::Electricity=>'kWh',self::Gas=>'m³'};} }
