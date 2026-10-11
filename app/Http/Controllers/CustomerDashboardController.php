<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $orders = $request->user()->orders()
            ->where('status', 'paid')
            ->with(['items.product.files'])
            ->latest('paid_at')
            ->get();

        return view('customer.dashboard', compact('orders'));
    }
}
