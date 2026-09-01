<?php
namespace App\Modules\Production\Application\Data;
final readonly class ActualOutputData { public function __construct(public string $compatibleProductId,public string $quantity,public string $doughQuantityKg){} /** @param array<string,mixed> $data */ public static function fromArray(array $data):self{return new self($data['compatible_product_id'],(string)$data['quantity'],(string)$data['dough_quantity']);} }
