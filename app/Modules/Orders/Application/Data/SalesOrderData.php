<?php
namespace App\Modules\Orders\Application\Data;
use App\Models\User;use App\Modules\Identity\Domain\Models\AuthorizationRequest;use App\Modules\Orders\Domain\Enums\OrderPriority;use Carbon\CarbonInterface;
final readonly class SalesOrderData {/** @param list<OrderLineData> $lines */public function __construct(public string $customerProfileId,public string $priceListId,public CarbonInterface $orderedAt,public CarbonInterface $dueAt,public OrderPriority $priority,public int $discount,public ?string $notes,public User $actor,public array $lines,public ?AuthorizationRequest $priceAuthorization=null){}}
