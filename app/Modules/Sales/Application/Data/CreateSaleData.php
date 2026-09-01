<?php
namespace App\Modules\Sales\Application\Data;
use App\Models\User;use Carbon\CarbonInterface;
final readonly class CreateSaleData {/** @param list<SaleLineData> $lines @param list<SalePaymentPlanData> $payments */public function __construct(public ?string $customerProfileId,public ?string $salesOrderId,public string $priceListId,public CarbonInterface $soldAt,public ?CarbonInterface $dueAt,public int $discount,public User $actor,public array $lines,public array $payments){}}
