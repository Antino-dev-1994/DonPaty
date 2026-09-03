<?php

namespace App\Modules\Dashboard\Presentation\Http\Controllers;

use App\Modules\Dashboard\Application\DashboardOverviewQuery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowDashboardController
{
    public function __invoke(Request $request, DashboardOverviewQuery $overview): Response
    {
        return Inertia::render('dashboard/Index', [
            'overview' => $overview->execute($request->user()),
            'actions' => collect([
                ['label' => 'Nueva venta', 'href' => '/sales/create', 'permission' => 'sales.create'],
                ['label' => 'Nuevo pedido', 'href' => '/orders/create', 'permission' => 'orders.manage'],
                ['label' => 'Planificar producción', 'href' => '/production/create', 'permission' => 'production.manage'],
                ['label' => 'Registrar compra', 'href' => '/purchasing/purchases/create', 'permission' => 'purchases.manage'],
                ['label' => 'Registrar gasto', 'href' => '/finance/records/create', 'permission' => 'finance.manage'],
                ['label' => 'Operar caja', 'href' => '/cash', 'permission' => 'cash.operate'],
                ['label' => 'Atender solicitudes', 'href' => '/household', 'permission' => 'fund-requests.approve'],
            ])->filter(fn ($action) => $request->user()->hasPermission($action['permission']))->map(fn ($action) => ['label' => $action['label'], 'href' => $action['href']])->values(),
        ]);
    }
}
