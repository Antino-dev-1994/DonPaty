<?php
namespace App\Modules\Sales\Application\Data;
final readonly class SalePaymentPlanData {public function __construct(public string $financialAccountId,public int $amount,public ?string $reference){}public static function fromArray(array $d):self{return new self($d['financial_account_id'],(int)$d['amount'],$d['reference']??null);}}
