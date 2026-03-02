<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use App\Models\QuoteSubmission;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function enviarContacto(Request $request)
    {
        $valid = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        ContactSubmission::create($valid);

        return redirect()->route('contacto')->with('success', 'Tu mensaje fue enviado. Te contactaremos a la brevedad.');
    }

    public function enviarCotizacion(Request $request)
    {
        $valid = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        QuoteSubmission::create($valid);

        return redirect()->route('cotizar')->with('success', 'Gracias. Te contactaremos en menos de 24 horas.');
    }
}
