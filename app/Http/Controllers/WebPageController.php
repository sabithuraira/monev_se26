<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class WebPageController extends Controller
{
    public function dashboard(): View
    {
        return view('dashboard');
    }

    public function hasilKlaster(): View
    {
        return view('hasil_klaster');
    }

    public function rekapitulasi(): View
    {
        return view('rekapitulasi');
    }
}
