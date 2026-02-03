<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Models\QrScan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QrRedirectController extends Controller
{
    /**
     * Handle the QR code scan and redirect to the restaurant menu.
     */
    public function handle(string $slug): RedirectResponse
    {
        $qr = QrCode::where('redirect_slug', $slug)
            ->where('is_active', true)
            ->with('restaurant')
            ->firstOrFail();

        // Log the scan for analytics
        QrScan::create([
            'qr_code_id' => $qr->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Accumulate scan count for quick access
        $qr->increment('scans_count');

        return redirect()->route('public.menu', $qr->restaurant->slug);
    }
}
