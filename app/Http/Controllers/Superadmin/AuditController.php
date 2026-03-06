<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;

class AuditController extends Controller
{
    public function index()
    {
        return view('superadmin.audit');
    }
}