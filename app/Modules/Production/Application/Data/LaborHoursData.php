<?php
namespace App\Modules\Production\Application\Data;
final readonly class LaborHoursData { public function __construct(public string $personId,public string $hours,public int $hourlyRate){} /** @param array<string,mixed> $data */ public static function fromArray(array $data):self{return new self($data['person_id'],(string)$data['hours'],(int)$data['hourly_rate']);} }
