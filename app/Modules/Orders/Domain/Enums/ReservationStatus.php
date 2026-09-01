<?php
namespace App\Modules\Orders\Domain\Enums;
enum ReservationStatus:string { case Active='active';case Released='released';case Consumed='consumed'; }
