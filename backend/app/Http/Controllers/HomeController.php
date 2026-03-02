<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function soluciones()
    {
        return view('soluciones');
    }

    public function instalaciones()
    {
        return view('instalaciones');
    }

    public function contacto()
    {
        return view('contacto');
    }

    public function cotizar()
    {
        return view('cotizar');
    }
}
