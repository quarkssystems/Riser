<?php

namespace App\Http\Controllers;

use App\Models\PaymentPayout;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $adminIncome = PaymentPayout::where('role',config('constant.roles.admin'))
            ->sum('payout_amount');
        $totalTransactions = PaymentTransaction::sum('total');
        $totalCreators = User::select('iUserId')->whereHas(
            'roles', function($q){
                $q->whereIn('name', [config('constant.roles.creator')]);
            }
        )->count();
        $totalAgents = User::select('iUserId')->whereHas(
            'roles', function($q){
                $q->whereIn('name', [config('constant.roles.agent')]);
            }
        )->count();
        $totalUsers = User::select('iUserId')->whereHas(
            'roles', function($q){
                $q->whereIn('name', [config('constant.roles.user')]);
            }
        )->count();
        return view('dashboard', compact('adminIncome', 'totalTransactions', 'totalCreators', 'totalAgents', 'totalUsers'));
    }
}
