<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class EventsController extends Controller
{
    public function index()
    {
        return view('public.events', [
            'selectedSection'        => null,
            'campusStoryDescription' => '',
            'homeCms'                => [],
        ]);
    }
}