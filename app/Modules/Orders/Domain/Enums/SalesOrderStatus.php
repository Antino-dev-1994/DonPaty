<?php
namespace App\Modules\Orders\Domain\Enums;
enum SalesOrderStatus:string { case Draft='draft';case Confirmed='confirmed';case PendingProduction='pending_production';case InProduction='in_production';case Ready='ready';case Delivered='delivered';case Cancelled='cancelled'; public function label():string{return match($this){self::Draft=>'Borrador',self::Confirmed=>'Confirmado',self::PendingProduction=>'Pendiente de producción',self::InProduction=>'En producción',self::Ready=>'Listo',self::Delivered=>'Entregado',self::Cancelled=>'Cancelado'};} }
