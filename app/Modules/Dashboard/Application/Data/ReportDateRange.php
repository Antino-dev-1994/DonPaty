<?php

namespace App\Modules\Dashboard\Application\Data;

use Carbon\CarbonImmutable;
use DomainException;

final readonly class ReportDateRange
{
    public function __construct(public CarbonImmutable $from, public CarbonImmutable $to)
    {
        if ($from->gt($to) || $from->diffInDays($to) > 366) {
            throw new DomainException('El rango debe ser válido y no superar 366 días.');
        }
    }

    public static function month(?string $month = null): self
    {
        $date = $month && preg_match('/^\d{4}-\d{2}$/', $month)
            ? CarbonImmutable::createFromFormat('Y-m', $month)
            : CarbonImmutable::now();

        return new self($date->startOfMonth(), $date->endOfMonth());
    }

    public static function dates(string $from, string $to): self
    {
        return new self(CarbonImmutable::parse($from)->startOfDay(), CarbonImmutable::parse($to)->endOfDay());
    }

    public function containsToday(): bool
    {
        return CarbonImmutable::now()->betweenIncluded($this->from, $this->to);
    }
}
