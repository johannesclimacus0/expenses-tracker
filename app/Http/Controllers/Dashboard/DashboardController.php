<?php

namespace App\Http\Controllers\Dashboard;

use App\DTOs\Dashboard\DashboardFilterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\FilterDashboardRequest;
use App\Services\Dashboard\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(FilterDashboardRequest $request, DashboardService $dashboardService): View
    {
        $filter = DashboardFilterData::fromArray(
            $request->validated(),
            $request->user()->settings->dashboard_period,
        );
        $dashboard = $dashboardService->build($request->user(), $filter);

        return view('dashboard', compact('dashboard'));
    }
}
