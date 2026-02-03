<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function dashboard(): View
    {
        // Basic Counts
        $usersCount = User::count();
        $plansCount = SubscriptionPlan::where('is_active', true)->count();
        $couponsCount = Coupon::count();

        // Revenue Stats (Mocked or Real if SubscriptionPayment model exists)
        // If we don't have a payments table yet, we'll calculate potential MRR based on active subscriptions
        $activeSubscriptions = \App\Models\Subscription::where('status', 'active')->with('plan')->get();
        $monthlyRevenue = $activeSubscriptions->sum(fn($sub) => $sub->plan->price ?? 0);
        
        // Users Chart Data (Last 30 days)
        $usersChartData = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Plan Distribution
        $planDistribution = \App\Models\Subscription::where('status', 'active')
            ->selectRaw('plan_id, COUNT(*) as count')
            ->groupBy('plan_id')
            ->with('plan')
            ->get()
            ->map(fn($item) => [
                'name' => $item->plan->name,
                'count' => $item->count,
                'color' => $item->plan->slug === 'premium' ? '#7c3aed' : '#fbbf24'
            ]);

        return view('super-admin.dashboard', [
            'usersCount' => $usersCount,
            'plansCount' => $plansCount,
            'couponsCount' => $couponsCount,
            'monthlyRevenue' => $monthlyRevenue,
            'usersChartData' => $usersChartData,
            'planDistribution' => $planDistribution,
            'recentUsers' => User::latest()->take(5)->get(),
        ]);
    }

    public function users(): View
    {
        $users = User::with('company.activeSubscription.plan')->paginate(20);
        $plans = SubscriptionPlan::all();
        return view('super-admin.users', compact('users', 'plans'));
    }

    public function plans(): View
    {
        $plans = SubscriptionPlan::all();
        return view('super-admin.plans', compact('plans'));
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'price' => 'numeric|min:0',
            'price_annual' => 'numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $plan->update($validated);

        return back()->with('success', 'Plan actualizado correctamente');
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

     public function assignPlan(Request $request, User $user)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->plan_id);
        $company = $user->company;

        if (!$company) {
             return back()->with('error', 'El usuario no tiene compañía asignada');
        }

        // Logic to manually assign plan (bypass payment)
        // Usually creates a subscription record with 'free' status or similar, or just updates company plan_id
        // Let's reuse assignFreePlan logic but adaptable? Or just simplistic approach:
        
        $company->update(['plan_id' => $plan->id]);
        
        // If there was a subscription, should we cancel it? 
        // For manual assignment, we might assume it overrides everything.
        // Let's cancel any active subscription first to avoid double billing if it exists.
        if ($company->activeSubscription && $company->activeSubscription->isActive()) {
             $company->activeSubscription->cancel();
        }
        
        // Create a 'manual' subscription record for audit?
        // Reuse Subscription model but with status 'active' and no MP ID.
        $company->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addYears(10), // Limit to 10 years to avoid TIMESTAMP overflow (2038)
        ]);
        
        $company->update(['subscription_id' => $company->subscriptions()->latest()->first()->id]);

        return back()->with('success', 'Plan asignado manualmente');
    }
}
