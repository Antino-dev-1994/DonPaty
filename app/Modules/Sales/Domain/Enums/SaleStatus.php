<?php
namespace App\Modules\Sales\Domain\Enums;
enum SaleStatus:string {case Draft='draft';case Confirmed='confirmed';case PartiallyPaid='partially_paid';case Paid='paid';case Reversed='reversed';public function label():string{return match($this){self::Draft=>'Borrador',self::Confirmed=>'Confirmada',self::PartiallyPaid=>'Pago parcial',self::Paid=>'Pagada',self::Reversed=>'Revertida'};}}
