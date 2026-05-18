<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class LegalController extends Controller
{
    public function privacy()       { return view('legal.privacy'); }
    public function dataUsage()     { return view('legal.data-usage'); }
    public function cookies()       { return view('legal.cookies'); }
    public function terms()         { return view('legal.terms'); }
    public function consent()       { return view('legal.consent'); }
}
