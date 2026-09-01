<?php
namespace App\Modules\Orders\Domain\Enums;
enum ProductionDemandStatus:string {case Pending='pending';case Planned='planned';case Fulfilled='fulfilled';case Cancelled='cancelled';}
