<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Services\InventoryDashboardService;
use Illuminate\Contracts\View\View;

class InventoryDashboardController extends Controller
{
    public function __construct(
        private readonly InventoryDashboardService $dashboardService
    ) {}

    public function index(): View
    {
        $data = $this->dashboardService->getDashboardData();

        return view('pages.inventory.dashboard', $data);
    }
}