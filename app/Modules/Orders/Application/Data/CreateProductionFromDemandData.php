<?php
namespace App\Modules\Orders\Application\Data;
use App\Models\User;use App\Modules\Production\Domain\Enums\LaborMethod;use Carbon\CarbonInterface;
final readonly class CreateProductionFromDemandData {/** @param list<string> $demandIds */public function __construct(public array $demandIds,public CarbonInterface $plannedFor,public ?string $flourQuantityKg,public LaborMethod $laborMethod,public string $responsiblePersonId,public User $actor){}}
