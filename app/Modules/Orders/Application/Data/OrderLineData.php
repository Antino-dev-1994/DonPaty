<?php
namespace App\Modules\Orders\Application\Data;
final readonly class OrderLineData {public function __construct(public string $presentationId,public string $quantity,public ?int $unitPrice,public int $discount,public ?string $priceReason,public ?string $notes){}public static function fromArray(array $data):self{return new self($data['presentation_id'],(string)$data['quantity'],isset($data['unit_price'])?(int)$data['unit_price']:null,(int)($data['discount']??0),$data['price_reason']??null,$data['notes']??null);}}
