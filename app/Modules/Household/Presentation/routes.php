<?php

use App\Modules\Household\Presentation\Http\Controllers\HouseholdDashboardController;
use App\Modules\Household\Presentation\Http\Controllers\ApproveFundRequestController;
use App\Modules\Household\Presentation\Http\Controllers\ConfirmFundRequestController;
use App\Modules\Household\Presentation\Http\Controllers\PayFundRequestController;
use App\Modules\Household\Presentation\Http\Controllers\RejectFundRequestController;
use App\Modules\Household\Presentation\Http\Controllers\StoreFundRequestController;
use App\Modules\Household\Presentation\Http\Controllers\ConfirmHouseholdBudgetController;
use App\Modules\Household\Presentation\Http\Controllers\StoreHouseholdBudgetLineController;
use App\Modules\Household\Presentation\Http\Controllers\PayDebtController;
use App\Modules\Household\Presentation\Http\Controllers\StoreDebtController;
use App\Modules\Household\Presentation\Http\Controllers\StoreSavingsContributionController;
use App\Modules\Household\Presentation\Http\Controllers\StoreSavingsGoalController;
use App\Modules\Household\Presentation\Http\Controllers\StoreHouseholdAccountController;
use App\Modules\Household\Presentation\Http\Controllers\StoreHouseholdTransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('household')->name('household.')->group(function (): void {
    Route::get('/', HouseholdDashboardController::class)->name('index');
    Route::post('/accounts', StoreHouseholdAccountController::class)->name('accounts.store');
    Route::post('/transactions', StoreHouseholdTransactionController::class)->name('transactions.store');
    Route::post('/fund-requests', StoreFundRequestController::class)->name('fund-requests.store');
    Route::post('/fund-requests/{fundRequest}/approve', ApproveFundRequestController::class)->name('fund-requests.approve');
    Route::post('/fund-requests/{fundRequest}/reject', RejectFundRequestController::class)->name('fund-requests.reject');
    Route::post('/fund-requests/{fundRequest}/pay', PayFundRequestController::class)->name('fund-requests.pay');
    Route::post('/fund-requests/{fundRequest}/confirm', ConfirmFundRequestController::class)->name('fund-requests.confirm');
    Route::post('/budgets/lines', StoreHouseholdBudgetLineController::class)->name('budgets.lines.store');
    Route::post('/budgets/{budget}/confirm', ConfirmHouseholdBudgetController::class)->name('budgets.confirm');
    Route::post('/debts', StoreDebtController::class)->name('debts.store');
    Route::post('/debts/{debt}/payments', PayDebtController::class)->name('debts.payments.store');
    Route::post('/savings-goals', StoreSavingsGoalController::class)->name('savings-goals.store');
    Route::post('/savings-goals/{savingsGoal}/contributions', StoreSavingsContributionController::class)->name('savings-goals.contributions.store');
});
