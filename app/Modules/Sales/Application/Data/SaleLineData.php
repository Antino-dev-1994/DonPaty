<?php
namespace App\Modules\Sales\Application\Data;
final readonly class SaleLineData {public function __construct(public string $presentationId,public string $quantity,public ?int $unitPrice,public int $discount,public ?string $priceReason,public ?string $salesOrderLineId=null){}public static function fromArray(array $d):self{return new self($d['presentation_id'],(string)$d['quantity'],isset($d['unit_price'])?(int)$d['unit_price']:null,(int)($d['discount']??0),$d['price_reason']??null,$d['sales_order_line_id']??null);}}
