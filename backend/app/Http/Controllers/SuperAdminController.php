<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function dashboard(): View
    {
        $usersCount = User::count();
        $couponsCount = Coupon::count();

        $usersChartData = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('super-admin.dashboard', [
            'usersCount' => $usersCount,
            'couponsCount' => $couponsCount,
            'usersChartData' => $usersChartData,
            'recentUsers' => User::latest()->take(5)->get(),
        ]);
    }

    public function users(): View
    {
        $users = User::with('company')->paginate(20);
        return view('super-admin.users', compact('users'));
    }

    public function coupons(): View
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->paginate(20);
        return view('super-admin.coupons', compact('coupons'));
    }

    public function createCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:coupons,code',
            'discount_percentage' => 'nullable|integer|min:1|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date',
        ]);

        Coupon::create($validated);

        return back()->with('success', 'Cupón creado correctamente');
    }
    
    public function toggleCoupon(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);
        return back()->with('success', 'Estado del cupón actualizado');
    }
}
