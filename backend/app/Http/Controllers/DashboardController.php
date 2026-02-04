<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Repositories\Interfaces\RestaurantRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        return view('admin.dashboard', $this->getDashboardData());
    }

    public function restaurants(): View
    {
        $data = array_merge($this->getDashboardData(), [
            'googleMapsApiKey' => config('services.google_maps.api_key', ''),
        ]);
        return view('admin.restaurants', $data);
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

    public function bankAccounts(): View
    {
        $data = $this->getDashboardData();
        $restaurant = $data['activeRestaurant'];
        $bankAccounts = $restaurant ? $restaurant->bankAccounts()->orderBy('is_active', 'desc')->orderBy('created_at', 'desc')->get() : collect();
        return view('admin.bank-accounts', array_merge($data, ['bankAccounts' => $bankAccounts]));
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
        $user = Auth::user();
        $company = $user->company;

        if (!$company || !$company->hasEcommerce()) {
            abort(404);
        }

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

    /**
     * Get sales analytics data (toda la historia, sin filtro temporal)
     */
    public function getSalesAnalytics(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['error' => 'No company found'], 400);
        }

        if (!$company->hasEcommerce()) {
            return response()->json(['error' => 'Ecommerce no habilitado para esta empresa'], 403);
        }

        $restaurantId = $request->get('restaurant_id');

        $baseOrderQuery = Order::query()
            ->whereHas('restaurant', function ($q) use ($company) {
                $q->where('company_id', $company->id);
            })
            ->when($restaurantId !== '' && $restaurantId !== null, function ($q) use ($restaurantId) {
                $q->where('restaurant_id', (int) $restaurantId);
            })
            ->where('status', '!=', 'cancelled');

        // Totales (todos los pedidos)
        $totalSales = (float) (clone $baseOrderQuery)->sum('total');
        $totalOrders = (clone $baseOrderQuery)->count();
        $avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Ventas por día: agrupar por fecha (todos los días con pedidos)
        $salesByDay = Order::query()
            ->whereHas('restaurant', fn ($q) => $q->where('company_id', $company->id))
            ->when($restaurantId !== '' && $restaurantId !== null, fn ($q) => $q->where('restaurant_id', (int) $restaurantId))
            ->where('status', '!=', 'cancelled')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as sales'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(function ($row) {
                return [
                    'date' => $row->date,
                    'label' => \Carbon\Carbon::parse($row->date)->format('d/m'),
                    'sales' => (float) $row->sales,
                    'orders' => (int) $row->orders,
                ];
            })
            ->values()
            ->all();

        // Sales by restaurant
        $salesByRestaurant = Order::query()
            ->whereHas('restaurant', fn ($q) => $q->where('company_id', $company->id))
            ->when($restaurantId !== '' && $restaurantId !== null, fn ($q) => $q->where('restaurant_id', (int) $restaurantId))
            ->where('status', '!=', 'cancelled')
            ->select('restaurant_id', DB::raw('SUM(total) as total_sales'), DB::raw('COUNT(*) as total_orders'))
            ->groupBy('restaurant_id')
            ->with('restaurant:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'restaurant_id' => $item->restaurant_id,
                    'restaurant_name' => $item->restaurant->name ?? 'N/A',
                    'total_sales' => (float) $item->total_sales,
                    'total_orders' => $item->total_orders,
                ];
            });

        // Sales by payment method
        $salesByPaymentMethod = Order::query()
            ->whereHas('restaurant', fn ($q) => $q->where('company_id', $company->id))
            ->when($restaurantId !== '' && $restaurantId !== null, fn ($q) => $q->where('restaurant_id', (int) $restaurantId))
            ->where('status', '!=', 'cancelled')
            ->select('payment_method', DB::raw('SUM(total) as total_sales'), DB::raw('COUNT(*) as total_orders'))
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                return [
                    'payment_method' => $item->payment_method ?? 'unknown',
                    'payment_method_label' => $item->payment_method === 'mercadopago' ? 'MercadoPago' : ($item->payment_method === 'bank_transfer' ? 'Transferencia' : 'Desconocido'),
                    'total_sales' => (float) $item->total_sales,
                    'total_orders' => $item->total_orders,
                ];
            });

        // Top products (todos los pedidos)
        $topProducts = OrderItem::whereHas('order', function ($query) use ($company, $restaurantId) {
            $query->whereHas('restaurant', fn ($q) => $q->where('company_id', $company->id))
                ->when($restaurantId !== '' && $restaurantId !== null, fn ($q) => $q->where('restaurant_id', (int) $restaurantId))
                ->where('status', '!=', 'cancelled');
        })
            ->select('product_id', 'product_name',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'total_quantity' => (int) $item->total_quantity,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            });

        // Orders by status (todos)
        $ordersByStatus = Order::query()
            ->whereHas('restaurant', fn ($q) => $q->where('company_id', $company->id))
            ->when($restaurantId !== '' && $restaurantId !== null, fn ($q) => $q->where('restaurant_id', (int) $restaurantId))
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->status => $item->count]);

        return response()->json([
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'avg_order_value' => $avgOrderValue,
            'sales_by_day' => $salesByDay,
            'sales_by_restaurant' => $salesByRestaurant,
            'sales_by_payment_method' => $salesByPaymentMethod,
            'top_products' => $topProducts,
            'orders_by_status' => $ordersByStatus,
        ]);
    }

    /**
     * Get products analytics data
     */
    public function getProductsAnalytics(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['error' => 'No company found'], 400);
        }

        if (!$company->hasEcommerce()) {
            return response()->json(['error' => 'Ecommerce no habilitado para esta empresa'], 403);
        }

        $restaurantId = $request->get('restaurant_id');
        $categoryId = $request->get('category_id');

        // Get restaurant IDs to filter (toda la historia, sin filtro temporal)
        $restaurantIds = $company->restaurants->pluck('id');
        if ($restaurantId && $restaurantIds->contains($restaurantId)) {
            $restaurantIds = collect([$restaurantId]);
        }

        // Total products
        $totalProducts = Product::whereIn('restaurant_id', $restaurantIds)
            ->when($categoryId, function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->count();

        // Products by category
        $productsByCategory = Product::whereIn('restaurant_id', $restaurantIds)
            ->select('category_id', DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->with('category:id,name')
            ->get()
            ->map(function($item) {
                return [
                    'category_id' => $item->category_id,
                    'category_name' => $item->category->name ?? 'Sin categoría',
                    'count' => $item->count,
                ];
            });

        // Top selling products (todos los pedidos)
        $topSellingProducts = OrderItem::whereHas('order', function ($query) use ($restaurantIds) {
                $query->whereIn('restaurant_id', $restaurantIds)
                      ->where('status', '!=', 'cancelled');
            })
            ->when($categoryId, function($query) use ($categoryId) {
                $query->whereHas('product', function($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            })
            ->select('product_id', 'product_name',
                     DB::raw('SUM(quantity) as total_quantity'),
                     DB::raw('SUM(subtotal) as total_revenue'),
                     DB::raw('COUNT(DISTINCT order_id) as order_count'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_quantity')
            ->limit(20)
            ->get()
            ->map(function($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'total_quantity' => (int) $item->total_quantity,
                    'total_revenue' => (float) $item->total_revenue,
                    'order_count' => $item->order_count,
                    'avg_per_order' => $item->order_count > 0 ? round($item->total_quantity / $item->order_count, 2) : 0,
                ];
            });

        // Products with no sales (en toda la historia)
        $productsWithNoSales = Product::whereIn('restaurant_id', $restaurantIds)
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->whereDoesntHave('orderItems', function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('status', '!=', 'cancelled');
                });
            })
            ->select('id', 'name', 'price', 'category_id')
            ->with('category:id,name')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'product_id' => $item->id,
                    'product_name' => $item->name,
                    'price' => (float) $item->price,
                    'category_name' => $item->category->name ?? 'Sin categoría',
                ];
            });

        // Products sales over time (todos los días con ventas)
        $productsSalesOverTime = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.restaurant_id', $restaurantIds)
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                DB::raw('DATE(orders.created_at) as date'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_items.product_id) as unique_products')
            )
            ->groupBy(DB::raw('DATE(orders.created_at)'))
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'label' => \Carbon\Carbon::parse($item->date)->format('d/m'),
                    'total_quantity' => (int) $item->total_quantity,
                    'total_revenue' => (float) $item->total_revenue,
                    'unique_products' => $item->unique_products,
                ];
            });

        // Products by restaurant
        $productsByRestaurant = Product::whereIn('restaurant_id', $restaurantIds)
            ->select('restaurant_id', DB::raw('COUNT(*) as count'))
            ->groupBy('restaurant_id')
            ->with('restaurant:id,name')
            ->get()
            ->map(function($item) {
                return [
                    'restaurant_id' => $item->restaurant_id,
                    'restaurant_name' => $item->restaurant->name ?? 'N/A',
                    'count' => $item->count,
                ];
            });

        return response()->json([
            'total_products' => $totalProducts,
            'products_by_category' => $productsByCategory,
            'top_selling_products' => $topSellingProducts,
            'products_with_no_sales' => $productsWithNoSales,
            'products_sales_over_time' => $productsSalesOverTime,
            'products_by_restaurant' => $productsByRestaurant,
        ]);
    }
}
