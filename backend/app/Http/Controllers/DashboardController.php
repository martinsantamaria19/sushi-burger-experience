<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Repositories\Interfaces\RestaurantRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected RestaurantRepositoryInterface $restaurantRepository;

    public function __construct(RestaurantRepositoryInterface $restaurantRepository)
    {
        $this->restaurantRepository = $restaurantRepository;
    }

    private function getDashboardData(): array
    {
        $activeRestaurantId = session('active_restaurant_id');
        $user = Auth::user();
        $company = $user->company;
        $restaurants = $company ? $company->restaurants : collect();
        $activeRestaurant = $restaurants->firstWhere('id', $activeRestaurantId) ?? $restaurants->first();

        if ($activeRestaurant && $activeRestaurant->id != $activeRestaurantId) {
            session(['active_restaurant_id' => $activeRestaurant->id]);
        }

        $menu = $activeRestaurant ? $activeRestaurant->menus()->first() : null;
        $categoriesCount = $activeRestaurant ? $activeRestaurant->categories()->count() : 0;
        $productsCount = $activeRestaurant ? $activeRestaurant->products()->count() : 0;

        // Stats for Dashboard/Analytics
        $stats = [
            'products_count' => $productsCount,
            'qrcodes_count' => $activeRestaurant ? $activeRestaurant->qrCodes()->count() : 0,
            'total_scans' => $activeRestaurant ? $activeRestaurant->qrCodes()->sum('scans_count') : 0,
            'categories_count' => $categoriesCount,
            'has_menu' => $menu !== null,
            'menu_name' => $menu ? $menu->name : null,
        ];

        return [
            'restaurants' => $restaurants,
            'activeRestaurant' => $activeRestaurant,
            'restaurant' => $activeRestaurant,
            'company' => $company,
            'stats' => $stats,
            'menu' => $menu,
        ];
    }

    public function index(): View
    {
        return view('admin.index', $this->getDashboardData());
    }

    public function restaurants(): View
    {
        return view('admin.restaurants', $this->getDashboardData());
    }

    public function switchRestaurant(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id'
        ]);

        $user = auth()->user();
        $restaurant = $user->company->restaurants()->findOrFail($request->restaurant_id);
        session(['active_restaurant_id' => $restaurant->id]);

        return back()->with('success', 'Contexto cambiado correctamente');
    }

    public function menu(): View
    {
        $data = $this->getDashboardData();
        $restaurant = $data['activeRestaurant'];
        
        $menu = $restaurant ? $restaurant->menus()->first() : null;
        if ($menu) {
            $menu->load(['categories.products']);
        }

        return view('admin.menu', array_merge($data, ['menu' => $menu]));
    }

    public function qrs(): View
    {
        return view('admin.qrs', $this->getDashboardData());
    }

    public function users(): View
    {
        $data = $this->getDashboardData();
        $user = Auth::user();
        $company = $user->company;
        
        // Get all users from the same company
        $users = $company ? $company->users()->orderBy('created_at', 'asc')->get() : collect();
        
        return view('admin.users', array_merge($data, ['users' => $users]));
    }

    public function analytics(): View
    {
        return view('admin.analytics', $this->getDashboardData());
    }

    public function settings(): View
    {
        return view('admin.settings', $this->getDashboardData());
    }

    /**
     * Update company settings
     */
    public function updateCompany(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'message' => 'Usuario no tiene una compañía asignada'
            ], 400);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'currency' => 'required|string|size:3|in:UYU,USD,ARS,EUR',
        ]);

        // Generate slug from company name if it changed
        $slug = $company->slug;
        if ($company->name !== $request->name) {
            $baseSlug = Str::slug($request->name);
            $slug = $baseSlug;
            $counter = 1;
            
            // Ensure slug is unique (excluding current company)
            while (Company::where('slug', $slug)->where('id', '!=', $company->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
        }

        $company->update([
            'name' => $request->name,
            'slug' => $slug,
            'currency' => $request->currency,
        ]);

        return response()->json([
            'message' => 'Configuración actualizada exitosamente',
            'company' => $company
        ]);
    }

    public function personalize(): View
    {
        return view('admin.personalize', $this->getDashboardData());
    }

    /**
     * Get analytics data for charts
     */
    public function getAnalytics(Request $request)
    {
        $activeRestaurantId = session('active_restaurant_id');
        $user = Auth::user();
        $company = $user->company;
        $restaurants = $company ? $company->restaurants : collect();
        $restaurant = $restaurants->firstWhere('id', $activeRestaurantId) ?? $restaurants->first();
        
        if (!$restaurant) {
            return response()->json([
                'scans_by_day' => [],
                'scans_by_hour' => [],
                'top_qrs' => [],
                'total_scans' => 0,
                'total_qrs' => 0,
            ]);
        }

        $period = $request->get('period', '7days'); // 7days, 30days, 24hours

        // Get QR codes with scans
        $qrCodes = $restaurant->qrCodes()->with('scans')->get();
        
        // Scans by day (last 7 or 30 days)
        $scansByDay = [];
        $days = $period === '30days' ? 30 : 7;
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = \App\Models\QrScan::whereHas('qrCode', function($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id);
            })
            ->whereDate('scanned_at', $date)
            ->count();
            
            $scansByDay[] = [
                'date' => $date,
                'label' => now()->subDays($i)->format('d/m'),
                'count' => $count
            ];
        }

        // Scans by hour (last 24 hours)
        $scansByHour = [];
        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i)->format('H:00');
            $count = \App\Models\QrScan::whereHas('qrCode', function($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id);
            })
            ->whereBetween('scanned_at', [
                now()->subHours($i)->startOfHour(),
                now()->subHours($i)->endOfHour()
            ])
            ->count();
            
            $scansByHour[] = [
                'hour' => $hour,
                'count' => $count
            ];
        }

        // Top QR codes by scans
        $topQrs = $qrCodes->map(function($qr) {
            return [
                'id' => $qr->id,
                'name' => $qr->name,
                'scans_count' => $qr->scans_count,
                'scans' => $qr->scans()->count()
            ];
        })->sortByDesc('scans_count')->take(5)->values();

        return response()->json([
            'scans_by_day' => $scansByDay,
            'scans_by_hour' => $scansByHour,
            'top_qrs' => $topQrs,
            'total_scans' => $restaurant->qrCodes()->sum('scans_count'),
            'total_qrs' => $qrCodes->count(),
        ]);
    }
}
