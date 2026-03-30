<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class DiplomaProgramsController extends Controller
{
    public function index()
    {
        return view('public.diploma-programs');
    }
}