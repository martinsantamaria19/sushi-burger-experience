<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            abort(403, 'No tienes una compañía asignada');
        }

        $query = Order::with(['restaurant', 'user', 'items'])
            ->whereIn('restaurant_id', $company->restaurants->pluck('id'));

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.orders.index', [
            'orders' => $orders,
            'restaurants' => $company->restaurants,
            'filters' => $request->only(['status', 'restaurant_id', 'payment_status', 'date_from', 'date_to', 'search']),
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, Order $order)
    {
        // If token is provided, redirect to public tracking page
        if ($request->has('token')) {
            $token = $request->query('token');
            if ($order->tracking_token === $token) {
                return redirect()->route('orders.show', ['order' => $order->id, 'token' => $token]);
            }
        }

        $user = Auth::user();
        $company = $user->company;

        // Verify order belongs to company's restaurant
        if (!$company || !$company->restaurants->contains($order->restaurant_id)) {
            abort(403, 'No tienes acceso a este pedido');
        }

        $order->load(['items.product', 'restaurant', 'statusHistory.changedBy', 'user']);

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $user = Auth::user();
        $company = $user->company;

        // Verify order belongs to company's restaurant
        if (!$company || !$company->restaurants->contains($order->restaurant_id)) {
            abort(403, 'No tienes acceso a este pedido');
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,out_for_delivery,delivered,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldStatus = $order->status;
        $success = $order->updateStatus($request->status, $request->notes, $user->id);

        if ($success) {
            return back()->with('success', "Estado del pedido actualizado de '{$order->getStatusLabelAttribute()}' a '{$request->status}'");
        }

        return back()->with('error', 'No se pudo actualizar el estado del pedido');
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $user = Auth::user();
        $company = $user->company;

        // Verify order belongs to company's restaurant
        if (!$company || !$company->restaurants->contains($order->restaurant_id)) {
            abort(403, 'No tienes acceso a este pedido');
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        if (!$order->canBeCancelled()) {
            return back()->with('error', 'Este pedido no puede ser cancelado en su estado actual');
        }

        $success = $order->cancel($request->reason, $user->id);

        if ($success) {
            return back()->with('success', 'Pedido cancelado exitosamente');
        }

        return back()->with('error', 'No se pudo cancelar el pedido');
    }
}
