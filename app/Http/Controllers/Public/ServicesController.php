<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class ServicesController extends Controller
{
    public function index()
    {
        return view('public.services');
    }
}