<?php
namespace App\Modules\CashManagement\Application;
use App\Modules\Finance\Domain\Models\JournalLine;
class FinancialAccountBalance {public function execute(string $accountId):int{return (int)JournalLine::query()->where('financial_account_id',$accountId)->selectRaw('COALESCE(SUM(debit_amount - credit_amount), 0) as balance')->value('balance');}}
