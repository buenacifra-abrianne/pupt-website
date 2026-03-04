<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class StudentsController extends Controller
{
    public function index()
    {
        return view('public.students');
    }
}