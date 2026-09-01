<?php
namespace App\Modules\Sales\Domain\Enums;
enum ReceivableStatus:string {case Pending='pending';case Partial='partial';case Paid='paid';case Cancelled='cancelled';}
