<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class DegreeProgramsController extends Controller
{
    public function index()
    {
        return view('public.degree-programs');
    }
}