<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class GraduateProgramsController extends Controller
{
    public function index()
    {
        return view('public.graduateprograms');
    }
}