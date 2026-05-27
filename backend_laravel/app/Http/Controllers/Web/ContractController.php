<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class ContractController extends Controller
{
    public function free()
    {
        return view('contracts.free');
    }

    public function pro()
    {
        return view('contracts.pro');
    }

    public function plus()
    {
        return view('contracts.plus');
    }
}
