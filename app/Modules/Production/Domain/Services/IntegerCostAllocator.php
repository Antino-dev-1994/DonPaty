<?php

namespace App\Modules\Production\Domain\Services;

use DomainException;

class IntegerCostAllocator
{
    /** @param list<float> $weights @return list<int> */
    public function allocate(int $total, array $weights): array
    {
        $weightTotal = array_sum($weights);
        if ($total < 0 || $weights === [] || $weightTotal <= 0) throw new DomainException('No es posible distribuir el costo entre los productos.');
        $exact = array_map(fn(float $weight):float=>$total*($weight/$weightTotal),$weights); $allocated=array_map(fn(float $value):int=>(int)floor($value),$exact); $remaining=$total-array_sum($allocated); $indexes=array_keys($weights);
        usort($indexes,fn(int $left,int $right):int=>($exact[$right]-$allocated[$right])<=>($exact[$left]-$allocated[$left]));
        for($position=0;$position<$remaining;$position++)$allocated[$indexes[$position%count($indexes)]]++;
        ksort($allocated); return array_values($allocated);
    }
}
