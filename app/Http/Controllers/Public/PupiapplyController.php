<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class PupiapplyController extends Controller
{
    public function index()
    {
        return view('public.pupiapply');
    }
}