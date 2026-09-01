<?php

namespace App\Modules\Pricing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pricing\Application\Data\PriceListData;
use App\Modules\Pricing\Application\SavePriceList;
use App\Modules\Pricing\Domain\Enums\PriceListType;
use App\Modules\Pricing\Presentation\Http\Requests\SavePriceListRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StorePriceListController extends Controller
{
    public function __invoke(SavePriceListRequest $request, SavePriceList $action): RedirectResponse
    {
        $data=$request->validated(); $list=$action->execute(new PriceListData($data['name'],PriceListType::from($data['type']),isset($data['starts_at'])?Carbon::parse($data['starts_at']):null,isset($data['ends_at'])?Carbon::parse($data['ends_at']):null,(bool)$data['is_default'],(bool)$data['is_active'],$data['items']),$request->user());
        return to_route('pricing.edit',$list)->with('success','Lista de precios creada.');
    }
}
