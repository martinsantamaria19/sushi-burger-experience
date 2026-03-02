<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteSubmission;

class QuoteSubmissionController extends Controller
{
    public function index()
    {
        $submissions = QuoteSubmission::latest()->paginate(20);

        return view('admin.quotes.index', compact('submissions'));
    }
}
