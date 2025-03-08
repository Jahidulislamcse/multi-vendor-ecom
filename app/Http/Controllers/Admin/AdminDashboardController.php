<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MainOrder;
use App\Models\User;
use Auth;

class AdminDashboardController extends Controller
{
    public function index()
    {

        $data['pending_order'] = MainOrder::where('status', 'pending')
            ->count();
        $data['total_sales'] = MainOrder::where('status', 'deliverd')
            ->sum('amount');
        $data['total_products'] = MainOrder::where('status', 'confirm')
            ->count();
        return view('admin.index', $data);
    }
}
