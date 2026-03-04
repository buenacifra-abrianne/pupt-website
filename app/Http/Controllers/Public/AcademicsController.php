<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class AcademicsController extends Controller
{
    public function index()
    {
        return view('public.academics');
    }
}