<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class FeedbackController extends Controller
{
    public function index()
    {
        return view('public.feedback');
    }
}