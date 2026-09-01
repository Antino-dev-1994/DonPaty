<?php
namespace App\Modules\Production\Application\Data;
final readonly class ActualConsumptionData { public function __construct(public string $consumptionId, public string $actualQuantity, public ?string $differenceReason) {} /** @param array<string,mixed> $data */ public static function fromArray(array $data):self{return new self($data['consumption_id'],(string)$data['actual_quantity'],$data['difference_reason']??null);} }
