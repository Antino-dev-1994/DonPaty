<?php

namespace App\Modules\Pricing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pricing\Application\Data\PriceListData;
use App\Modules\Pricing\Application\SavePriceList;
use App\Modules\Pricing\Domain\Enums\PriceListType;
use App\Modules\Pricing\Domain\Models\PriceList;
use App\Modules\Pricing\Presentation\Http\Requests\SavePriceListRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class UpdatePriceListController extends Controller
{
    public function __invoke(SavePriceListRequest $request, PriceList $priceList, SavePriceList $action): RedirectResponse
    {
        $data=$request->validated(); $action->execute(new PriceListData($data['name'],PriceListType::from($data['type']),isset($data['starts_at'])?Carbon::parse($data['starts_at']):null,isset($data['ends_at'])?Carbon::parse($data['ends_at']):null,(bool)$data['is_default'],(bool)$data['is_active'],$data['items']),$request->user(),$priceList);
        return back()->with('success','Lista de precios actualizada.');
    }
}
