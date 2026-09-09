<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PublicPortalController extends Controller
{
    public function __invoke(): View
    {
        return view('public-portal');
    }
}
