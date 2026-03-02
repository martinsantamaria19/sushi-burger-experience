<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use App\Models\QuoteSubmission;

class DashboardController extends Controller
{
    public function index()
    {
        $contactCount = ContactSubmission::count();
        $quoteCount = QuoteSubmission::count();
        $recentContacts = ContactSubmission::latest()->take(5)->get();
        $recentQuotes = QuoteSubmission::latest()->take(5)->get();

        return view('admin.dashboard', compact('contactCount', 'quoteCount', 'recentContacts', 'recentQuotes'));
    }
}
