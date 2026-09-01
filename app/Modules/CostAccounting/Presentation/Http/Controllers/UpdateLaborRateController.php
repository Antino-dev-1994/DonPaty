<?php

namespace App\Modules\CostAccounting\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CostAccounting\Application\UpdateStandardLaborRate;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateLaborRateController extends Controller
{
    public function __invoke(Request $request, CostPeriod $costPeriod, UpdateStandardLaborRate $action): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('cost-periods.manage'), 403);
        $data = $request->validate(['rate' => ['required', 'integer', 'min:0'], 'reason' => ['required', 'string', 'max:2000']]);
        $action->execute($costPeriod, (int) $data['rate'], $data['reason'], $request->user());
        return to_route('cost-periods.show', $costPeriod)->with('success', 'Tarifa estándar actualizada.');
    }
}
