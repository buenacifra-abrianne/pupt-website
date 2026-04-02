<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class UniversityCalendarController extends Controller
{
    public function index()
    {
        return view('public.universitycalendar');
    }
}