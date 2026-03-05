<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    public function index(Request $request)
    {
        // for now purely UI demo (frontend JS sample data)
        return view('faculty.accounts');
    }
}